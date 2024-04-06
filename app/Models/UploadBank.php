<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadBank extends Model
{
    protected $fillable = [
        'token_applicant',
        'type',
        'url',
        'processor',
        'process_status',
        'start_recon_by',
        'created_by',
        'created_by',
        'modified_by',
        'file_id',
        'created_at',
        'updated_at'
    ];

    
    public function detail()
    {
        return $this->hasMany(UploadBankDetail::class, 'token_applicant', 'token_applicant');
    }
    
    // public function transactionTotal()
    // {
    //     return $this->hasMany(UploadBankDetail::class, 'token_applicant', 'token_applicant')->count();
    // }
    
    // public function creditTotal()
    // {
    //     return $this->hasMany(UploadBankDetail::class, 'token_applicant', 'token_applicant')->sum('');
    // }
    
    // public function debitTotal()
    // {
    //     return $this->hasMany(UploadBankDetail::class, 'token_applicant', 'token_applicant')->count();
    // }
}
