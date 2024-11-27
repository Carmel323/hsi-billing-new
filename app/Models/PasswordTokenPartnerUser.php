<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordTokenPartnerUser extends Model
{
    use HasFactory;

    protected $table = 'password_tokens_partner_user';
}
