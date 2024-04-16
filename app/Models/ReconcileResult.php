<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconcileResult extends Model
{
    protected $fillable = [
        'token_applicant',
        'statement_id',
        'request_id',
        'status',
        'tid',
        'mid',
        'batch_fk',
        'trx_counts',
        'total_sales',
        'processor_payment',
        'internal_payment',
        'merchant_payment',
        'merchant_name',
        'merchant_id',
        'transfer_amount',
        'tax_payment',
        'fee_mdr_merchant',
        'fee_bank_merchant',
        'bank_transfer',
        'is_reconcile',
        'created_by',
        'modified_by',
        'created_at',
        'updated_at'
    ];
}
