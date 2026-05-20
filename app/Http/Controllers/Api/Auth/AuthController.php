<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\SocialAccount;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'nom' => $request->string('nom')->toString(),
            'prenom' => $request->string('prenom')->toString(),
            'telephone' => $request->string('telephone')->toString(),
            'email' => $request->string('email')->lower()->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);
        $user->assignApplicationRole(User::ROLE_COUTURIER);

        event(new Registered($user));

        return $this->authenticatedResponse(
            $user,
            $request->string('device_name')->toString() ?: 'mobile-app',
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $credentials['email'] = Str::lower((string) ($credentials['email'] ?? ''));

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        return $this->authenticatedResponse(
            $user,
            $request->string('device_name')->toString() ?: 'mobile-app',
        );
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user),
                'needs_onboarding' => $user->mobile_onboarding_completed_at === null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $currentAccessToken = $request->user()?->currentAccessToken();

        if ($currentAccessToken !== null && method_exists($currentAccessToken, 'delete')) {
            $currentAccessToken->delete();
        }

        return response()->json([
            'message' => 'Deconnexion effectuee avec succes.',
        ]);
    }

    private function authenticatedResponse(User $user, string $deviceName, int $status = 200): JsonResponse
    {
        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken($deviceName);
        $freshUser = $user->fresh();

        return response()->json([
            'message' => 'Authentification reussie.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $this->userPayload($freshUser),
                'needs_onboarding' => $freshUser?->mobile_onboarding_completed_at === null,
            ],
        ], $status);
    }

    public function social(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'provider' => ['required', 'string', 'in:google'],
            'id_token' => ['required_if:provider,google', 'string'],
            'provider_user_id' => ['sometimes', 'string'],
            'email' => ['nullable', 'email'],
            'nom' => ['nullable', 'string'],
            'prenom' => ['nullable', 'string'],
            'avatar' => ['nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $provider = $payload['provider'];
        $deviceName = $payload['device_name'] ?? 'mobile-app';
        $info = [];

        if ($provider === 'google') {
            $idToken = $payload['id_token'] ?? null;
            if (! $idToken) {
                return response()->json(['message' => 'id_token manquant'], 422);
            }

            try {
                $resp = Http::acceptJson()
                    ->timeout(8)
                    ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
            } catch (ConnectionException) {
                return response()->json([
                    'message' => 'Le service Google est temporairement indisponible.',
                ], 503);
            }

            if ($resp->failed()) {
                return response()->json(['message' => 'Jeton Google invalide'], 401);
            }

            $info = $resp->json();
            $providerUserId = $info['sub'] ?? $payload['provider_user_id'] ?? null;
            $email = $info['email'] ?? $payload['email'] ?? null;
            $nom = $info['family_name'] ?? $payload['nom'] ?? null;
            $prenom = $info['given_name'] ?? $payload['prenom'] ?? null;
            $avatar = $info['picture'] ?? $payload['avatar'] ?? null;
        } else {
            return response()->json(['message' => 'Provider non supporte'], 422);
        }

        if (! filled($providerUserId)) {
            return response()->json(['message' => 'Identifiant Google introuvable.'], 422);
        }

        $email = filled($email ?? null) ? Str::lower((string) $email) : null;
        $prenom = filled($prenom ?? null) ? trim((string) $prenom) : 'Utilisateur';
        $nom = filled($nom ?? null) ? trim((string) $nom) : 'E-Couture';

        DB::beginTransaction();
        try {
            $social = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($social) {
                $social->fill([
                    'provider_email' => $email,
                    'provider_avatar_url' => $avatar,
                ])->save();
                $user = $social->user;
            } else {
                $user = User::query()
                    ->when(
                        filled($email),
                        fn ($query) => $query->where('email', $email),
                        fn ($query) => $query->whereRaw('1 = 0'),
                    )
                    ->first();
                if (! $user) {
                    $user = User::create([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'telephone' => null,
                        'password' => Hash::make(Str::password(32)),
                        'est_actif' => true,
                        'email_verified_at' => filter_var($info['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)
                            ? now()
                            : null,
                    ]);
                    $user->assignApplicationRole(User::ROLE_COUTURIER);
                }

                SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                    'provider_email' => $email,
                    'provider_avatar_url' => $avatar,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->authenticatedResponse($user, $deviceName);
    }

    private function userPayload(User $user): array
    {
        return [
            'external_id' => $user->external_id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'est_actif' => $user->est_actif,
            'roles' => $user->getRoleNames()->values()->all(),
            'primary_role' => $user->primary_role,
            'email_verified_at' => $user->email_verified_at?->toAtomString(),
            'last_login_at' => $user->last_login_at?->toAtomString(),
        ];
    }
}
