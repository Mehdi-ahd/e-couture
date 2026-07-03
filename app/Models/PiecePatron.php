<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiecePatron extends BaseModel
{
    protected $table = 'piece_patrons';

    public $timestamps = false;

    protected $fillable = [
        'patron_id',
        'nom',
        'ordre',
        'donnees_geometriques',
        'donnees_geometriques_v2',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'donnees_geometriques' => 'array',
            'donnees_geometriques_v2' => 'binary',
        ];
    }

    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class, 'patron_id');
    }

    public function annotationPatrons(): HasMany
    {
        return $this->hasMany(AnnotationPatron::class, 'piece_patron_id');
    }

    public function dispositionPiecePatrons(): HasMany
    {
        return $this->hasMany(DispositionPiecePatron::class, 'piece_patron_id');
    }

    public function dispositions(): HasMany
    {
        return $this->dispositionPiecePatrons();
    }
}
