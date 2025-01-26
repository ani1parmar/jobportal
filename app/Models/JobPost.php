<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'company', 'location', 'salary'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
