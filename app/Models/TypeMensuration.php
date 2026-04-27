<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeMensuration extends BaseModel
{
    protected $table = 'types_mensurations';

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function typesVetementsCommePivot(): HasMany
    {
        return $this->hasMany(TypeVetement::class, 'mensuration_pivot_id');
    }

    public function reglesCommeSource(): HasMany
    {
        return $this->hasMany(RegleProportion::class, 'mensuration_source_id');
    }

    public function reglesCommeCible(): HasMany
    {
        return $this->hasMany(RegleProportion::class, 'mensuration_cible_id');
    }

    public function lignesMensurations(): HasMany
    {
        return $this->hasMany(LigneMensuration::class, 'type_mensuration_id');
    }

    public function mensurationsModeles(): HasMany
    {
        return $this->hasMany(MensurationModele::class, 'type_mensuration_id');
    }

    public function annotationsPatrons(): HasMany
    {
        return $this->hasMany(AnnotationPatron::class, 'type_mensuration_id');
    }
}
