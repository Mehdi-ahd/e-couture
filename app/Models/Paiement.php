<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends BaseModel
{
    protected $table = 'paiements';

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_initiation' => 'datetime',
            'date_confirmation' => 'datetime',
            'metadonnees_agregateur' => 'array',
        ];
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(CommandeVetement::class, 'commande_id');
    }
}
