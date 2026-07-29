<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modele representant un patron de couture lie a un modele de vetement.
 * Contient les donnees de dessin et les versions du patron.
 */
class Patron extends BaseModel
{
    protected $table = 'patrons';

    public $timestamps = false;

    protected $fillable = [
        'modele_vetement_id',
        'methode',
        'version',
        'version_semver',
        'fichier_url',
        'donnees_dessin',
        'donnees_dessin_v2',
        'fichier_koda',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'donnees_dessin' => 'array',
            'donnees_dessin_v2' => 'string',
            'created_at' => 'datetime',
        ];
    }

    public function getVersionLabelAttribute(): string
    {
        return $this->version_semver ?? sprintf('v%d.0', $this->version);
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function piecePatrons(): HasMany
    {
        return $this->hasMany(PiecePatron::class, 'patron_id');
    }

    /**
     * Backward-compatible alias used by mobile controllers/serializers.
     */
    public function piecesPatrons(): HasMany
    {
        return $this->piecePatrons();
    }
}
