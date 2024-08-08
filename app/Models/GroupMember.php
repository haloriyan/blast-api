<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id', 'contact_id'
    ];

    public function contact() {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function group() {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
