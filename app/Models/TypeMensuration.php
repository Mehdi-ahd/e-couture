<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeMensuration extends BaseModel
{
    protected $table = 'type_mesures';

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function reglesCommeSource(): HasMany
    {
        return $this->hasMany(RegleProportion::class, 'type_mesure_source_id');
    }

    public function reglesCommeCible(): HasMany
    {
        return $this->hasMany(RegleProportion::class, 'type_mesure_cible_id');
    }

    public function lignesMensurations(): HasMany
    {
        return $this->hasMany(LigneMensuration::class, 'type_mesure_id');
    }

    public function mensurationsModeles(): HasMany
    {
        return $this->hasMany(MensurationModele::class, 'type_mesure_id');
    }

    public function annotationsPatrons(): HasMany
    {
        return $this->hasMany(AnnotationPatron::class, 'type_mesure_id');
    }
}
