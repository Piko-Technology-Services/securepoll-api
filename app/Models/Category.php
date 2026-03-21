<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['poll_id', 'name', 'description'];

    public function nominees()
    {
        return $this->hasMany(Nominee::class);
    }
}