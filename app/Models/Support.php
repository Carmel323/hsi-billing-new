<?php

namespace App\Models;

use App\Mail\Email;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class Support extends Model
{
    use HasFactory;

    protected $casts = [
        'attributes' => 'json',
    ];
}
