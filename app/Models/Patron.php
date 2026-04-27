<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patron extends BaseModel
{
    protected $table = 'patrons';

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'donnees_dessin' => 'array',
        ];
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function piecesPatrons(): HasMany
    {
        return $this->hasMany(PiecePatron::class, 'patron_id');
    }
}
