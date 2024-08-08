<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadcast_id', 'contact_id', 'status'
    ];

    public function contact() {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
