<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modele representant une regle de proportion pour calculer des mesures.
 * Utilise un coefficient et un offset pour deduire une mesure a partir d une autre.
 */
class RegleProportion extends BaseModel
{
    protected $table = 'regles_proportions';

    public $timestamps = false;

    protected $fillable = [
        'type_mesure_id',
        'nom',
        'coefficient',
        'offset',
        'source_metier',
        'version',
        'est_active',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:6',
            'offset' => 'decimal:6',
            'est_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
