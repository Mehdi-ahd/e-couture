<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegleProportion extends BaseModel
{
    protected $table = 'regles_proportions';

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:4',
            'offset' => 'decimal:2',
            'version' => 'integer',
            'est_active' => 'boolean',
        ];
    }

    public function mensurationSource(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'type_mesure_source_id');
    }

    public function mensurationCible(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'type_mesure_cible_id');
    }
}
