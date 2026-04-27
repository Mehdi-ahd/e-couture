<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

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
        $token = $user->createToken($deviceName);

        return response()->json([
            'message' => 'Authentification reussie.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $this->userPayload($user->fresh()),
            ],
        ], $status);
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
