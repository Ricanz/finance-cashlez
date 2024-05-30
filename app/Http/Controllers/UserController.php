<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('modules.users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $data = User::where('uuid', $uuid)->first();
        $role = Role::where('id', '!=', 1)->get();
        return view('modules.users.edit', compact('data', 'role'));
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
        DB::beginTransaction();
        try {
            $data = User::where('uuid', $request->uuid)->first();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->password = Hash::make($request->password);
            $data->role = $request->role_id;
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
    public function destroy($uuid)
    {
        $data = User::where('uuid', $uuid)->first();
        $data->status = 'deleted';
        if ($data->save()) {
            $response = [
                'success' => true,
                'message' => "Berhasil Hapus Data",
            ];
    
            return response()->json($response, 200);
        }
    }

    
    public function userData(Request $request) {
        $query = User::where('status', '!=', 'deleted')->orderByDesc('created_at');

        return DataTables::of($query->get())->addIndexColumn()->make(true);
    }
}
