<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\InternalBatch;
use App\Models\InternalTransaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    public function job()
    {
        $url = 'https://api.cashlez.com/helper-service/finance-settlement-reconcile-by-date?settlement-date=2024-03-08';
        $client = new Client([
            'verify' => false,
            'timeout' => 240
        ]);

        DB::beginTransaction();
        try {
            $response = $client->request('GET', $url);
            $result = $response->getBody()->getContents();
            $decode = json_decode($result);

            foreach ($decode as $key => $value) {
                $batchDto = $value->batchDTO;
                $merchantDTO = $value->merchantDTO;
                $transactionAuthorizedDto = $value->transactionAuthorizedDto;

                $createdAt = Carbon::createFromFormat('Y-m-d', '2024-03-08');
                $batch = InternalBatch::create([
                    'batch_fk' => null,
                    'transaction_count' => $batchDto->transactionCount,
                    'status' => 'SUCCESSFUL',
                    'tid' => null,
                    'mid' => $batchDto->mid,
                    'merchant_name' => $merchantDTO->name,
                    'processor' => $batchDto->processor, 
                    'batch_running_no' => null,
                    'merchant_id' => $merchantDTO->id ,
                    'mid_ppn' => $batchDto->midPpn,
                    'transaction_amount' => $batchDto->transactionAmount,
                    'total_sales_amount' => $batchDto->totalSalesAmount,
                    'settlement_audit_id' => $batchDto->settlementAuditId,
                    'tax_payment' => $batchDto->taxPayment,
                    'fee_mdr_merchant' => $batchDto->feeMdrMerchant,
                    'fee_bank_merchant' => $batchDto->feeBankMerchant,
                    'bank_transfer' => $batchDto->bankTransfer,
                    'bank_id' => $batchDto->bankId,
                    'created_by' => 'kafka',
                    'created_at' => $createdAt,
                    'updated_at' => Carbon::now()
                ]);
                if ($batch) {
                    foreach ($transactionAuthorizedDto as $key => $trx) {
                        InternalTransaction::create([
                            'batch_id' => $batch->id,
                            'settlement_date' => $trx->settlementDate,
                            'retrieval_number' => $trx->retrievalNumber,
                            'transaction_amount' => $trx->transactionAmount,
                            'bank_payment' => $trx->bankPayment,
                            'merchant_payment' => $trx->merchantPayment,
                            'txid' => $trx->txid,
                            'bank_fee_amount' => $trx->bankFeeAmount,
                            'merchant_fee_amount' => $trx->merchantFeeAmount,
                            'transaction_type' => $trx->transactionType,
                            'tax_amount' => $trx->taxPayment
                        ]);
                    }
                }
            }

            DB::commit();
            return  response()->json(['message'=> "Successfully get data!", 'status' => true], 200);

            // $statusCode = $response->getStatusCode();
            // return $statusCode;
        } catch (RequestException $e) {
            DB::rollBack();
            dd($e);
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                return $statusCode;
            } else {
                return 500;
            }
        }
    }
}
