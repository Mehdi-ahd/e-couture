<?php

namespace App\Models;

use App\Models\Concerns\HasExternalId;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Computed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'external_id',
    'type',
    'nom',
    'prenom',
    'email',
    'telephone',
    'password',
    'est_actif',
    'kyc_type_piece',
    'kyc_statut',
    'kyc_motif_rejet',
    'kyc_date_soumission',
    'kyc_date_validation',
    'specialite',
    'adresse_atelier',
    'bio',
    'date_naissance',
    'notes',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasExternalId;
    use HasFactory;
    use MustVerifyEmail;
    use Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'kyc_date_soumission' => 'datetime',
            'kyc_date_validation' => 'datetime',
            'date_naissance' => 'date',
            'est_actif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    #[Computed]
    public function fullName(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    #[Computed]
    public function name(): string
    {
        return $this->full_name;
    }
}
