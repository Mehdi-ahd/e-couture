<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FicheMesure extends BaseModel
{
    protected $table = 'fiche_mesures';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'version_regles' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function lignesMensurations(): HasMany
    {
        return $this->hasMany(LigneMensuration::class, 'fiche_mesure_id');
    }

    public function commandesVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'fiche_mesure_id');
    }
}
