<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasExternalId
{
    protected static function bootHasExternalId(): void
    {
        static::creating(function (Model $model): void {
            static::ensureExternalIdIsPresent($model);
        });

        static::saving(function (Model $model): void {
            static::ensureExternalIdIsPresent($model);
        });
    }

    protected static function ensureExternalIdIsPresent(Model $model): void
    {
        if (blank($model->getAttribute('external_id'))) {
            $model->setAttribute('external_id', (string) Str::uuid());
        }
    }
}
