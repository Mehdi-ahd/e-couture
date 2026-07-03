<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FicheMesure extends BaseModel implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'fiche_mesures';

    protected $fillable = [
        'client_id',
        'date',
        'methode',
        'validee',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'validee' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function mesures(): HasMany
    {
        return $this->hasMany(Mesure::class, 'fiche_mesure_id');
    }

    // Collections Spatie — une par vue
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('face')
            ->singleFile();   // max 1 photo par vue

        $this->addMediaCollection('dos')
            ->singleFile();

        $this->addMediaCollection('profil')
            ->singleFile();
    }
}
