<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModeleVetement extends BaseModel
{
    protected $table = 'model_vetements';

    public function typeVetement(): BelongsTo
    {
        return $this->belongsTo(TypeVetement::class, 'type_vetement_id');
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function mensurationsModeles(): HasMany
    {
        return $this->hasMany(MensurationModele::class, 'model_vetement_id');
    }

    public function patron(): HasOne
    {
        return $this->hasOne(Patron::class, 'model_vetement_id');
    }

    public function commandesVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'model_vetement_id');
    }
}
