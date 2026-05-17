<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patron extends BaseModel
{
    use HasUuids;

    protected $table = 'patrons';

    public $timestamps = false;

    protected $fillable = [
        'modele_vetement_id',
        'methode',
        'version',
        'fichier_url',
        'donnees_dessin',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'donnees_dessin' => 'array',
            'created_at'     => 'datetime',
        ];
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function piecePatrons(): HasMany
    {
        return $this->hasMany(PiecePatron::class, 'patron_id');
    }
}
