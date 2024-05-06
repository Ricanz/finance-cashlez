<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\InternalBatch;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    public function job()
    {
        $url = 'https://api.cashlez.com/helper-service/finance-settlement-reconcile-by-date?settlement-date=2024-03-04';
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
                // if ($batchDto->mid == '000002019001637') {
                //     dd($batchDto);
                // }

                $merchantDTO = $value->merchantDTO;

                $createdAt = Carbon::createFromFormat('Y-m-d', '2024-03-04');
                $batch = InternalBatch::create([
                    'batch_fk' => $batchDto->batchId,
                    'transaction_count' => $batchDto->transactionCount,
                    'status' => $batchDto->status,
                    'tid' => $batchDto->tid,
                    'mid' => $batchDto->mid,
                    'merchant_name' => $merchantDTO->name,
                    'processor' => $batchDto->processor, 
                    'batch_running_no' => $batchDto->batchRunningNo,
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
