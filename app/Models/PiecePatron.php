<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiecePatron extends BaseModel
{
    protected $table = 'pieces_patrons';

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'donnees_geometriques' => 'array',
        ];
    }

    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class, 'patron_id');
    }

    public function annotationsPatrons(): HasMany
    {
        return $this->hasMany(AnnotationPatron::class, 'piece_patron_id');
    }
}
