<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UserRelationTrait
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
