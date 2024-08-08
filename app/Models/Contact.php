<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'whatsapp', 'email', 'groups', 'country_code'
    ];

    public function groupsJoined() {
        return $this->belongsToMany(Group::class, 'group_members', 'contact_id', 'group_id');
    }
}
