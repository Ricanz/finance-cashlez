<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransaction extends Model
{
    protected $fillable = [
        'settelment_date',
        'retrieval_number',
        'transaction_amount',
        'bank_payment',
        'txid',
        'batch_fk',
        'bank_fee_amount',
        'merchant_fee_amount',
        'tax_amount',
        'transaction_type',
        'status',
        'comparator_code'
    ];

    public function header()
    {
        return $this->belongsTo(InternalBatch::class, 'batch_fk', 'batch_fk');
    }


    public function merchant()
    {
        return $this->hasOneThrough(
            InternalMerchant::class,
            InternalBatch::class,
            'batch_fk', // Foreign key on internal_batches table
            'id', // Local key on internal_transactions table
            'batch_fk', // Foreign key on internal_merchants table
            'merchant_id' // Local key on internal_batches table
        );
    }
}
