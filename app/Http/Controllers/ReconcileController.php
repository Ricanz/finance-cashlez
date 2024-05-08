<?php

namespace App\Http\Controllers;

use App\Exports\ReconcileExport;
use App\Helpers\Reconcile;
use App\Helpers\Utils;
use App\Models\Bank;
use App\Models\BankParameter;
use App\Models\Channel;
use App\Models\InternalBatch;
use App\Models\InternalMerchant;
use App\Models\InternalTransaction;
use App\Models\ReconcileResult;
use App\Models\UploadBank;
use App\Models\UploadBankDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReconcileController extends Controller
{
    public function index()
    {
        $banks = Bank::where('status', 'active')->get();
        return view('modules.reconcile.index', compact('banks'));
    }

    public function store(Request $request)
    {
        if ($request->filled('bo_date') && $request->filled('bs_date')) {
            $splitDate = explode(' - ', $request->bo_date);
            $BoStartDate = date('Y-m-d', strtotime($splitDate[0]));
            $BoEndDate = date('Y-m-d', strtotime($splitDate[0]));

            $BsSplitDate = explode(' - ', $request->bo_date);
            $BsStartDate = date('Y-d-m', strtotime($BsSplitDate[0]));
            $BsEndDate = date('Y-d-m', strtotime($BsSplitDate[1]));
        }

        $channel = Channel::where('channel', $request->bank)->first();
        $parameter = BankParameter::where('channel_id', $channel->id)->first();
        

        $reconResult = false;
        try {
            if ($parameter->bo_summary == 'mid' && $parameter->bank_statement == 'mid') {
                $reconResult = Reconcile::midBoBank($BoStartDate, $BoEndDate, $request->bank, $BsStartDate, $BsEndDate);
            }

            if (!$reconResult) {
                return  response()->json(['message' => 'Error while reconcile, try again', 'status' => false], 200);
            }
            return  response()->json(['message' => 'Successfully reconcile data!', 'status' => true], 200);
        } catch (\Throwable $th) {
            return  response()->json(['message' => 'Error while reconcile, try again', 'status' => false], 200);
        }
    }

    public function reconcile(Request $request)
    {
        $user = Auth::user();
        if (!isset($request->selectedBo)) {
            return  response()->json(['message' => ["Please select Back Office Settlement!"], 'status' => false], 200);
        }
        if (!isset($request->selectedBank)) {
            return  response()->json(['message' => ["Please select Bank Settlement!"], 'status' => false], 200);
        }

        $selectedBo = explode(',', $request->selectedBo);
        $selectedBank = explode(',', $request->selectedBank);

        $trxCount = 0;
        $boSettlement = 0;
        $feeMdrMerchant = 0;
        $feeBankMerchant = 0;
        $taxPayment = 0;
        $totalSales = 0;
        $sumTransaction = 0;
        $merchantPayment = 0;
        $bankSettlement = 0;


        foreach ($selectedBo as $key => $value) {
            // $transaction = InternalTransaction::with('header')->where('id', $value)->first();
            $internalBatch = InternalBatch::where('id', $value)->first();

            $trxCount = $trxCount + $internalBatch->transaction_count;
            $boSettlement = $boSettlement + $internalBatch->bank_transfer;
            $feeMdrMerchant = $feeMdrMerchant + $internalBatch->fee_mdr_merchant;
            $feeBankMerchant = $feeBankMerchant + $internalBatch->fee_bank_merchant;
            $taxPayment = $taxPayment + $internalBatch->tax_payment;
            $totalSales = $totalSales + $internalBatch->transaction_amount;
            $merchant_id = $internalBatch->merchant_id;
            $sumTransaction = $sumTransaction + $internalBatch->transaction_amount;

            $merchantPayment = $merchantPayment + Utils::calculateMerchantPayment($boSettlement, $feeMdrMerchant, $feeBankMerchant, $taxPayment);
        }

        foreach ($selectedBank as $key => $value) {
            $bank = UploadBankDetail::where('id', $value)->first();
            // $sumBank = $sumBank + (float)$bank->amount_credit;
            // $amount_credit = $amount_credit + $bank->amount_credit;
            $bankSettlement = $bankSettlement + (float)$bank->amount_credit;
        }

        $rounded_value = round((int)$bankSettlement);
        $amount_credit = number_format($rounded_value, 0, '', '');

        $diff = abs((float)$boSettlement - (float)$bankSettlement);

        $treshold = Utils::calculateTreshold($trxCount);
        $status = Utils::getStatusReconcile($treshold, $boSettlement, $bankSettlement);

        $diff = abs((float)$boSettlement - (float)$bankSettlement);

        if ($status == "MATCH") {
            foreach ($selectedBank as $key => $value) {
                $det = UploadBankDetail::with('header')->where('id', $value)->first();
                // $internalBatch = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->get();
                $carbonDate = Carbon::createFromFormat('m/d/Y', $det->transfer_date);

                $reconcile = ReconcileResult::create([
                    'token_applicant' => $det->token_applicant,
                    'statement_id' => $det->id,
                    'request_id' => $det->header->id,
                    'status' => $status,
                    'mid' => $det->mid,
                    'trx_counts' => $trxCount, // total transaksi 1 batch
                    'total_sales' => $totalSales, // sum transaction_amout di internal_taransaction 
                    'processor_payment' => $det->header->processor,
                    'internal_payment' => $boSettlement, // bank_payment
                    'merchant_payment' => $merchantPayment, // bank_payment - merchant_fee_amount
                    'merchant_id' => $merchant_id,
                    'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
                    'bank_settlement_amount' => $amount_credit, // bank_settlement
                    'dispute_amount' => $diff, // dispute_amount
                    'created_by' => $user->name,
                    'modified_by' => null,
                    'settlement_date' => $carbonDate
                ]);
                if ($reconcile) {
                    $det->is_reconcile = true;
                    $det->save();
                }
                return  response()->json(['message' => 'Successfully Reconcile data!', 'status' => true], 200);
            }
            return  response()->json(['message' => ['Failed Reconcile Data!'], 'status' => false], 200);
        }
        return  response()->json(['message' => ['Data Not Match!'], 'status' => false], 200);
    }

    public function result()
    {
        $token_applicant = request()->query('token');
        $status = request()->query('status');

        $query1 = ReconcileResult::query();
        $query2 = ReconcileResult::query();
        $query3 = ReconcileResult::query();
        $query4 = ReconcileResult::query();
        $query5 = ReconcileResult::query();
        $query6 = ReconcileResult::query();

        if ($token_applicant) {
            $query1->where('token_applicant', $token_applicant);
            $query2->where('token_applicant', $token_applicant);
            $query3->where('token_applicant', $token_applicant);
            $query4->where('token_applicant', $token_applicant);
            $query5->where('token_applicant', $token_applicant);
            $query6->where('token_applicant', $token_applicant);
        }

        $match = $query1->where('status', 'MATCH')->count();
        $dispute = $query2->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->count();
        $onHold = $query3->where('status', 'ON_HOLD')->count();

        $sumMatch = $query4->where('status', 'MATCH')->sum('total_sales');
        $sumDispute = $query5->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->sum('total_sales');
        $sumHold = $query6->where('status', 'ON_HOLD')->sum('total_sales');

        $banks = Bank::where('status', 'active')->get();

        return view('modules.reconcile.show', compact('banks', 'match', 'dispute', 'onHold', 'sumMatch', 'sumDispute', 'sumHold'));
    }

    public function show($token_applicant)
    {
        $match = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->count();
        $dispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->count();
        $onHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->count();

        $sumMatch = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->sum('total_sales');
        $sumDispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->sum('total_sales');
        $sumHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->sum('total_sales');

        $banks = Bank::where('status', 'active')->get();

        return view('modules.reconcile.show', compact('banks', 'match', 'dispute', 'onHold', 'token_applicant', 'sumMatch', 'sumDispute', 'sumHold'));
    }

    public function data(Request $request)
    {
        $token_applicant = request()->query('token');
        $status = request()->query('status');

        $query = ReconcileResult::with('merchant', 'bank_account');
        if ($token_applicant) {
            $query->where('token_applicant', $token_applicant);
        }
        if ($status) {
            if ($status == "match") {
                $query->where('status', 'MATCH');
            } elseif ($status == "dispute") {
                $query->whereIn('status', ['NOT_MATCH', 'NOT_FOUND']);
            }
        }

        if ($request->input('status') !== null) {
            switch ($request->input('status')) {
                case 'match':
                    $status = ['MATCH'];
                    break;
                case 'dispute':
                    $status = ['NOT_MATCH', 'NOT_FOUND'];
                    break;
                case 'onHold':
                    $status = ['NOT_FOUND'];
                    break;
                default:
                    $status = ['NOT_FOUND'];
                    break;
            }
            $query->whereIn('status', $status);
        }

        if ($request->input('startDate') && $request->input('endDate')) {
            $startDate = $request->startDate;
            $endDate = $request->endDate;

            $query->whereDate('settlement_date', '>=', $startDate)
                ->whereDate('settlement_date', '<=', $endDate);
        }

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }

    public function download()
    {
        $token_applicant = request()->query('token');
        $status = request()->query('status');
        $bank = request()->query('bank');

        $startDate = request()->query('startDate');
        $endDate = request()->query('endDate');

        if (!$status) {
            $text = 'all';
        } else {
            $text = $status;
        }

        $filename = $bank . '-' . $startDate . '-to-' . $endDate . '-' . $text;

        return Excel::download(new ReconcileExport($token_applicant, $status, $startDate, $endDate), 'reconcile-' . $filename . '.xlsx');
    }

    public function mrcDetail($token_applicant)
    {
        $data = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $token_applicant)->first();

        return  response()->json(['data' => $data, 'message' => 'Successfully get data!', 'status' => true], 200);
    }

    public function proceed($token_applicant)
    {
        $data = UploadBank::where('token_applicant', $token_applicant)->first();

        if ($data) {
            DB::beginTransaction();
            try {
                $boData = InternalBatch::selectRaw('
                            SUM(transaction_count) as transaction_count,
                            SUM(bank_transfer) as bank_transfer,
                            SUM(fee_mdr_merchant) as fee_mdr_merchant,
                            SUM(fee_bank_merchant) as fee_bank_merchant,
                            SUM(tax_payment) as tax_payment,
                            SUM(transaction_amount) as transaction_amount,
                            merchant_id,
                            mid
                        ')
                    ->where(DB::raw('DATE(created_at)'), '>=', date('Y-m-d', strtotime($data->start_date)))
                    ->where(DB::raw('DATE(created_at)'), '<=', date('Y-m-d', strtotime($data->end_date)))
                    ->where('processor', $data->processor)
                    ->groupBy('mid', 'merchant_id')
                    ->get();
                dd($boData[2]->mid);

                foreach ($boData as $key => $value) {
                    dd($value->mid);
                    $details = UploadBankDetail::selectRaw('
                                    SUM(amount_credit) as amount_credit,
                                    mid
                                ')
                        ->where('token_applicant', $token_applicant)
                        ->where('mid', 'like', '%' . $value->mid . '%')
                        ->where('type_code', '001')
                        ->where('is_reconcile', false)
                        ->groupBy('mid')->first();
                    dd($details);
                }
                $details = UploadBankDetail::where('token_applicant', $token_applicant)->where('type_code', '001')->where('is_reconcile', false)->get();

                foreach ($details as $key => $value) {
                    $carbonDate = Carbon::createFromFormat('d/m/Y', $value->transfer_date);
                    $formattedDate = $carbonDate->format('Y-m-d');
                    $internalBatch = InternalBatch::selectRaw('
                                    SUM(transaction_count) as transaction_count,
                                    SUM(bank_transfer) as bank_transfer,
                                    SUM(fee_mdr_merchant) as fee_mdr_merchant,
                                    SUM(fee_bank_merchant) as fee_bank_merchant,
                                    SUM(tax_payment) as tax_payment,
                                    SUM(transaction_amount) as transaction_amount,
                                    merchant_id,
                                    mid
                                ')
                        ->where('mid', 'like', '%' . $value->mid . '%')
                        ->where(DB::raw('DATE(created_at)'), '=', $formattedDate)
                        ->groupBy('mid', 'merchant_id')
                        ->first();

                    $bankSettlement = $value->amount_credit;
                    $rounded_value = round((int)$bankSettlement);
                    $amount_credit = number_format($rounded_value, 0, '', '');
                    if ($internalBatch) {
                        $trxCount = $internalBatch->transaction_count;
                        $boSettlement = $internalBatch->bank_transfer;
                        $feeMdrMerchant = $internalBatch->fee_mdr_merchant;
                        $feeBankMerchant = $internalBatch->fee_bank_merchant;
                        $taxPayment = $internalBatch->tax_payment;
                        $totalSales = $internalBatch->bank_transfer + $internalBatch->fee_bank_merchant;
                        $merchant_id = $internalBatch->merchant_id;
                        $sumTransaction = $internalBatch->transaction_amount;

                        $merchantPayment = Utils::calculateMerchantPayment($boSettlement, $feeMdrMerchant, $feeBankMerchant, $taxPayment); // tanya mas tri

                        $diff = abs((float)$boSettlement - (float)$bankSettlement);
                        $treshold = Utils::calculateTreshold($trxCount);
                        $status = Utils::getStatusReconcile($treshold, $boSettlement, $bankSettlement);
                    } else {
                        $status = 'NOT_FOUND';
                        $trxCount = 0;
                        $totalSales = 0;
                        $boSettlement = 0;
                        $merchantPayment = 0;
                        $sumTransaction = 0;
                        $diff = 0 - (float)$bankSettlement;
                    }

                    $reconcile = ReconcileResult::create([
                        'token_applicant' => $token_applicant,
                        'statement_id' => $value->id,
                        'request_id' => $data->id,
                        'status' => $status,
                        // 'tid' => $tid,
                        'mid' => $value->mid,
                        // 'batch_fk' => $batch_fk,
                        'trx_counts' => $trxCount, // total transaksi 1 batch
                        'total_sales' => $totalSales, // sum transaction_amout di internal_taransaction 
                        'processor_payment' => $data->processor,
                        'internal_payment' => $boSettlement, // bank_payment
                        'merchant_payment' => $merchantPayment, // bank_payment - merchant_fee_amount
                        'merchant_id' => $merchant_id,
                        'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
                        'bank_settlement_amount' => $amount_credit, // bank_settlement
                        'dispute_amount' => $diff, // dispute_amount
                        // 'tax_payment',
                        // 'fee_mdr_merchant',
                        // 'fee_bank_merchant',
                        // 'bank_transfer',
                        'created_by' => 'System',
                        'modified_by' => null,
                        'settlement_date' => $carbonDate
                    ]);

                    $det = UploadBankDetail::where('id', $value->id)->first();

                    if ($status == 'MATCH') {
                        $det->is_reconcile = true;
                    } else {
                        $det->is_reconcile = false;
                    }
                    $det->save();

                    $data->is_reconcile = true;
                    $data->save();
                }

                DB::commit();
                return  response()->json(['message' => 'Successfully reconcile data!', 'status' => true], 200);
            } catch (\Throwable $th) {
                dd($th);
                DB::rollBack();
                return  response()->json(['message' => 'Error while reconcile, try again', 'status' => false], 200);
            }
        }
    }

    public function detail($token_applicant)
    {
        $channels = UploadBankDetail::select('description2')->where('token_applicant', $token_applicant)->where('description2', '!=', '')->groupBy('description2')->get();

        $sumCredit = UploadBankDetail::where('token_applicant', $token_applicant)->sum('amount_credit');
        $totalCredit = UploadBankDetail::where('token_applicant', $token_applicant)->where('amount_credit', '>', 0)->count();

        $sumDebit = UploadBankDetail::where('token_applicant', $token_applicant)->sum('amount_debit');
        $totalDebit = UploadBankDetail::where('token_applicant', $token_applicant)->where('amount_debit', '>', 0)->count();
        return view('modules.reconcile.detail.index', compact('channels', 'sumCredit', 'totalCredit', 'sumDebit', 'totalDebit'));
    }

    public function detailData(Request $request, $token_applicant)
    {
        $query = UploadBankDetail::where('token_applicant', $token_applicant);
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = date('d/m/Y', strtotime($request->startDate));
            $endDate = date('d/m/Y', strtotime($request->endDate));

            $query->where('transfer_date', '>=', $startDate);
            $query->where('transfer_date', '<=', $endDate);
        }
        if ($request->filled('channel')) {
            $query->where('description2', $request->channel);
        }

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }

    public function channel(Request $request)
    {
        $token_applicant = $request->token_applicant;

        if ($request->filled('bo_date') && $request->filled('bs_date')) {
            $splitDate = explode(' - ', $request->bo_date);
            $BoStartDate = date('Y-m-d', strtotime($splitDate[0]));
            $BoEndDate = date('Y-m-d', strtotime($splitDate[0]));

            $BsSplitDate = explode(' - ', $request->bo_date);
            $BsStartDate = date('d/m/Y', strtotime($BsSplitDate[0]));
            $BsEndDate = date('d/m/Y', strtotime($BsSplitDate[1]));
        }

        DB::beginTransaction();
        try {
            $uploadBank = UploadBank::where('token_applicant', $token_applicant)->first();
            $boData = InternalBatch::selectRaw('
                        SUM(transaction_count) as transaction_count,
                        SUM(bank_transfer) as bank_transfer,
                        SUM(fee_mdr_merchant) as fee_mdr_merchant,
                        SUM(fee_bank_merchant) as fee_bank_merchant,
                        SUM(tax_payment) as tax_payment,
                        SUM(transaction_amount) as transaction_amount,
                        SUM(total_sales_amount) as total_sales_amount,
                        merchant_id,
                        mid,
                        DATE(created_at) as created_date
                    ')
                ->where(DB::raw('DATE(created_at)'), '>=', $BoStartDate)
                ->where(DB::raw('DATE(created_at)'), '<=', $BoEndDate)
                ->where('processor', 'Mandiri')
                ->where('bank_id', 5)
                ->where('status', 'SUCCESSFUL')
                ->groupBy('mid', 'merchant_id', 'created_date')
                ->get();

            foreach ($boData as $key => $value) {
                $modMid = substr($value->mid, 5);
                $bsData = UploadBankDetail::selectRaw('
                        SUM(amount_credit) as amount_credit,
                        mid, token_applicant
                    ')
                    ->with('header')
                    ->whereHas('header', function ($query) use ($uploadBank) {
                        $query->where('processor', $uploadBank->processor);
                    })
                    ->where('mid', 'like', '%' . $modMid . '%')
                    ->where('token_applicant', $token_applicant)
                    ->where('description2', $request->channel)
                    ->where('type_code', '001')
                    ->where('is_reconcile', false)
                    ->groupBy('mid', 'token_applicant')->first();

                if ($bsData) {
                    dd($bsData);
                    $bankSettlement = $bsData->amount_credit;
                    $token_applicant = $bsData->header->token_applicant;

                    $trxCount = $value->transaction_count;
                    $boSettlement = Utils::customRound($value->bank_transfer);

                    $feeMdrMerchant = $value->fee_mdr_merchant;
                    $feeBankMerchant = $value->fee_bank_merchant;
                    $taxPayment = $value->tax_payment;
                    $totalSales = $value->total_sales_amount;

                    $merchant_id = $value->merchant_id;
                    $sumTransaction = $value->transaction_amount;

                    $merchantPayment = Utils::calculateMerchantPayment($boSettlement, $feeMdrMerchant, $feeBankMerchant, $taxPayment); // tanya mas tri

                    $rounded_value = round((int)$bankSettlement);
                    $amount_credit = number_format($rounded_value, 0, '', '');

                    $diff = abs((float)$boSettlement - (float)$bankSettlement);
                    $treshold = Utils::calculateTreshold($trxCount);
                    $status = Utils::getStatusReconcile($treshold, $boSettlement, $bankSettlement);

                    $reconcile = ReconcileResult::create([
                        'token_applicant' => $token_applicant,
                        'statement_id' => $bsData ? $bsData->id : null,
                        'request_id' => $bsData ? $bsData->header->id : null,
                        'status' => $status,
                        'mid' => $value->mid,
                        'trx_counts' => $trxCount, // total transaksi 1 batch
                        'total_sales' => $totalSales, // sum transaction_amout di internal_taransaction 
                        'processor_payment' => $request->bank,
                        'internal_payment' => $boSettlement, // bank_payment
                        'merchant_payment' => $merchantPayment, // bank_payment - merchant_fee_amount
                        'merchant_id' => $merchant_id,
                        'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
                        'bank_settlement_amount' => $amount_credit, // bank_settlement
                        'dispute_amount' => $diff, // dispute_amount
                        'created_by' => 'System',
                        'modified_by' => null,
                        'settlement_date' => $value->created_date
                    ]);
                    if ($token_applicant) {
                        $uploadBank = UploadBank::where('token_applicant', $token_applicant)->update([
                            'is_reconcile' => true
                        ]);
                    }
                }
            }
            DB::commit();
            return  response()->json(['message' => 'Successfully reconcile data!', 'status' => true], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return  response()->json(['message' => 'Error while reconcile, try again', 'status' => false], 200);
        }
    }
}
