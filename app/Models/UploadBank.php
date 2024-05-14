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
        'is_reconcile',
        'created_by',
        'created_by',
        'modified_by',
        'file_id',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    public function detail()
    {
        return $this->hasMany(UploadBankDetail::class, 'token_applicant', 'token_applicant');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'processor', 'bank_id');
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
