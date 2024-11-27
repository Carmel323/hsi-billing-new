<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;

    protected $casts = [
        'payment_details' => 'json',
        'invoice_items' => 'json',
        'subscription_id' => 'json',
    ];
}
