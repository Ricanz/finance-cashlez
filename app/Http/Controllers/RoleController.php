<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('modules.roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Role::where('id', $id)->first();
        return view('modules.roles.edit', compact('data'));
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
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $data = Role::where('id', $request->id)->first();
            $data->title = $request->title;
            $data->modified_by = $user->name;
            $data->updated_at = $now;
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
        $now = Carbon::now();

        $data = Role::where('id', $id)->first();
        $data->status = 'deleted';
        $data->modified_by = $user->name;
        $data->updated_at = $now;
        if ($data->save()) {
            $response = [
                'success' => true,
                'message' => "Berhasil Hapus Data",
            ];
    
            return response()->json($response, 200);
        }
    }

    public function data(Request $request) {
        $query = Role::where('status', '!=', 'deleted')->orderBy('created_at');

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }
}
