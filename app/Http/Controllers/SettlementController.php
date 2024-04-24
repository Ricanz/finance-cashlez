<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\InternalBatch;
use App\Models\InternalMerchant;
use App\Models\InternalTransaction;
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
                            if ($index == 5) {
                                $name = 'description_2';
                            } else if ($index == 4) {
                                $name = 'description_1';
                            }
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
                    $splits = explode('/', $value['description_2']);
                    if ($value['description_1'] == 'Pbyrn Merchant ') {
                        $typeCode = '001';
                    } else {
                        $typeCode = '002';
                    }
                    UploadBankDetail::create([
                        'token_applicant' => $upload->token_applicant,
                        'account_no' => $value['account_no'],
                        'amount_debit' => str_replace(',', '', $value['debit']),
                        'amount_credit' => str_replace(',', '', $value['credit']),
                        'transfer_date' => $value['val_date'],
                        'date' => $value['date'],
                        'statement_code' => $value['reference_no'],
                        'type_code' => $typeCode,
                        'description1' => $value['description_1'],
                        'mid' => $splits[0] ? preg_replace('/\s+/','',$splits[0]) : '-',
                        'merchant_name' => isset($splits[1]) ? preg_replace('/\s+/','',$splits[1]) : '-',
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

    public function boSettlement(Request $request) 
    {
        $query = InternalTransaction::with('header', 'merchant');
        $query = InternalBatch::with('merchant');
        if ($request->filled('bank')) {
            $query->where(DB::raw('LOWER(processor)'), strtolower($request->bank));
            // $query->whereHas('header', function ($query) use ($request) {
            //     $query->where(DB::raw('LOWER(processor)'), strtolower($request->bank));
            // });
        }
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            
            $query->where(DB::raw('DATE(created_at)'), '>=', $startDate);
            $query->where(DB::raw('DATE(created_at)'), '<=', $endDate);
        }
        $query->orderBy('created_at');
        
        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }

    public function bankSettlement(Request $request) 
    {
        $query = UploadBankDetail::with('header')->where('type_code', '001')->where('amount_credit', '>', 0);
        if ($request->filled('bank')) {
            $query->whereHas('header', function ($query) use ($request) {
                $query->where('processor', $request->bank);
            });
        }
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = date('d/m/Y', strtotime($request->startDate));
            $endDate = date('d/m/Y', strtotime($request->endDate));
        
            $query->where('transfer_date', '>=', $startDate);
            $query->where('transfer_date', '<=', $endDate);
        }
        $query->orderByDesc('id');
        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }
}
