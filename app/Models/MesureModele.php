<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modele representant une mesure associee a un modele de vetement.
 * Definit les dimensions standards d un modele pour un type de mesure donne.
 */
class MesureModele extends BaseModel
{
    use SoftDeletes;

    protected $table = 'mesure_modeles';

    protected $appends = ['modele_external_id', 'type_mesure_external_id'];

    protected $fillable = [
        'external_id',
        'modele_vetement_id',
        'type_mesure_id',
        'valeur',
        'notes',
    ];

    public function getModeleExternalIdAttribute(): ?string
    {
        return $this->modeleVetement?->external_id;
    }

    public function getTypeMesureExternalIdAttribute(): ?string
    {
        return $this->typeMesure?->external_id;
    }

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
        ];
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
