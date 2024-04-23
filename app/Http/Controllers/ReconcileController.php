<?php

namespace App\Http\Controllers;

use App\Exports\ReconcileExport;
use App\Models\Bank;
use App\Models\InternalBatch;
use App\Models\InternalMerchant;
use App\Models\InternalTransaction;
use App\Models\ReconcileResult;
use App\Models\UploadBank;
use App\Models\UploadBankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReconcileController extends Controller
{
    public function index(){
        $banks = Bank::where('status', 'active')->get();

        return view('modules.reconcile.index', compact('banks'));
    }

    public function allData(Request $request){
        $query = ReconcileResult::with('merchant', 'bank_account');
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

    public function proceed($token_applicant)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
        $data = UploadBank::where('token_applicant', $token_applicant)->first();
        if ($data) {
            $details = UploadBankDetail::where('token_applicant', $token_applicant)->where('type_code', '001')->get();
            foreach ($details as $key => $value) {
                $batch_fk = '';
                $tid = '';

                $internalBatch = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->get();
                $sumTransaction = InternalBatch::where('mid', 'like', '%' . $value->mid . '%')->sum('transaction_amount');
                $trxCount = 0;
                $trxSum = 0;
                $trxAmount = 0;
                $trxMerchantSum = 0;
                $sumCreditAmount = 0;
                if ($internalBatch) {
                    foreach ($internalBatch as $key => $batch) {
                        $merchant_id = $batch->merchant_id;

                        $batch_fk .= $batch->batch_fk . ', ';
                        $tid .= $batch->tid . ', ';
                        $trxCount = $trxCount + $batch->transaction_count;

                        $select = InternalTransaction::selectRaw('
                                        SUM(bank_payment) as bank_payment, 
                                        SUM(transaction_amount) as transaction_amount,
                                        SUM(merchant_fee_amount) as sum_merchant_fee')
                                    ->where('batch_fk', $batch->batch_fk)->first();

                        $trxSum = $trxSum + $select->bank_payment;
                        $sumCreditAmount = $sumCreditAmount + (float)$value->amount_credit;

                        $trxAmount = $trxAmount + $select->transaction_amount;

                        $calculateMerchant = (float)$select->bank_payment - (float)$select->sum_merchant_fee;
                        $trxMerchantSum = $trxMerchantSum + $calculateMerchant;
                    }

                    $batch_fk = Str::beforeLast($batch_fk, ', ');
                    $tid = Str::beforeLast($tid, ', ');

                    $rounded_value = round((int)$sumCreditAmount);
                    $amount_credit = number_format($rounded_value, 0, '', '');

                    $diff = abs((float)$trxSum - (float)$sumCreditAmount);
                    
                    if ($diff == 1 || $diff == 0) {
                        $status = 'MATCH';
                    } else if ((float)$select->bank_payment !== (float)$value->amount_credit) {
                        $status = 'NOT_MATCH';
                    } else {
                        $status = 'NOT_FOUND';
                    }

                    $reconcile = ReconcileResult::create([
                        'token_applicant' => $token_applicant,
                        'statement_id' => $value->id,
                        'request_id' => $data->id,
                        'status' => $status,
                        'tid' => $tid,
                        'mid' => $value->mid,
                        'batch_fk' => $batch_fk,
                        'trx_counts' => $trxCount, // total transaksi 1 batch
                        'total_sales' => $trxAmount, // sum transaction_amout di internal_taransaction 
                        'processor_payment' => $data->processor,
                        'internal_payment' => $trxSum, // bank_payment
                        'merchant_payment' => $trxMerchantSum, // bank_payment - merchant_fee_amount
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
                        'settlement_date' => $internalBatch[0]->created_at
                    ]);
                    if ($reconcile) {
                        $det = UploadBankDetail::where('id', $value->id)->first();
                        $det->is_reconcile = true;
                        $det->save();

                        $data->is_reconcile = true;
                        $data->save();
                    }
                }
            }
        }
        DB::commit();
        return  response()->json(['message' => 'Successfully upload data!', 'status' => true], 200);

        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            return  response()->json(['message' => 'Error while uploading, try again', 'status' => false], 200);
        }
    }

    public function show($token_applicant){
        $match = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->count();
        $dispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->count();
        $onHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->count();

        $sumMatch = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'MATCH')->sum('total_sales');
        $sumDispute = ReconcileResult::where('token_applicant', $token_applicant)->whereIn('status', ['NOT_MATCH', 'NOT_FOUND'])->sum('total_sales');
        $sumHold = ReconcileResult::where('token_applicant', $token_applicant)->where('status', 'NOT_FOUND')->sum('total_sales');
        
        return view('modules.reconcile.show', compact('match', 'dispute', 'onHold', 'token_applicant', 'sumMatch', 'sumDispute', 'sumHold'));
    }

    public function data(Request $request, $token_applicant){
        $query = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $token_applicant);
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

    public function download($token_applicant) 
    {
        $filename = date('d-m-Y');
        return Excel::download(new ReconcileExport($token_applicant), 'reconcile'.$filename.'.xlsx');
    }

    public function mrcDetail($token_applicant)
    {
        $data = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $token_applicant)->first();

        return  response()->json(['data' => $data, 'message' => 'Successfully get data!', 'status' => true], 200);
    }
}
