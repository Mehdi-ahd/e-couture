<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controleur d authentification via les reseaux sociaux.
 * Utilise Laravel Socialite pour les connexions Google, Facebook, etc.
 */
class SocialAuthController extends Controller
{
    public function redirect(string $provider): JsonResponse
    {
        $this->assertSupportedProvider($provider);

        $authorizationUrl = Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'data' => [
                'provider' => $provider,
                'authorization_url' => $authorizationUrl,
                'callback_url' => config('services.mobile_auth.callback_url'),
            ],
        ]);
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->assertSupportedProvider($provider);

        $providerUser = Socialite::driver($provider)->stateless()->user();

        [$user, $wasCreated] = DB::transaction(
            fn () => $this->findOrCreateSocialUser($provider, $providerUser),
        );

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken(sprintf('%s-mobile', $provider))->plainTextToken;
        $callbackUrl = $this->buildCallbackUrl($provider, $user, $token, $wasCreated);

        return redirect()->away($callbackUrl);
    }

    private function assertSupportedProvider(string $provider): void
    {
        $providers = config('services.mobile_auth.providers', []);

        if (! in_array($provider, $providers, true)) {
            throw new HttpException(404, 'Fournisseur social non supporte.');
        }

        if (blank(config("services.{$provider}.client_id")) || blank(config("services.{$provider}.client_secret"))) {
            throw new HttpException(503, 'Fournisseur social non configure.');
        }
    }

    private function findOrCreateSocialUser(string $provider, ProviderUser $providerUser): array
    {
        $providerEmail = filled($providerUser->getEmail())
            ? Str::lower((string) $providerUser->getEmail())
            : null;

        $socialAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUser->getId())
            ->first();

        if ($socialAccount !== null) {
            $this->hydrateSocialAccount($socialAccount, $providerUser)->save();

            return [$socialAccount->user, false];
        }

        $user = User::query()
            ->when(
                filled($providerEmail),
                fn ($query) => $query->where('email', $providerEmail),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->first();

        $wasCreated = false;

        if ($user === null) {
            [$prenom, $nom] = $this->splitName($providerUser);

            $user = User::create([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $providerEmail,
                'password' => Hash::make(Str::password(32)),
                'email_verified_at' => $providerEmail ? now() : null,
            ]);
            $user->assignApplicationRole(User::ROLE_COUTURIER);

            event(new Registered($user));
            $wasCreated = true;
        }

        $socialAccount = new SocialAccount([
            'provider' => $provider,
            'provider_user_id' => $providerUser->getId(),
        ]);

        $socialAccount->user()->associate($user);
        $this->hydrateSocialAccount($socialAccount, $providerUser)->save();

        return [$user, $wasCreated];
    }

    private function hydrateSocialAccount(SocialAccount $socialAccount, ProviderUser $providerUser): SocialAccount
    {
        $providerEmail = filled($providerUser->getEmail())
            ? Str::lower((string) $providerUser->getEmail())
            : null;

        return $socialAccount->fill([
            'provider_email' => $providerEmail,
            'provider_avatar_url' => $providerUser->getAvatar(),
            'provider_token' => $providerUser->token,
            'provider_refresh_token' => $providerUser->refreshToken,
            'provider_token_expires_at' => $providerUser->expiresIn
                ? now()->addSeconds($providerUser->expiresIn)
                : null,
        ]);
    }

    private function splitName(ProviderUser $providerUser): array
    {
        $fullName = trim((string) ($providerUser->getName() ?: 'Utilisateur E-Couture'));
        $parts = preg_split('/\s+/', $fullName) ?: [];
        $prenom = Arr::first($parts) ?: 'Utilisateur';
        $nom = trim(implode(' ', array_slice($parts, 1))) ?: 'E-Couture';

        return [$prenom, $nom];
    }

    private function buildCallbackUrl(string $provider, User $user, string $token, bool $wasCreated): string
    {
        $baseUrl = config('services.mobile_auth.callback_url');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl.$separator.http_build_query([
            'provider' => $provider,
            'token' => $token,
            'registered' => $wasCreated ? '1' : '0',
            'external_id' => $user->external_id,
        ]);
    }
}
