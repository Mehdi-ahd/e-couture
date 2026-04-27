<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModeleVetement extends BaseModel
{
    protected $table = 'modeles_vetements';

    public function typeVetement(): BelongsTo
    {
        return $this->belongsTo(TypeVetement::class, 'type_vetement_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function mensurationsModeles(): HasMany
    {
        return $this->hasMany(MensurationModele::class, 'modele_vetement_id');
    }

    public function patron(): HasOne
    {
        return $this->hasOne(Patron::class, 'modele_vetement_id');
    }

    public function commandesVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'modele_vetement_id');
    }
}
