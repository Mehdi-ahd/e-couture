<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnotationPatron extends BaseModel
{

    protected $table = 'annotation_patrons';

    public $timestamps = false;

    protected $fillable = [
        'piece_patron_id',
        'type_mesure_id',
        'label',
        'position_depart',
        'position_fin',
        'orientation',
    ];

    public function piecePatron(): BelongsTo
    {
        return $this->belongsTo(PiecePatron::class, 'piece_patron_id');
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
