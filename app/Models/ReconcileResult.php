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
        'bank_settlement_amount',
        'dispute_amount',
        'tax_payment',
        'fee_mdr_merchant',
        'fee_bank_merchant',
        'bank_transfer',
        'is_reconcile',
        'created_by',
        'modified_by',
        'settlement_date',
        'created_at',
        'updated_at'
    ];


    public function merchant()
    {
        return $this->belongsTo(InternalMerchant::class, 'merchant_id', 'id');
    }

    public function bank_account()
    {
        return $this->hasOneThrough(
            BankAccount::class,
            InternalMerchant::class,
            'id',
            'merchant_id',
            'merchant_id'
        );
    }
}
