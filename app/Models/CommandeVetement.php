<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modele representant une commande de vetement passée par un client.
 * Liee a un modele de vetement et a une fiche de mesures specifique.
 */
class CommandeVetement extends BaseModel
{
    protected $table = 'commande_vetements';

    protected $appends = ['client_external_id', 'modele_external_id'];

    protected $fillable = [
        'client_id',
        'modele_vetement_id',
        'fiche_mesure_id',
        'statut',
        'notes',
        'date_commande',
        'date_livraison',
    ];

    public function getClientExternalIdAttribute(): ?string
    {
        return $this->client?->external_id;
    }

    public function getModeleExternalIdAttribute(): ?string
    {
        return $this->modeleVetement?->external_id;
    }

    protected function casts(): array
    {
        return [
            'date_commande' => 'date',
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
