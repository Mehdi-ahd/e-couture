<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Controleur API mobile pour la securite du compte.
 * Gere la verification par email et telephone, l authentification a deux facteurs,
 * la gestion des sessions et la modification du mot de passe.
 */
class MobileSecurityController extends Controller
{
    private const CODE_EXPIRY_MINUTES = 10;

    // ── Email Verification ───────────────────────────────────────────

    public function sendEmailVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email deja verifie.']);
        }

        $code = $this->generateCode();
        $this->storeCode($user, 'email_verification', $code, $user->email);

        // In production, send email. For now log it.
        logger("Email verification code for {$user->email}: {$code}");

        return response()->json(['message' => 'Code de verification envoye par email.']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email deja verifie.']);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (! $this->verifyCode($user, 'email_verification', $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expire.'],
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email verifie avec succes.']);
    }

    // ── Phone Verification ───────────────────────────────────────────

    public function sendPhoneVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->phone_verified_at !== null) {
            return response()->json(['message' => 'Telephone deja verifie.']);
        }

        if (blank($user->telephone)) {
            throw ValidationException::withMessages([
                'telephone' => ['Aucun numero de telephone enregistre.'],
            ]);
        }

        $code = $this->generateCode();
        $this->storeCode($user, 'phone_verification', $code, $user->telephone);

        // In production, send SMS. For now log it.
        logger("Phone verification code for {$user->telephone}: {$code}");

        return response()->json(['message' => 'Code de verification envoye par SMS.']);
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->phone_verified_at !== null) {
            return response()->json(['message' => 'Telephone deja verifie.']);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (! $this->verifyCode($user, 'phone_verification', $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expire.'],
            ]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        return response()->json(['message' => 'Telephone verifie avec succes.']);
    }

    // ── Two-Factor Authentication ─────────────────────────────────────

    public function enableTwoFactor(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_confirmed_at !== null) {
            return response()->json(['message' => '2FA deja active.']);
        }

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10).'-'.Str::random(10);
        }

        $user->forceFill([
            'two_factor_secret' => Str::random(32),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ])->save();

        return response()->json([
            'message' => '2FA presque active. Confirmez avec un code.',
            'data' => [
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }

    public function confirmTwoFactor(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_confirmed_at !== null) {
            return response()->json(['message' => '2FA deja confirmee.']);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        // Generate and verify a confirmation code
        $code = $this->generateCode();
        $this->storeCode($user, 'two_factor_confirm', $code, $user->email);
        logger("2FA confirmation code for {$user->email}: {$code}");

        if (! $this->verifyCode($user, 'two_factor_confirm', $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expire.'],
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return response()->json(['message' => '2FA activee avec succes.']);
    }

    public function disableTwoFactor(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json(['message' => '2FA desactivee.']);
    }

    public function twoFactorChallenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', Str::lower($validated['email']))->first();

        if (! $user || $user->two_factor_confirmed_at === null) {
            throw ValidationException::withMessages([
                'email' => ['2FA non configuree pour ce compte.'],
            ]);
        }

        // Check recovery code first
        $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
        if (in_array($validated['code'], $codes, true)) {
            $codes = array_values(array_diff($codes, [$validated['code']]));
            $user->forceFill([
                'two_factor_recovery_codes' => json_encode($codes),
            ])->save();

            $user->forceFill(['last_login_at' => now()])->save();
            $token = $user->createToken($request->string('device_name', 'mobile-app')->toString());

            return response()->json([
                'message' => 'Authentification reussie (code de recuperation).',
                'data' => [
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                ],
            ]);
        }

        // Check verification code
        if (! $this->verifyCode($user, 'two_factor', $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expire.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken($request->string('device_name', 'mobile-app')->toString());

        return response()->json([
            'message' => 'Authentification reussie.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    // ── Sessions ─────────────────────────────────────────────────────

    public function getSessions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        $sessions = $user->tokens()->get()->map(function ($token) use ($currentToken) {
            return [
                'id' => $token->id,
                'device_name' => $token->name,
                'is_current' => $currentToken !== null && $currentToken->id === $token->id,
                'last_used_at' => $token->last_used_at?->diffForHumans(),
                'created_at' => $token->created_at->toAtomString(),
            ];
        });

        return response()->json(['data' => $sessions]);
    }

    public function revokeSession(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken !== null && (int) $id === $currentToken->id) {
            return response()->json(['message' => 'Impossible de supprimer la session en cours.'], 422);
        }

        $token = $user->tokens()->findOrFail($id);
        $token->delete();

        return response()->json(['message' => 'Session revoquee.']);
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        $user->tokens()
            ->when($currentToken !== null, fn ($q) => $q->where('id', '!=', $currentToken->id))
            ->delete();

        return response()->json(['message' => 'Autres sessions revoquees.']);
    }

    // ── Password ─────────────────────────────────────────────────────

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($validated['new_password'])])->save();

        return response()->json(['message' => 'Mot de passe mis a jour avec succes.']);
    }

    // ── Security Status ──────────────────────────────────────────────

    public function getSecurityStatus(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'email_verified' => $user->hasVerifiedEmail(),
                'phone_verified' => $user->phone_verified_at !== null,
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'sessions_count' => $user->tokens()->count(),
            ],
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function storeCode(User $user, string $type, string $code, ?string $destination = null): void
    {
        VerificationCode::where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        VerificationCode::create([
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'destination' => $destination,
            'expires_at' => now()->addMinutes(self::CODE_EXPIRY_MINUTES),
        ]);
    }

    private function verifyCode(User $user, string $type, string $code): bool
    {
        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', $type)
            ->where('code', $code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return false;
        }

        $record->update(['used_at' => now()]);

        return true;
    }
}
