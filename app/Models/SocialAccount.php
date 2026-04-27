<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'external_id',
    'user_id',
    'provider',
    'provider_user_id',
    'provider_email',
    'provider_avatar_url',
    'provider_token',
    'provider_refresh_token',
    'provider_token_expires_at',
])]
class SocialAccount extends BaseModel
{
    protected function casts(): array
    {
        return [
            'provider_token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
