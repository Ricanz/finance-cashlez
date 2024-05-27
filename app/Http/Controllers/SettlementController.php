<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\Bank;
use App\Models\BankParameter;
use App\Models\Channel;
use App\Models\InternalBatch;
use App\Models\InternalMerchant;
use App\Models\InternalTransaction;
use App\Models\ReportPartner;
use App\Models\UploadBank;
use App\Models\UploadBankDetail;
use Carbon\Carbon;
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
        $banks = Channel::with('parameter')
            ->where('status', 'active')
            ->whereHas('parameter')
            ->get();
        return view('modules.settlement.index', compact('banks'));
    }

    public function data()
    {
        // $query = UploadBank::with('detail', 'channel')->get();
        $query = UploadBank::with('channel')->get();
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
        $splitDate = explode(' - ', $request->range_date);
        $startDate = Carbon::createFromFormat('m/d/Y', $splitDate[0]);
        $endDate = Carbon::createFromFormat('m/d/Y', $splitDate[1]);

        $user = Auth::user();
        if ($request->hasFile('file') or $request->hasFile('filePartner')) {
            DB::beginTransaction();
            try {
                $upload = UploadBank::create([
                    'token_applicant' => Str::uuid(),
                    'type' => 'API',
                    'url' => $request->url,
                    'processor' => $request->bank,
                    'process_status' => 'COMPLETED',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'created_by' => $user->name,
                    'updated_by' => $user->name
                ]);
                if (!$upload) {
                    DB::rollBack();
                    return  response()->json(['message' => 'Error while uploading, try again', 'status' => false], 200);
                }
                if ($request->hasFile('file')) {
                    $file = $request->file('file');

                    $mappedData = [];
                    if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                        $header = fgetcsv($handle);

                        while (($row = fgetcsv($handle)) !== false) {
                            $mappedRow = [];
                            foreach ($header as $index => $columnName) {
                                $column = strtolower(str_replace(" ", "_", trim($columnName)));
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
                        $credit_amount = (float)str_replace('.', '', $value['credit_amount']);
                        $debit_amount = (float)str_replace('.', '', $value['debit_amount']);

                        UploadBankDetail::create([
                            'token_applicant' => $upload->token_applicant,
                            'account_no' => isset($value['account_number']) ? $value['account_number'] : '',
                            'transfer_date' => Carbon::createFromFormat('d/m/y', $value['date']),
                            'description2' => $value['channel'],
                            'description1' => $value['description'],
                            'type_code' => $credit_amount > 0 ? '001' : '002',
                            'amount_debit' => $debit_amount,
                            'amount_credit' => $credit_amount,
                            'mid' => $value['mid'],
                            'created_by' => $user->name,
                            'modified_by' => $user->name
                        ]);
                    }
                }
                if ($request->hasFile('filePartner')) {
                    $filePartner = $request->file('filePartner');

                    $mappedDataPartner = [];
                    if (($handlePartner = fopen($filePartner->getPathname(), 'r')) !== false) {
                        $headerPartner = fgetcsv($handlePartner);

                        while (($row = fgetcsv($handlePartner)) !== false) {
                            $mappedRowPartner = [];
                            foreach ($headerPartner as $index => $columnName) {
                                $column = strtolower(str_replace(" ", "_", trim($columnName)));
                                $name = strtolower(str_replace(".", "", $column));
                                $mappedRowPartner[$name] = $row[$index] ?? null;
                            }

                            $mappedDataPartner[] = $mappedRowPartner;
                        }
                        fclose($handlePartner);
                    } else {
                        DB::rollBack();
                        return  response()->json(['message' => 'Failed to open CSV file', 'status' => false], 200);
                    }
                    foreach ($mappedDataPartner as $key => $value) {
                        $net_amount = (float)str_replace('.', '', $value['net_amount']);
                        $date = Carbon::createFromFormat('d/m/y', $value['date']);
                        ReportPartner::create([
                            'token_applicant' => $upload->token_applicant,
                            'date' => $date,
                            'description' => $value['description'],
                            'ftp_file' => $value['ftp_file'],
                            'number_va' => $value['number_va'],
                            'auth_code' => $value['auth_code'],
                            'sid' => $value['shopeepay_sid'],
                            'rrn' => $value['retrieval_reference_number'],
                            'net_amount' => $net_amount,
                            'channel' => $value['channel'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
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
            $query->where('bank_id', $request->bank);
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

    public function boDetailSettlement(Request $request)
    {
        $query = InternalTransaction::with('channel')->orderBy('created_at', 'asc');

        if ($request->filled('bank')) {
            $query->whereHas('header', function ($query) use ($request) {
                $query->where('bank_id', $request->bank);
            });
        }
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = $request->startDate;
            $endDate = $request->endDate;

            $query->where(DB::raw('DATE(created_at)'), '>=', $startDate);
            $query->where(DB::raw('DATE(created_at)'), '<=', $endDate);
        }

        return DataTables::of($query)->addIndexColumn()->make(true);
    }

    public function bankSettlement(Request $request)
    {
        try {
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
            dd("here");
            return DataTables::of($query)->addIndexColumn()->make(true);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function partnerReport(Request $request)
    {
        $query = ReportPartner::with('header')->where('net_amount', '>', 0);
        if ($request->filled('bank')) {
            $query->whereHas('header', function ($query) use ($request) {
                $query->where('processor', $request->bank);
            });
        }
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = date('d/m/Y', strtotime($request->startDate));
            $endDate = date('d/m/Y', strtotime($request->endDate));

            $query->where('date', '>=', $startDate);
            $query->where('date', '<=', $endDate);
        }
        $query->orderByDesc('id');
        return DataTables::of($query)->addIndexColumn()->make(true);
    }
}
