<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modele representant une forme de decoupe predefinie.
 * Peut etre globale ou personnalisee et contient les donnees geometriques de la forme.
 */
class FormeDecoupe extends BaseModel
{
    protected $table = 'formes_decoupe';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'description',
        'donnees_formes',
        'miniature_url',
        'source',
        'est_global',
    ];

    protected function casts(): array
    {
        return [
            'donnees_formes' => 'array',
            'est_global' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function dispositionPiecePatrons(): HasMany
    {
        return $this->hasMany(DispositionPiecePatron::class, 'forme_decoupe_id');
    }

    public function materiaux(): HasMany
    {
        return $this->hasMany(Materiau::class, 'forme_decoupe_id');
    }
}
