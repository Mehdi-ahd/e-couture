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

class MobileAuthSyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:191'],
            'prenom' => ['required', 'string', 'max:80'],
            'nom' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:190'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'provider' => ['nullable', 'string', 'max:40'],
            'provider_user_id' => ['nullable', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var User $user */
        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()
                ->where('external_id', $validated['external_id'])
                ->when(
                    filled($validated['email'] ?? null),
                    fn ($query) => $query->orWhere('email', strtolower((string) $validated['email'])),
                )
                ->first();

            if ($user === null) {
                $user = new User([
                    'external_id' => $validated['external_id'],
                    'password' => Hash::make(Str::password(32)),
                ]);
            }

            $user->fill([
                'prenom' => $validated['prenom'],
                'nom' => $validated['nom'],
                'email' => filled($validated['email'] ?? null)
                    ? strtolower((string) $validated['email'])
                    : $user->email,
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
                    'provider_email' => $validated['email'] ?? null,
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
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'est_actif' => $user->est_actif,
                    'roles' => $user->getRoleNames()->values()->all(),
                    'primary_role' => $user->primary_role,
                ],
            ],
        ]);
    }
}
