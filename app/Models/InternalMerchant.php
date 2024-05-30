<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMerchant extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'reference_code',
        'email',
        'created_by',
        'modified_by',
        'company_name'
    ];
}
