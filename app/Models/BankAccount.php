<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'merchant_id',
        'account_number',
        'account_holder',
        'bank_code',
        'bank_name',
        'modified_by',
        'updated_at'
    ];
}
