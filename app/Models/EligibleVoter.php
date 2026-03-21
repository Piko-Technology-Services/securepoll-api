<?php

// app/Models/EligibleVoter.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleVoter extends Model
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