<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse|SymfonyRedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth provider callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['social' => 'Unable to authenticate with this social provider.']);
        }

        $socialAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', (string) $socialiteUser->getId())
            ->first();

        if ($socialAccount) {
            Auth::login($socialAccount->user);

            return redirect()->intended(route('home', absolute: false));
        }

        $email = $socialiteUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors(['social' => 'Your social account did not provide an email address.']);
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $this->nameFor($socialiteUser, $email),
                'password' => Str::password(32),
                'user_image' => $socialiteUser->getAvatar(),
                'is_verified' => true,
            ],
        );

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => (string) $socialiteUser->getId(),
            'token' => $socialiteUser->token ?? null,
        ]);

        Auth::login($user);

        return redirect()->intended(route('home', absolute: false));
    }

    private function nameFor(SocialiteUser $socialiteUser, string $email): string
    {
        return $socialiteUser->getName()
            ?: $socialiteUser->getNickname()
            ?: Str::before($email, '@');
    }
}
