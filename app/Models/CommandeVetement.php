<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommandeVetement extends BaseModel
{

    protected $table = 'commande_vetements';

    protected $fillable = [
        'client_id',
        'modele_vetement_id',
        'fiche_mesure_id',
        'statut',
        'notes',
        'date_commande',
        'date_livraison',
    ];

    protected function casts(): array
    {
        return [
            'date_commande'  => 'date',
            'date_livraison' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function ficheMesure(): BelongsTo
    {
        return $this->belongsTo(FicheMesure::class, 'fiche_mesure_id');
    }

}
