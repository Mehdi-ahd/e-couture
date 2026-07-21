<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modele representant un code de verification envoye a un utilisateur.
 * Utilise pour la verification en deux etapes ou la confirmation du numero de telephone.
 */
class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'code',
        'destination',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
