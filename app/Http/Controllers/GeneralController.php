<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\Channel;
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
                            'tax_amount' => $trx->taxPayment,
                            'bank_id' => $batchDto->bankId,
                            'created_at'=> $createdAt,
                            'updated_at' => Carbon::now()
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

    public function migrateBank()
    {
        $json = '
            [
                {
                    "bankId": 1,
                    "bankName": "PT. Bank Mandiri (Persero) Tbk-B2B",
                    "bankReference": "00080000"
                },
                {
                    "bankId": 2,
                    "bankName": "PT Bank Maybank Indonesia Tbk",
                    "bankReference": "00160000"
                },
                {
                    "bankId": 3,
                    "bankName": "Bank Angkasa Jaya",
                    "bankReference": "00300000"
                },
                {
                    "bankId": 4,
                    "bankName": "Bank Maggie Z",
                    "bankReference": "00301234"
                },
                {
                    "bankId": 5,
                    "bankName": "PT. Bank Mandiri (Persero) Tbk.-B2C",
                    "bankReference": "00080001"
                },
                {
                    "bankId": 6,
                    "bankName": "PT Bank Maybank Indonesia Tbk-MIGS",
                    "bankReference": "10160000"
                },
                {
                    "bankId": 7,
                    "bankName": "PT Bank Negara Indonesia (Persero) Tbk-B2C",
                    "bankReference": "00090000"
                },
                {
                    "bankId": 8,
                    "bankName": "PT Bank Negara Indonesia (Persero) Tbk-B2B",
                    "bankReference": "00090001"
                },
                {
                    "bankId": 9,
                    "bankName": "Dimo Pay",
                    "bankReference": "99990000"
                },
                {
                    "bankId": 17,
                    "bankName": "LinkAja",
                    "bankReference": "09110001"
                },
                {
                    "bankId": 18,
                    "bankName": "Bank Tabungan Pensiunan Nasional",
                    "bankReference": "02130000"
                },
                {
                    "bankId": 19,
                    "bankName": "PT. Bank Mandiri (Persero) Tbk.-VA",
                    "bankReference": "00080002"
                },
                {
                    "bankId": 20,
                    "bankName": "PT. Bank Mandiri (Persero) Tbk.-Mandiri Pay",
                    "bankReference": "00080003"
                },
                {
                    "bankId": 21,
                    "bankName": "OVO",
                    "bankReference": "15030000"
                },
                {
                    "bankId": 22,
                    "bankName": "GOPAY",
                    "bankReference": "10110000"
                },
                {
                    "bankId": 23,
                    "bankName": "GOPAY",
                    "bankReference": "10110001"
                },
                {
                    "bankId": 24,
                    "bankName": "KREDIVO",
                    "bankReference": "10220001"
                },
                {
                    "bankId": 25,
                    "bankName": "KREDIVO",
                    "bankReference": "10220000"
                },
                {
                    "bankId": 28,
                    "bankName": "DANA Indonesia",
                    "bankReference": "10240000"
                },
                {
                    "bankId": 29,
                    "bankName": "VA Artajasa",
                    "bankReference": "10230000"
                },
                {
                    "bankId": 30,
                    "bankName": "PT Bank CIMB Niaga Tbk",
                    "bankReference": "00220000"
                },
                {
                    "bankId": 32,
                    "bankName": "SHOPEEPAY",
                    "bankReference": "10250000"
                },
                {
                    "bankId": 33,
                    "bankName": "ShopeePay",
                    "bankReference": "10250001"
                },
                {
                    "bankId": 34,
                    "bankName": "bank testing",
                    "bankReference": "00305555"
                },
                {
                    "bankId": 35,
                    "bankName": "PT Bank CIMB Niaga Tbk",
                    "bankReference": "00220001"
                },
                {
                    "bankId": 36,
                    "bankName": "PT Bank Rakyat Indonesia MIGS (Persero) Tbk-B2C",
                    "bankReference": "10020002"
                },
                {
                    "bankId": 37,
                    "bankName": "PT Bank Permata Tbk-VA",
                    "bankReference": "10260000"
                },
                {
                    "bankId": 39,
                    "bankName": "Vospay",
                    "bankReference": "10270001"
                },
                {
                    "bankId": 40,
                    "bankName": "Nobu bank",
                    "bankReference": "10280002"
                },
                {
                    "bankId": 41,
                    "bankName": "PT Bank Mandiri ( Persero ) Tbk - EMoney",
                    "bankReference": "00080004"
                },
                {
                    "bankId": 42,
                    "bankName": "PT. Bank Central Asia Tbk - Virtual Account",
                    "bankReference": "10290000"
                },
                {
                    "bankId": 43,
                    "bankName": "ShopeePay-Online",
                    "bankReference": "10250002"
                },
                {
                    "bankId": 44,
                    "bankName": "Atome-B2B",
                    "bankReference": "10300001"
                },
                {
                    "bankId": 45,
                    "bankName": "DBS",
                    "bankReference": "00460000"
                },
                {
                    "bankId": 46,
                    "bankName": "PT Bank CIMB Niaga Tbk-B2B",
                    "bankReference": "00220002"
                },
                {
                    "bankId": 47,
                    "bankName": "Nobu Dynamic QRIS",
                    "bankReference": "10280001"
                },
                {
                    "bankId": 48,
                    "bankName": "Indodana Offline",
                    "bankReference": "10310000"
                },
                {
                    "bankId": 49,
                    "bankName": "Indodana Offline_B2B",
                    "bankReference": "10310001"
                },
                {
                    "bankId": 50,
                    "bankName": "Atome-B2C",
                    "bankReference": "10300002"
                },
                {
                    "bankId": 51,
                    "bankName": "BSI Qris",
                    "bankReference": "04510001"
                },
                {
                    "bankId": 52,
                    "bankName": "PT. Bank Mandiri (Persero) Tbk.- MTI",
                    "bankReference": "10080000"
                },
                {
                    "bankId": 53,
                    "bankName": "OVO Qris",
                    "bankReference": "15030002"
                },
                {
                    "bankId": 54,
                    "bankName": "Doku Qris",
                    "bankReference": "10340001"
                },
                {
                    "bankId": 55,
                    "bankName": "Cashlez QRIS",
                    "bankReference": "66600001"
                },
                {
                    "bankId": 56,
                    "bankName": "Motion Qris",
                    "bankReference": "10350000"
                }
            ]
        ';

        $array = json_decode($json, true);
        
        DB::beginTransaction();
        try {
            foreach ($array as $key => $value) {
                Channel::create([
                    'bank_id' => $value['bankId'],
                    'channel' => $value['bankName'],
                    'bank_reference' => $value['bankReference'],
                    'status' => 'active'
                ]);
            }
            DB::commit();
            return  response()->json(['message'=> "Successfully get data!", 'status' => true], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
