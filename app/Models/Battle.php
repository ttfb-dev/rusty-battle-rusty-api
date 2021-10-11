<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    protected $fillable = [
        'members',
        'winners',
        'status',
        'points',
        'points_version',
    ];

    protected $casts = [
        'members' => 'array',
        'winners' => 'array',
    ];
}
