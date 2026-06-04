<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeVetement extends BaseModel
{
    protected $table = 'type_vetements';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function modelesVetements(): HasMany
    {
        return $this->hasMany(ModeleVetement::class, 'type_vetement_id');
    }
}
