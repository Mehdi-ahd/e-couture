<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensurationModele extends BaseModel
{
    protected $table = 'mesure_modeles';

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
        ];
    }

    public function modeleVetement(): BelongsTo
    {
        return $this->belongsTo(ModeleVetement::class, 'model_vetement_id');
    }

    public function typeMensuration(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'type_mesure_id');
    }
}
