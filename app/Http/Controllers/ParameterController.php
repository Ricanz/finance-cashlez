<?php

namespace App\Http\Controllers;

use App\Models\BankParameter;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ParameterController extends Controller
{
    public function index()
    {
        $channel = Channel::where('status', '!=', 'deleted')->orderBy('channel')->get();
        $params = ['rrn', 'mid', 'vlookup', 'ftp_file', 'va_number', 'auth_code', 'shopeepay_sid'];
        return view('modules.parameters.index', compact('params', 'channel'));
    }

    public function data(Request $request) {
        $query = Channel::with('parameter')
                ->where('status', '!=', 'deleted')
                ->whereHas('parameter');

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }

    public function edit($id)
    {
        $data = Channel::with('parameter')->where('id', $id)->first();
        $params = ['rrn', 'mid', 'vlookup', 'ftp_file', 'va_number', 'auth_code', 'shopeepay_sid'];
        return view('modules.parameters.edit', compact('data', 'params'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = Channel::with('parameter')->where('id', $request->id)->first();
        $param = BankParameter::where('id', $data->parameter->id)->first();
        
        DB::beginTransaction();
        try {
            $param->report_partner = $request->report_partner;
            $param->bo_detail_transaction = $request->bo_detail_transaction;
            $param->bo_summary = $request->bo_summary;
            $param->bank_statement = $request->bank_statement;
            $param->created_by = $user->name;
            if ($param->save()) {
                DB::commit();
                return  response()->json(['message'=> "Successfully update data!", 'status' => true], 200);
            }
            DB::rollBack();
            return  response()->json(['message'=> "Failed update data!", 'status' => false], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return  response()->json(['message'=> "Failed update data!", 'status' => false], 200);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $store = BankParameter::create([
                'channel_id' => $request->bank_id,
                'report_partner'=> $request->report_partner,
                'bo_detail_transaction' => $request->bo_detail_transaction,
                'bo_summary' => $request->bo_summary,
                'bank_statement' => $request->bank_statement,
                'created_by' => $user->name
            ]);

            if ($store) {
                DB::commit();
                return  response()->json(['message'=> "Successfully store data!", 'status' => true], 200);
            }
            DB::rollBack();
            return  response()->json(['message'=> "Failed store data!", 'status' => false], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return  response()->json(['message'=> "Failed store data!", 'status' => false], 200);
        }
    }

    public function destroy($id)
    {
        $data = BankParameter::where('id', $id)->first();
        if ($data->delete()) {
            $response = [
                'success' => true,
                'message' => "Berhasil Hapus Data",
            ];
    
            return response()->json($response, 200);
        }
    }
}
