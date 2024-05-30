<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'channel',
        'bank_reference',
        'status',
        'created_by'
    ];

    public function parameter()
    {
        return $this->hasOne(BankParameter::class, 'channel_id', 'bank_id');
    }
}
