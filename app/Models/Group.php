<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'color', 'members_count'
    ];

    public function members() {
        return $this->belongsToMany(Contact::class, 'group_members', 'group_id', 'contact_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
