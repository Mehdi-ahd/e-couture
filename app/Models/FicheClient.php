<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FicheClient extends BaseModel
{
    protected $table = 'fiches_clients';

    protected function casts(): array
    {
        return [
            'date_creation' => 'datetime',
            'est_actif' => 'boolean',
        ];
    }

    public function couturier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'couturier_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commandesVetements(): HasMany
    {
        return $this->hasMany(CommandeVetement::class, 'fiche_client_id');
    }
}
