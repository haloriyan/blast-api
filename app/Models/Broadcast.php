<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'device_id', 'group_id', 
        'title', 'content', 'image', 'attachment',
        'delay_time', 'delivery_status', 'group_member', 'delivery_time'
    ];

    public function device() {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }
    public function group() {
        return $this->belongsTo(Group::class, 'group_id');
    }
    public function logs() {
        return $this->hasMany(BroadcastLog::class, 'broadcast_id');
    }
}
