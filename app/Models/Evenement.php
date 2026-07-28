<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evenement extends BaseModel
{
    protected $table = 'evenements';

    protected $fillable = [
        'commande_id',
        'titre',
        'description',
        'date',
        'type',
        'est_complete',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'est_complete' => 'boolean',
        ];
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(CommandeVetement::class, 'commande_id');
    }
}
