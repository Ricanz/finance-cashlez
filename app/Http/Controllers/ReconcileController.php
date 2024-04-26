<?php

namespace App\Http\Controllers;

use App\Exports\ReconcileExport;
use App\Helpers\Utils;
use App\Models\Bank;
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

        return view('modules.reconcile.show', compact('match', 'dispute', 'onHold', 'sumMatch', 'sumDispute', 'sumHold'));
    }

    // public function proceed($token_applicant)
    // {
    //     $user = Auth::user();
    //     $data = UploadBank::where('token_applicant', $token_applicant)->first();
    //     if ($data) {
    //         $details = UploadBankDetail::where('token_applicant', $token_applicant)->where(DB::raw('LOWER(description1)', '=', 'pbyrn merchant'))->where('is_reconcile', false)->get();
    //         foreach ($details as $key => $value) {
    //             $carbonDate = Carbon::createFromFormat('d/m/Y', $value->transfer_date);
    //             $formattedDate = $carbonDate->format('Y-m-d');
    //             // $batch_fk = '';
    //             // $tid = '';

    //             $internalBatch = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->where(DB::raw('DATE(created_at)'), '>=', $formattedDate)->first();
    //             // if (!$internalBatch) {
    //             //     return  response()->json(['message' => ['Data not found in Back Office'], 'status' => false], 400);
    //             // }
    //             $bankSettlement = $value->amount_credit;
    //             $rounded_value = round((int)$bankSettlement);
    //             $amount_credit = number_format($rounded_value, 0, '', '');
    //             if ($internalBatch) {
    //                 $trxCount = $internalBatch->transaction_count;
    //                 $boSettlement = $internalBatch->bank_transfer;
    //                 $feeMdrMerchant = $internalBatch->fee_mdr_merchant;
    //                 $feeBankMerchant = $internalBatch->fee_bank_merchant;
    //                 $taxPayment = $internalBatch->tax_payment;
    //                 $totalSales = $internalBatch->transaction_amount;
    //                 $merchant_id = $internalBatch->merchant_id;
    //                 $sumTransaction = $internalBatch->transaction_amount;

    //                 $merchantPayment = Utils::calculateMerchantPayment($boSettlement, $feeMdrMerchant, $feeBankMerchant, $taxPayment); // tanya mas tri

    //                 $diff = abs((float)$boSettlement - (float)$bankSettlement);
    //                 $treshold = Utils::calculateTreshold($trxCount);
    //                 $status = Utils::getStatusReconcile($treshold, $boSettlement, $bankSettlement);
    //             } else {
    //                 $status = 'NOT_FOUND';
    //                 $trxCount = 0;
    //                 $totalSales = 0;
    //                 $boSettlement = 0;
    //                 $merchantPayment = 0;
    //                 $sumTransaction = 0;
    //                 $diff = 0 - (float)$bankSettlement;
    //             }
    //             dd("here");

    //             DB::beginTransaction();
    //             try {
    //                 $reconcile = ReconcileResult::create([
    //                     'token_applicant' => $token_applicant,
    //                     'statement_id' => $value->id,
    //                     'request_id' => $data->id,
    //                     'status' => $status,
    //                     // 'tid' => $tid,
    //                     'mid' => $value->mid,
    //                     // 'batch_fk' => $batch_fk,
    //                     'trx_counts' => $trxCount, // total transaksi 1 batch
    //                     'total_sales' => $totalSales, // sum transaction_amout di internal_taransaction 
    //                     'processor_payment' => $data->processor,
    //                     'internal_payment' => $boSettlement, // bank_payment
    //                     'merchant_payment' => $merchantPayment, // bank_payment - merchant_fee_amount
    //                     'merchant_id' => $merchant_id,
    //                     'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
    //                     'bank_settlement_amount' => $amount_credit, // bank_settlement
    //                     'dispute_amount' => $diff, // dispute_amount
    //                     // 'tax_payment',
    //                     // 'fee_mdr_merchant',
    //                     // 'fee_bank_merchant',
    //                     // 'bank_transfer',
    //                     'created_by' => 'System',
    //                     'modified_by' => null,
    //                     'settlement_date' => $carbonDate
    //                 ]);
    //                 if ($reconcile) {
    //                     $det = UploadBankDetail::where('id', $value->id)->first();
    //                     if ($status == 'MATCH') {
    //                         $det->is_reconcile = true;
    //                     } else {
    //                         $det->is_reconcile = false;
    //                     }
    //                     $det->save();

    //                     $data->is_reconcile = true;
    //                     $data->save();
    //                 }
    //                 DB::commit();
    //                 return  response()->json(['message' => 'Successfully reconcile data!', 'status' => true], 200);
    //             } catch (\Throwable $th) {
    //                 dd($th);
    //                 DB::rollBack();
    //                 return  response()->json(['message' => 'Error while reconcile, try again', 'status' => false], 200);
    //             }
    //         }
    //     }
    // }

    public function show($token_applicant)
    {
        $match = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->count();
        $dispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->count();
        $onHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->count();

        $sumMatch = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->sum('total_sales');
        $sumDispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->sum('total_sales');
        $sumHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->sum('total_sales');

        return view('modules.reconcile.show', compact('match', 'dispute', 'onHold', 'token_applicant', 'sumMatch', 'sumDispute', 'sumHold'));
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

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }

    public function download()
    {
        $token_applicant = request()->query('token');
        $status = request()->query('status');
        if (!$status) {
            $text = 'all';
        } else {
            $text = $status;
        }

        $filename = date('d-m-Y') . '-' . $text;
        return Excel::download(new ReconcileExport($token_applicant, $status), 'reconcile' . $filename . '.xlsx');
    }

    public function mrcDetail($token_applicant)
    {
        $data = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $token_applicant)->first();

        return  response()->json(['data' => $data, 'message' => 'Successfully get data!', 'status' => true], 200);
    }

    // OLD RIYANTI LOGIC

    public function proceed($token_applicant)
    {
        $data = UploadBank::where('token_applicant', $token_applicant)->first();

        if ($data) {

            DB::beginTransaction();
            try {
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
                        $totalSales = $internalBatch->transaction_amount;
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

    // public function proceed($token_applicant)
    // {
    //     $user = Auth::user();

    //     DB::beginTransaction();
    //     try {
    //         $data = UploadBank::where('token_applicant', $token_applicant)->first();
    //         if ($data) {
    //             $details = UploadBankDetail::where('token_applicant', $token_applicant)->where('type_code', '001')->where('is_reconcile', false)->get();
    //             foreach ($details as $key => $value) {
    //                 $batch_fk = '';
    //                 $tid = '';

    //                 $internalBatch = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->get();
    //                 $sumTransaction = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->sum('transaction_amount');

    //                 $trxCount = 0;
    //                 $trxSum = 0;
    //                 $trxAmount = 0;
    //                 $trxMerchantSum = 0;
    //                 $sumCreditAmount = 0;
    //                 if ($internalBatch) {
    //                     foreach ($internalBatch as $key => $batch) {
    //                         $merchant_id = $batch->merchant_id;

    //                         $batch_fk .= $batch->batch_fk . ', ';
    //                         $tid .= $batch->tid . ', ';
    //                         $trxCount = $trxCount + $batch->transaction_count;

    //                         $select = InternalTransaction::selectRaw('
    //                                     SUM(bank_payment) as bank_payment, 
    //                                     SUM(transaction_amount) as transaction_amount,
    //                                     SUM(merchant_fee_amount) as sum_merchant_fee')
    //                             ->where('batch_fk', $batch->batch_fk)->first();

    //                         $trxSum = $trxSum + $select->bank_payment;
    //                         $sumCreditAmount = $sumCreditAmount + (float)$value->amount_credit;

    //                         $trxAmount = $trxAmount + $select->transaction_amount;

    //                         $calculateMerchant = (float)$select->bank_payment - (float)$select->sum_merchant_fee;
    //                         $trxMerchantSum = $trxMerchantSum + $calculateMerchant;
    //                     }

    //                     $batch_fk = Str::beforeLast($batch_fk, ', ');
    //                     $tid = Str::beforeLast($tid, ', ');

    //                     $rounded_value = round((int)$sumCreditAmount);
    //                     $amount_credit = number_format($rounded_value, 0, '', '');

    //                     $diff = abs((float)$trxSum - (float)$sumCreditAmount);

    //                     $treshold = Utils::calculateTreshold($trxCount);
    //                     $status = Utils::getStatusReconcile($treshold, $trxSum, $sumCreditAmount);

    //                     $reconcile = ReconcileResult::create([
    //                         'token_applicant' => $token_applicant,
    //                         'statement_id' => $value->id,
    //                         'request_id' => $data->id,
    //                         'status' => $status,
    //                         'tid' => $tid,
    //                         'mid' => $value->mid,
    //                         'batch_fk' => $batch_fk,
    //                         'trx_counts' => $trxCount, // total transaksi 1 batch
    //                         'total_sales' => $trxAmount, // sum transaction_amout di internal_taransaction 
    //                         'processor_payment' => $data->processor,
    //                         'internal_payment' => $trxSum, // bank_payment
    //                         'merchant_payment' => $trxMerchantSum, // bank_payment - merchant_fee_amount
    //                         'merchant_id' => $merchant_id,
    //                         'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
    //                         'bank_settlement_amount' => $amount_credit, // bank_settlement
    //                         'dispute_amount' => $diff, // dispute_amount
    //                         // 'tax_payment',
    //                         // 'fee_mdr_merchant',
    //                         // 'fee_bank_merchant',
    //                         // 'bank_transfer',
    //                         'created_by' => 'System',
    //                         'modified_by' => null,
    //                         'settlement_date' => $internalBatch[0]->created_at
    //                     ]);
    //                     if ($reconcile) {
    //                         $det = UploadBankDetail::where('id', $value->id)->first();
    //                         $det->is_reconcile = true;
    //                         $det->save();

    //                         $data->is_reconcile = true;
    //                         $data->save();
    //                     }
    //                 }
    //             }
    //         }
    //         DB::commit();
    //         return  response()->json(['message' => 'Successfully upload data!', 'status' => true], 200);
    //     } catch (\Throwable $th) {
    //         dd($th);
    //         DB::rollBack();
    //         return  response()->json(['message' => 'Error while uploading, try again', 'status' => false], 200);
    //     }
    // }


    // public function reconcile(Request $request)
    // {
    //     if (!isset($request->selectedBo)) {
    //         return  response()->json(['message' => ["Please select Back Office Settlement!"], 'status' => false], 200);
    //     }
    //     if (!isset($request->selectedBank)) {
    //         return  response()->json(['message' => ["Please select Bank Settlement!"], 'status' => false], 200);
    //     }

    //     $selectedBo = explode(',', $request->selectedBo);
    //     $selectedBank = explode(',', $request->selectedBank);;

    //     $sumTrx = 0;
    //     $sumBank = 0;
    //     $mid = '';
    //     $tid = '';
    //     $batch_fk = '';
    //     $trxCount = 0;
    //     $trxAmount = 0;
    //     $trxSum = 0;
    //     $trxMerchantSum = 0;
    //     $sumTransaction = 0;
    //     $amount_credit = 0;


    //     foreach ($selectedBo as $key => $value) {
    //         $transaction = InternalTransaction::with('header')->where('id', $value)->first();
    //         $sumTrx = $sumTrx + (float)$transaction->bank_payment;
    //         $mid = $transaction->header->mid;
    //         $tid .= $transaction->header->tid . ', ';
    //         $batch_fk .= $transaction->header->batch_fk . ', ';
    //         $trxCount = $trxCount + $transaction->header->transaction_count;
    //         $trxAmount = $trxAmount + $transaction->transaction_amount;
    //         $trxSum = $trxSum + $transaction->bank_payment;
    //         $calculateMerchant = (float)$transaction->bank_payment - (float)$transaction->sum_merchant_fee;
    //         $trxMerchantSum = $trxMerchantSum + $calculateMerchant;
    //         $merchant_id = $transaction->header->merchant_id;
    //         $sumTransaction = $transaction->header->transaction_amount;
    //     }

    //     $batch_fk = Str::beforeLast($batch_fk, ', ');
    //     $tid = Str::beforeLast($tid, ', ');

    //     foreach ($selectedBank as $key => $value) {
    //         $bank = UploadBankDetail::where('id', $value)->first();
    //         $sumBank = $sumBank + (float)$bank->amount_credit;
    //         $amount_credit = $amount_credit + $bank->amount_credit;
    //     }

    //     $diff = abs((float)$sumTrx - (float)$sumBank);

    //     if ($diff == 1 || $diff == 0) {
    //         $status = 'MATCH';
    //     } else if ((float)$sumTrx !== (float)$sumBank) {
    //         $status = 'NOT_MATCH';
    //     } else {
    //         $status = 'NOT_FOUND';
    //     }

    //     if ($status == "MATCH") {
    //         foreach ($selectedBank as $key => $value) {
    //             $det = UploadBankDetail::with('header')->where('id', $value)->first();
    //             // $internalBatch = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->get();
    //             $carbonDate = Carbon::createFromFormat('m/d/Y', $det->transfer_date);

    //             $reconcile = ReconcileResult::create([
    //                 'token_applicant' => $det->token_applicant,
    //                 'statement_id' => $det->id,
    //                 'request_id' => $det->header->id,
    //                 'status' => $status,
    //                 'tid' => $tid,
    //                 'mid' => $mid,
    //                 'batch_fk' => $batch_fk,
    //                 'trx_counts' => $trxCount, // total transaksi 1 batch
    //                 'total_sales' => $trxAmount, // sum transaction_amout di internal_taransaction 
    //                 'processor_payment' => $det->header->processor,
    //                 'internal_payment' => $trxSum, // bank_payment
    //                 'merchant_payment' => $trxMerchantSum, // bank_payment - merchant_fee_amount
    //                 'merchant_id' => $merchant_id,
    //                 'transfer_amount' => $sumTransaction, // transaction_amount di internal_batch
    //                 'bank_settlement_amount' => $amount_credit, // bank_settlement
    //                 'dispute_amount' => $diff, // dispute_amount
    //                 'created_by' => 'System',
    //                 'modified_by' => null,
    //                 'settlement_date' => $carbonDate
    //             ]);
    //             if ($reconcile) {
    //                 $det->is_reconcile = true;
    //                 $det->save();
    //             }
    //             return  response()->json(['message' => 'Successfully Reconcile data!', 'status' => true], 200);
    //         }
    //         return  response()->json(['message' => ['Failed Reconcile Data!'], 'status' => false], 200);
    //     }
    //     return  response()->json(['message' => ['Data Not Match!'], 'status' => false], 200);
    // }
}
