<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class FormeDecoupe extends BaseModel
{
    protected $table = 'formes_decoupe';

    protected function casts(): array
    {
        return [
            'donnees_formes' => 'array',
            'est_global' => 'boolean',
        ];
    }

    public function materiaux(): HasMany
    {
        return $this->hasMany(Materiau::class, 'forme_decoupe_id');
    }

    public function dispositionsPiecePatron(): HasMany
    {
        return $this->hasMany(DispositionPiecePatron::class, 'forme_decoupe_id');
    }
}
