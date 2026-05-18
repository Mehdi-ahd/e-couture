<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mesure extends Model
{
    use HasFactory;

    protected $table = 'mesures';

    public $timestamps = false;

    protected $fillable = [
        'fiche_mesure_id',
        'type_mesure_id',
        'valeur',
        'source',
        'confiance',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'valeur'    => 'decimal:2',
            'confiance' => 'decimal:4',
        ];
    }

    public function ficheMesure(): BelongsTo
    {
        return $this->belongsTo(FicheMesure::class, 'fiche_mesure_id');
    }

    public function typeMesure(): BelongsTo
    {
        return $this->belongsTo(TypeMesure::class, 'type_mesure_id');
    }
}
