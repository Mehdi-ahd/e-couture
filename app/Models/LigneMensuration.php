<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneMensuration extends BaseModel
{
    protected $table = 'lignes_mensurations';

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
            'confiance' => 'decimal:4',
        ];
    }

    public function ficheMesure(): BelongsTo
    {
        return $this->belongsTo(FicheMesure::class, 'fiche_mesure_id');
    }

    public function typeMensuration(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'type_mensuration_id');
    }
}
