<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_applicant',
        'date',
        'description',
        'ftp_file',
        'number_va',
        'auth_code',
        'sid',
        'rrn',
        'net_amount',
        'channel',
        'is_reconcile'
    ];


    public function header()
    {
        return $this->belongsTo(UploadBank::class, 'token_applicant', 'token_applicant');
    }
}
