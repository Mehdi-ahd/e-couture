<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mesure extends BaseModel
{
    protected $table = 'mesures';

    protected $appends = ['fiche_external_id', 'type_mesure_external_id'];

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
            'confiance' => 'decimal:4',
        ];
    }

    public function getFicheExternalIdAttribute(): ?string
    {
        return $this->ficheMesure?->external_id;
    }

    public function getTypeMesureExternalIdAttribute(): ?string
    {
        return $this->typeMesure?->external_id;
    }

    public function ficheMesure(): BelongsTo
    {
        return $this->belongsTo(FicheMesure::class, 'fiche_mesure_id');
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
