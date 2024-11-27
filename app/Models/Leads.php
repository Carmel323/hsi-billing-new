<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    use HasFactory;

    protected $table = 'leads';

    protected $casts = [
        'availability_data' => 'json',
        'company_info' => 'json',
    ];
}
