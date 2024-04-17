<?php

namespace App\Http\Controllers;

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
use Yajra\DataTables\Facades\DataTables;

class ReconcileController extends Controller
{
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
                if ($internalBatch) {
                    foreach ($internalBatch as $key => $batch) {
                        $merchant_id = $batch->merchant_id;

                        $batch_fk .= $batch->batch_fk . ', ';
                        $tid .= $batch->tid . ', ';
                        $trxCount = $trxCount + $batch->transaction_count;

                        $select = InternalTransaction::selectRaw('
                                        SUM(bank_payment) as bank_payment, 
                                        SUM(transaction_amount) as transaction_amount,
                                        SUM(merchant_fee_amount) as sum_merchant_fee')->where('batch_fk', $batch->batch_fk)->first();

                        if ((float)$select->bank_payment === (float)$value->amount_credit) {
                            $status = 'MATCH';
                        } else if ((float)$select->bank_payment === (float)$value->amount_credit) {
                            $status = 'NOT_MATCH';
                        } else {
                            $status = 'MATCH';
                        }
                        $trxSum = $trxSum + $select->bank_payment;

                        $trxAmount = $trxAmount + $select->transaction_amount;

                        $calculateMerchant = (float)$select->bank_payment - (float)$select->sum_merchant_fee;
                        $trxMerchantSum = $trxMerchantSum + $calculateMerchant;
                    }

                    $batch_fk = Str::beforeLast($batch_fk, ', ');
                    $tid = Str::beforeLast($tid, ', ');

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
        return view('modules.reconcile.index');
    }

    public function data($token_applicant){
        $query = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $token_applicant)->get();
        // dd($query[0]);
        return DataTables::of($query)->addIndexColumn()->make(true);
    }
}
