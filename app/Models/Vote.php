<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'poll_id',
        'category_id',
        'nominee_id',
        'user_id',
        'voter_identifier'
    ];
}
