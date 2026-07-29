<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Controleur pour synchroniser l authentification depuis l application mobile.
 * Cree ou met a jour un utilisateur via son identifiant externe, email ou compte social,
 * et retourne un jeton d acces Sanctum.
 */
class MobileAuthSyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:191'],
            'prenom' => ['required', 'string', 'max:80'],
            'nom' => ['required', 'string', 'max:80'],
            'sexe' => ['required', 'string', 'in:homme,femme,autre'],
            'email' => ['nullable', 'email', 'max:190'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'provider' => ['nullable', 'string', 'max:40'],
            'provider_user_id' => ['nullable', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var User $user */
        $user = DB::transaction(function () use ($validated): User {
            $user = null;
            $external = $validated['external_id'] ?? null;
            $email = filled($validated['email'] ?? null) ? strtolower((string) $validated['email']) : null;

            // Only treat external_id as UUID when it is a valid UUID
            if (! empty($external) && Str::isUuid($external)) {
                $user = User::query()
                    ->where('external_id', $external)
                    ->when(filled($email), fn ($query) => $query->orWhere('email', $email))
                    ->first();
            }

            // Try to resolve via social_accounts if provider info present
            if ($user === null && filled($validated['provider'] ?? null) && filled($validated['provider_user_id'] ?? null)) {
                $social = SocialAccount::query()
                    ->where('provider', $validated['provider'])
                    ->where('provider_user_id', $validated['provider_user_id'])
                    ->first();

                if ($social) {
                    $user = $social->user;
                }
            }

            // Fallback to email lookup
            if ($user === null && $email !== null) {
                $user = User::query()->where('email', $email)->first();
            }

            if ($user === null) {
                $attrs = [
                    'password' => Hash::make(Str::password(32)),
                ];
                if (! empty($external) && Str::isUuid($external)) {
                    $attrs['external_id'] = $external;
                }

                $user = new User($attrs);
            }

            $user->fill([
                'prenom' => $validated['prenom'],
                'nom' => $validated['nom'],
                'sexe' => $validated['sexe'],
                'email' => $email ?? $user->email,
                'telephone' => $validated['telephone'] ?? $user->telephone,
                'est_actif' => true,
            ]);
            $user->save();
            $user->assignApplicationRole(User::ROLE_COUTURIER);
            $user->forceFill(['last_login_at' => now()])->save();

            if (filled($validated['provider'] ?? null) && filled($validated['provider_user_id'] ?? null)) {
                $socialAccount = SocialAccount::query()->firstOrNew([
                    'provider' => $validated['provider'],
                    'provider_user_id' => $validated['provider_user_id'],
                ]);

                $socialAccount->user()->associate($user);
                $socialAccount->fill([
                    'provider_email' => $email,
                ]);
                $socialAccount->save();
            }

            return $user->fresh();
        });

        $token = $user->createToken($validated['device_name'] ?? 'koda-mobile');

        return response()->json([
            'message' => 'Synchronisation mobile effectuee.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'external_id' => $user->external_id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'sexe' => $user->sexe ?? 'autre',
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'est_actif' => $user->est_actif,
                    'roles' => $user->getRoleNames()->values()->all(),
                    'primary_role' => $user->primary_role,
                ],
                'needs_onboarding' => $user->mobile_onboarding_completed_at === null,
            ],
        ]);
    }
}
