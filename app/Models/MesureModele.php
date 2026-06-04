<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesureModele extends BaseModel
{
    protected $table = 'mesure_modeles';

    public $timestamps = false;

    protected $fillable = [
        'modele_vetement_id',
        'type_mesure_id',
        'valeur',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
        ];
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'modele_vetement_id');
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
