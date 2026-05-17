<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends BaseModel
{
    protected $table = 'clients';

    protected $fillable = [
        'external_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'genre',
        'date_naissance',
        'prestataire_id',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'est_actif' => 'boolean',
        ];
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function fichesMesures(): HasMany
    {
        return $this->hasMany(FicheMesure::class, 'client_id');
    }

    public function commandesVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'client_id');
    }
}
