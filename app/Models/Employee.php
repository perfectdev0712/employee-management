<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'email',
        'address',
        'bank_details',
        'aadhar_card',
        'pan_card',
        'resume',
        'password',
        'photo',
        'wallet_balance'
    ];

    protected $casts = [
        'wallet_balance' => 'double',
    ];
}
