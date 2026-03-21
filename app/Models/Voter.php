<?php

// app/Models/Voter.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $fillable = [
        'poll_id',
        'email',
    ];

    // 🔗 Relationships
    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
}