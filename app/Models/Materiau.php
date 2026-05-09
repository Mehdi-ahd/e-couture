<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materiau extends BaseModel
{
    protected $table = 'materiaux';

    protected function casts(): array
    {
        return [
            'est_global' => 'boolean',
        ];
    }

    public function formeDecoupe(): BelongsTo
    {
        return $this->belongsTo(FormeDecoupe::class, 'forme_decoupe_id');
    }

    public function dispositionsPiecePatron(): HasMany
    {
        return $this->hasMany(DispositionPiecePatron::class, 'materiau_id');
    }
}
