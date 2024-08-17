<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_id', 'transaction_id', 'package_name', 'quantity', 'price', 'total_pay', 'active_until',
        'payment_method', 'payment_channel', 'payment_status', 'payment_payload'
    ];

    public function user() {
        return $this->belongsto(User::class, 'user_id');
    }
}
