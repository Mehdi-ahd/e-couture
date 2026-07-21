<?php

namespace App\Models;

use App\Models\Concerns\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Classe de base pour tous les modeles de l application.
 * Fournit un identifiant unique externalId et active les factories.
 */
abstract class BaseModel extends Model
{
    use HasExternalId;
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'external_id';
    }
}
