<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommandeVetement extends BaseModel
{
    protected $table = 'commande_vetements';

    protected function casts(): array
    {
        return [
            'date_commande' => 'datetime',
            'date_livraison' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'model_vetement_id');
    }

    public function ficheMesure(): BelongsTo
    {
        return $this->belongsTo(FicheMesure::class, 'fiche_mesure_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'commande_id');
    }

    public function noteCouturier(): HasOne
    {
        return $this->hasOne(NoteCouturier::class, 'commande_id');
    }
}
