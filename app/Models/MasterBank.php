<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBank extends Model
{
    use HasFactory;
    protected $fillable = [
        'remark',
        'version',
        'created_by',
        'created_date',
        'modified_by',
        'modified_date',
        'activate_date',
        'status',
        'suspend_date',
        'terminate_date',
        'business_address1',
        'business_address2',
        'business_contact',
        'business_registration_number',
        'city',
        'postcode',
        'state',
        'bank_name',
        'setting_fk',
        'bank_reference',
        'receipt_footer_message',
        'virtual_mid_tid',
        'bank_status',
        'cashlez_account',
    ];
}
