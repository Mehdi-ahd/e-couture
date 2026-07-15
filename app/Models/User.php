<?php

namespace App\Models;

use App\Models\Concerns\HasExternalId;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName as FilamentHasName;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentHasName, FilamentUser, MustVerifyEmailContract
{
    public const ROLE_ADMINISTRATEUR = 'administrateur';

    public const ROLE_CLIENT = 'client';

    public const ROLE_COUTURIER = 'couturier';

    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasExternalId;

    use HasFactory;
    use HasRoles;
    use MustVerifyEmail;
    use Notifiable;

    protected $fillable = [
        'external_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'email_verified_at',
        'remember_token',
        'est_actif',
        'specialite',
        'adresse_atelier',
        'bio',
        'type_piece',
        'fichier_piece_recto',
        'fichier_piece_verso',
        'selfie',
        'kyc_statut',
        'motif_rejet',
        'date_soumission',
        'date_validation',
        'mobile_onboarding_completed_at',
        'date_naissance',
        'last_login_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_soumission' => 'datetime',
            'date_validation' => 'datetime',
            'mobile_onboarding_completed_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'date_naissance' => 'date',
            'est_actif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'prestataire_id');
    }

    public function compteSocialPrincipal(): HasOne
    {
        return $this->hasOne(SocialAccount::class);
    }

    public function comptesSociaux(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function fichesMesures(): HasManyThrough
    {
        return $this->hasManyThrough(
            FicheMesure::class,
            Client::class,
            'prestataire_id',
            'client_id',
            'id',
            'id',
        );
    }

    public function modelesVetementsCrees(): HasMany
    {
        return $this->hasMany(ModeleVetement::class, 'prestataire_id');
    }

    public function scopeCouturiers(Builder $query): Builder
    {
        return $query->role(self::ROLE_COUTURIER);
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->role(self::ROLE_CLIENT);
    }

    public function scopeAdministrateurs(Builder $query): Builder
    {
        return $query->role(self::ROLE_ADMINISTRATEUR);
    }

    public function isCouturier(): bool
    {
        return $this->hasRole(self::ROLE_COUTURIER);
    }

    public function isClient(): bool
    {
        return $this->hasRole(self::ROLE_CLIENT);
    }

    public function isAdministrateur(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRATEUR);
    }

    public static function ensureRole(string $roleName): Role
    {
        return Role::findOrCreate($roleName);
    }

    public function assignApplicationRole(string $roleName): self
    {
        $this->assignRole(self::ensureRole($roleName));

        return $this;
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getRouteKeyName(): string
    {
        return 'external_id';
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (app()->isLocal()) {
            return true;
        }

        return $this->hasRole(self::ROLE_ADMINISTRATEUR);
    }
}
