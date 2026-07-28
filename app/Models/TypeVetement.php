<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modele representant une categorie ou un type de vetement.
 * Exemples: robe, pantalon, veste, chemise.
 */
class TypeVetement extends BaseModel
{
    protected $table = 'type_vetements';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'categorie',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function modelesVetements(): HasMany
    {
        return $this->hasMany(ModeleVetement::class, 'type_vetement_id');
    }

    public function typeMesures(): BelongsToMany
    {
        return $this->belongsToMany(TypeMesure::class, 'type_vetement_mesures', 'type_vetement_id', 'type_mesure_id')
            ->withPivot('est_obligatoire')
            ->withTimestamps();
    }
}
