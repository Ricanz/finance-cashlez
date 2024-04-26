<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'title',
        'status',
        'created_by',
        'modified_by',
        'updated_at'
    ];

    public function privileges()
    {
        return $this->hasMany(Privilege::class, 'role_id', 'id');
    }
}
