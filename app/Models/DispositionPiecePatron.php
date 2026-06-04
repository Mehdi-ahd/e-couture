<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispositionPiecePatron extends BaseModel
{
    protected $table = 'disposition_piece_patrons';

    public $timestamps = false;

    protected $fillable = [
        'piece_patron_id',
        'forme_decoupe_id',
        'materiau_id',
        'position_x',
        'position_y',
        'rotation',
        'echelle',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'decimal:4',
            'position_y' => 'decimal:4',
            'rotation' => 'decimal:4',
            'echelle' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function piecePatron(): BelongsTo
    {
        return $this->belongsTo(PiecePatron::class, 'piece_patron_id');
    }

    public function formeDecoupe(): BelongsTo
    {
        return $this->belongsTo(FormeDecoupe::class, 'forme_decoupe_id');
    }

    public function materiau(): BelongsTo
    {
        return $this->belongsTo(Materiau::class, 'materiau_id');
    }
}
