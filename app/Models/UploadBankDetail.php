<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadBankDetail extends Model
{
    protected $fillable = [
        'token_applicant',
        'account_no',
        'mid',
        'merchant_name',
        'amount_debit',
        'amount_credit',
        'transfer_date',
        'date',
        'statement_code',
        'type_code',
        'description1',
        'description2',
        'created_by',
        'modified_by',
        'is_reconcile',
        'created_at',
        'updated_at'
    ];

    public function header()
    {
        return $this->belongsTo(UploadBank::class, 'token_applicant', 'token_applicant');
    }

}
