<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'datetime_start',
        'datetime_end',
        'status',
        'visibility',
        'voting_method'
    ];

    protected $casts = [
        'voting_method' => 'array'
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
