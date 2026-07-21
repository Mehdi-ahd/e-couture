<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modele representant un type de mesure corporelle.
 * Exemples: tour de poitrine, longueur de manche, tour de taille.
 */
class TypeMesure extends BaseModel
{
    protected $table = 'type_mesures';

    public $timestamps = false;

    protected $fillable = [
        'external_id',
        'code',
        'nom',
        'unite',
        'categorie',
        'description',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function mesures(): HasMany
    {
        return $this->hasMany(Mesure::class, 'type_mesure_id');
    }

    public function mesureModeles(): HasMany
    {
        return $this->hasMany(MesureModele::class, 'type_mesure_id');
    }

    public function annotationPatrons(): HasMany
    {
        return $this->hasMany(AnnotationPatron::class, 'type_mesure_id');
    }

    public function regleProportions(): HasMany
    {
        return $this->hasMany(RegleProportion::class, 'type_mesure_id');
    }
}
