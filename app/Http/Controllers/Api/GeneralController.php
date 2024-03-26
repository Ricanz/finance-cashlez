<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RestController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

class GeneralController extends RestController
{
    
    public function portalLogin(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            return RestController::sendResponse(null, "Berhasill Login");
        } catch (\Throwable $th) {
            return RestController::sendError(null, "Gagal Login");
        }
    }

    public function test()
    {
        dd("hi");
    }
}
