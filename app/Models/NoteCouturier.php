<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteCouturier extends BaseModel
{
    protected $table = 'notes_couturiers';

    protected function casts(): array
    {
        return [
            'note_service' => 'integer',
            'note_conception' => 'integer',
            'note_livraison' => 'integer',
            'note_delai' => 'integer',
            'date_notation' => 'datetime',
            'est_visible' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function couturier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'couturier_id');
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(CommandeVetement::class, 'commande_id');
    }

    public function getNoteGlobaleAttribute(): float
    {
        return round(
            ($this->note_service + $this->note_conception + $this->note_livraison + $this->note_delai) / 4,
            2,
        );
    }
}
