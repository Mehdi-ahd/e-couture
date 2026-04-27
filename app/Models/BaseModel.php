<?php

namespace App\Models;

use App\Models\Concerns\HasExternalId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
