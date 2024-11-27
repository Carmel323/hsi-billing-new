<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = ['plan_code', 'features_json'];


    protected $casts = [
        'features_json' => 'array'
    ];
}
