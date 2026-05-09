<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispositionPiecePatron extends BaseModel
{
    protected $table = 'dispositions_piece_patron';

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
            'rotation' => 'float',
            'echelle' => 'float',
            'ordre' => 'integer',
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
