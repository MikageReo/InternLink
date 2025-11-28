<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeerTip extends Model
{
    protected $fillable = [
        'nickname',
        'tip_content',
        'studentID',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
