<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('modules.banks.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $store = Bank::create([
                'name' => $request->name,
                'status' => 'active',
                'created_by' => $user->name,
                'modified_by' => $user->name,
                'created_at' => $now,
                'updatted_at' => $now
            ]);
            if ($store) {
                DB::commit();
                return  response()->json(['message'=> "Successfully store data!", 'status' => true], 200);
            }
            DB::rollBack();
            return  response()->json(['message'=> "Failed store data!", 'status' => true], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return  response()->json(['message'=> "Failed store data!", 'status' => true], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Channel::where('id', $id)->first();
        return view('modules.banks.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $data = Bank::where('id', $request->id)->first();
            $data->name = $request->name;
            $data->modified_by = $user->name;
            if ($data->save()) {
                DB::commit();
                return  response()->json(['message'=> "Successfully update data!", 'status' => true], 200);
            }
        } catch (\Throwable $th) {
            DB::commit();
            return  response()->json(['message'=> "Failed update data!", 'status' => false], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
     public function destroy($id)
     {
        $user = Auth::user();
         $data = Bank::where('id', $id)->first();
         $data->status = 'deleted';
         $data->modified_by = $user->name;
         if ($data->save()) {
             $response = [
                 'success' => true,
                 'message' => "Berhasil Hapus Data",
             ];
     
             return response()->json($response, 200);
         }
     }
 
     public function data(Request $request) {
         $query = Channel::where('status', '!=', 'deleted')->orderBy('channel');

         return DataTables::of($query->get())->addIndexColumn()->make(true);
     }
}
