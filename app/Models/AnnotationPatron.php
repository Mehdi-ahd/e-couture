<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnotationPatron extends BaseModel
{
    protected $table = 'annotations_patrons';

    public function piecePatron(): BelongsTo
    {
        return $this->belongsTo(PiecePatron::class, 'piece_patron_id');
    }

    public function typeMensuration(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'type_mesure_id');
    }
}
