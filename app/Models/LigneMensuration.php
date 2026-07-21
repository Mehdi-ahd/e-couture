<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modele representant une ligne de mensuration.
 * Alias plus specifique du modele Mesure, utilise pour la clarte dans certains contextes.
 */
class LigneMensuration extends BaseModel
{
    public $timestamps = false;

    protected $table = 'mesures';

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
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
