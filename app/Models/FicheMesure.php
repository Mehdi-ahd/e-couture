<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FicheMesure extends BaseModel
{
    use HasUuids;

    protected $table = 'fiche_mesures';

    protected $fillable = [
        'client_id',
        'date',
        'methode',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function mesures(): HasMany
    {
        return $this->hasMany(Mesure::class, 'fiche_mesure_id');
    }
}
