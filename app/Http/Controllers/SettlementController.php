<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\InternalMerchant;
use App\Models\UploadBank;
use App\Models\UploadBankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SettlementController extends Controller
{
    public function index()
    {
        $banks = Bank::where('status', 'active')->get();
        return view('modules.settlement.index', compact('banks'));
    }

    public function data()
    {
        $query = UploadBank::with('detail')->get();
        $query->transform(function ($upload) {
                $upload->credit_total = UploadBankDetail::where('token_applicant', $upload->token_applicant)->where('amount_credit', '>', 0)->count();
                $upload->debit_total = UploadBankDetail::where('token_applicant', $upload->token_applicant)->where('amount_debit', '>', 0)->count();
                $upload->credit_sum = UploadBankDetail::where('token_applicant', $upload->token_applicant)->sum('amount_credit');
                $upload->debit_sum = UploadBankDetail::where('token_applicant', $upload->token_applicant)->sum('amount_debit');
            return $upload;
        });
        
        return DataTables::of($query)->addIndexColumn()->make(true);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $mappedData = [];

            DB::beginTransaction();
            try {
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    $upload = UploadBank::create([
                        'token_applicant' => Str::uuid(),
                        'type' => 'API',
                        'url' => $request->url,
                        'processor' => $request->bank,
                        'process_status' => 'COMPLETED',
                        'created_by' => $user->name,
                        'updated_by' => $user->name
                    ]);
                    if (!$upload) {
                        DB::rollBack();
                        return  response()->json(['message' => 'Error while uploading, try again', 'status' => false], 200);
                    }
                    $header = fgetcsv($handle);

                    while (($row = fgetcsv($handle)) !== false) {
                        $mappedRow = [];
                        foreach ($header as $index => $columnName) {
                            $column = strtolower(str_replace(" ", "_", $columnName));
                            $name = strtolower(str_replace(".", "", $column));
                            $mappedRow[$name] = $row[$index] ?? null;
                        }

                        $mappedData[] = $mappedRow;
                    }

                    fclose($handle);
                } else {
                    DB::rollBack();
                    return  response()->json(['message' => 'Failed to open CSV file', 'status' => false], 200);
                }

                foreach ($mappedData as $key => $value) {
                    UploadBankDetail::create([
                        'token_applicant' => $upload->token_applicant,
                        'account_no' => $value['account_no'],
                        'amount_debit' => str_replace(',', '', $value['debit']),
                        'amount_credit' => str_replace(',', '', $value['credit']),
                        'transfer_date' => $value['val_date'],
                        'date' => $value['date'],
                        'statement_code' => $value['reference_no'],
                        'description1' => $value['description'],
                        'created_by' => $user->name,
                        'modified_by' => $user->name
                    ]);
                }
                DB::commit();
                return  response()->json(['message' => 'Successfully upload data!', 'status' => true], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                return  response()->json(['message' => 'Error while uploading, try again', 'status' => false], 200);
            }
        } else {
            return  response()->json(['message' => 'No file uploaded', 'status' => false], 200);
        }
    }

    // Normal CSV Format
    // public function store(Request $request)
    // {
    //     // Periksa apakah file CSV dikirimkan dalam permintaan
    //     if ($request->hasFile('file')) {
    //         // Ambil file CSV dari permintaan
    //         $file = $request->file('file');

    //         // Baca konten file CSV
    //         $reader = Reader::createFromPath($file->getPathname(), 'r');
    //         $reader->setHeaderOffset(0); // Header offset

    //         // Inisialisasi array untuk menyimpan data hasil pemetaan
    //         $mappedData = [];

    //         // Loop melalui baris-baris CSV
    //         foreach ($reader as $row) {
    //             dd($row);
    //             // Lakukan pemetaan bidang di sini sesuai dengan header CSV Anda
    //             // Contoh: memetakan bidang "nama" dan "email"
    //             $mappedRow = [
    //                 'nama' => $row['nama'],
    //                 'email' => $row['email']
    //                 // Tambahkan bidang lain sesuai kebutuhan
    //             ];

    //             // Tambahkan data hasil pemetaan ke dalam array
    //             $mappedData[] = $mappedRow;
    //         }

    //         // Sekarang $mappedData berisi data CSV dengan setiap bidang dipetakan sesuai dengan header

    //         // Lakukan apa pun yang Anda butuhkan dengan data yang dipetakan, seperti menyimpannya ke database
    //         // Contoh:
    //         // foreach ($mappedData as $data) {
    //         //     User::create($data);
    //         // }

    //         return response()->json(['success' => true, 'data' => $mappedData]);
    //     } else {
    //         // Tangani kasus di mana file tidak dikirimkan
    //         return response()->json(['error' => 'No file uploaded'], 400);
    //     }
    // }
}
