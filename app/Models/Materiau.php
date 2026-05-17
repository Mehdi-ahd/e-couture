<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materiau extends BaseModel
{
    use HasUuids;

    protected $table = 'materiaux';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'description',
        'type',
        'image_url',
        'est_global',
        'forme_decoupe_id',
    ];

    protected function casts(): array
    {
        return [
            'est_global' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function formeDecoupe(): BelongsTo
    {
        return $this->belongsTo(FormeDecoupe::class, 'forme_decoupe_id');
    }

    public function dispositionPiecePatrons(): HasMany
    {
        return $this->hasMany(DispositionPiecePatron::class, 'materiau_id');
    }
}
