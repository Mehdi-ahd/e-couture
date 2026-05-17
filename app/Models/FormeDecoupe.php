<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormeDecoupe extends BaseModel
{
    use HasUuids;

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
            'est_global'     => 'boolean',
            'created_at'     => 'datetime',
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
