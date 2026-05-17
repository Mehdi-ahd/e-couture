<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegleProportion extends BaseModel
{
    use HasUuids;

    protected $table = 'regles_proportions';

    public $timestamps = false;

    protected $fillable = [
        'type_mesure_id',
        'nom',
        'coefficient',
        'offset',
        'source_metier',
        'version',
        'est_active',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:6',
            'offset'      => 'decimal:6',
            'est_active'  => 'boolean',
            'created_at'  => 'datetime',
        ];
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
