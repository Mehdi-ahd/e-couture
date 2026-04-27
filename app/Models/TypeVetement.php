<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeVetement extends BaseModel
{
    protected $table = 'types_vetements';

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function mensurationPivot(): BelongsTo
    {
        return $this->belongsTo(TypeMensuration::class, 'mensuration_pivot_id');
    }

    public function modelesVetements(): HasMany
    {
        return $this->hasMany(ModeleVetement::class, 'type_vetement_id');
    }
}
