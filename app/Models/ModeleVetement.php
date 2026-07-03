<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModeleVetement extends BaseModel
{
    protected $table = 'modele_vetements';

    protected $fillable = [
        'prestataire_id',
        'type_vetement_id',
        'nom',
        'description',
        'portee',
        'statut',
    ];

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function typeVetement(): BelongsTo
    {
        return $this->belongsTo(TypeVetement::class, 'type_vetement_id');
    }

    public function commandeVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'modele_vetement_id');
    }

    public function mesureModeles(): HasMany
    {
        return $this->hasMany(MesureModele::class, 'modele_vetement_id');
    }

    public function materiaux(): BelongsToMany
    {
        return $this->belongsToMany(Materiau::class, 'modele_vetement_materiau', 'modele_vetement_id', 'materiau_id');
    }

    public function patron(): HasOne
    {
        return $this->hasOne(Patron::class, 'modele_vetement_id');
    }
}
