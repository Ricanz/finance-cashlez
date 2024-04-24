<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalBatch extends Model
{
    use HasFactory;

    public function merchant()
    {
        return $this->belongsTo(InternalMerchant::class, 'merchant_id', 'id');
    }
}
