<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'report_partner',
        'bo_detail_transaction',
        'bo_summary',
        'bank_statement',
        'created_by',
        'created_at',
        'updated_at'
    ];
}
