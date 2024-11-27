<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpPartnerUser extends Model
{
    use HasFactory;

    protected $table = 'otps_partner_user';

    protected $casts = [
        'lead_data' => 'json',
    ];

    public $timestamps = false;
}
