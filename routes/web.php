<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\ReconcileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/job', [GeneralController::class, 'job'])->name('job');
// Route::get('/bank', [GeneralController::class, 'migrateBank'])->name('migrateBank');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/data', [UserController::class, 'userData'])->name('user.data');
    Route::get('/users/edit/{uuid}', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('user.update');
    Route::get('/users/destroy/{uuid}', [UserController::class, 'destroy'])->name('user.destroy');

    // Role
    Route::get('/roles', [RoleController::class, 'index'])->name('role.index');
    Route::get('/roles/data', [RoleController::class, 'data'])->name('role.data');
    Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/roles/update', [RoleController::class, 'update'])->name('role.update');
    Route::get('/roles/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
    Route::get('/role/detail/{slug}', [RoleController::class, 'detail'])->name('role.detail');
    Route::post('/privilege/update', [RoleController::class, 'privilege'])->name('role.privilege');

    // Bank
    Route::get('/banks', [BankController::class, 'index'])->name('bank.index');
    Route::get('/banks/data', [BankController::class, 'data'])->name('bank.data');
    Route::post('/banks/store', [BankController::class, 'store'])->name('bank.store');
    Route::get('/banks/edit/{id}', [BankController::class, 'edit'])->name('bank.edit');
    Route::post('/banks/update', [BankController::class, 'update'])->name('bank.update');
    Route::get('/banks/destroy/{id}', [BankController::class, 'destroy'])->name('bank.destroy');

    // Settlement
    Route::get('/settlement', [SettlementController::class, 'index'])->name('settlement.index');
    Route::get('/settlement/data', [SettlementController::class, 'data'])->name('settlement.data');
    Route::post('/settlement', [SettlementController::class, 'store'])->name('settlement.store');
    Route::get('/settlement/bo/data', [SettlementController::class, 'boSettlement'])->name('settlement.bo');
    Route::get('/settlement/bank/data', [SettlementController::class, 'bankSettlement'])->name('settlement.bank');


    // Reconcile
    Route::get('/reconcile', [ReconcileController::class, 'index'])->name('reconcile.index');
    Route::post('/reconcile', [ReconcileController::class, 'store'])->name('reconcile.store');
    Route::get('/reconcile/result', [ReconcileController::class, 'result'])->name('reconcile.result');
    Route::get('/reconcile/{token}/proceed', [ReconcileController::class, 'proceed'])->name('reconcile.proceed');
    Route::get('/reconcile/{token}/show', [ReconcileController::class, 'show'])->name('reconcile.show');
    Route::get('/reconcile/data', [ReconcileController::class, 'data'])->name('reconcile.data');
    Route::get('/reconcile/detail/{token}', [ReconcileController::class, 'detail'])->name('reconcile.detail');
    Route::get('/reconcile/detail/data/{token}', [ReconcileController::class, 'detailData'])->name('reconcile.detailData');
    Route::get('/reconcile/download', [ReconcileController::class, 'download'])->name('reconcile.download');
    Route::post('/reconcile/single', [ReconcileController::class, 'reconcile'])->name('reconcile.single');
    Route::post('/reconcile/channel', [ReconcileController::class, 'channel'])->name('reconcile.channel');

    // Modal Show
    Route::get('/mrc/{token}/detail', [ReconcileController::class, 'mrcDetail'])->name('reconcile.mrcDetail');

    // Disbursement
    Route::get('/disbursement', [DisbursementController::class, 'index'])->name('disbursment.index');

    
});

require __DIR__.'/auth.php';
