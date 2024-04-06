<?php

use App\Http\Controllers\Api\GeneralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('portal/login', [GeneralController::class, 'portalLogin']);
Route::get('test', [GeneralController::class, 'test']);
Route::post('/file/check', [GeneralController::class, 'check'])->name('fileCheck');
Route::post('/file/upload', [GeneralController::class, 'upload'])->name('fileUpload');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
