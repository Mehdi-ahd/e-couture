<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends BaseModel
{
    protected $table = 'paiements';

    protected $fillable = [
        'commande_id',
        'montant',
        'date_paiement',
        'methode',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_paiement' => 'datetime',
        ];
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(CommandeVetement::class, 'commande_id');
    }
}
