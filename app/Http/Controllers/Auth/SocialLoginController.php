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
use SweetAlert2\Laravel\Swal;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialLoginController extends Controller
{
    private const DEFAULT_TOAST_POSITION = 'top-end';

    private const TOAST_POSITIONS = [
        'top-start',
        'top-end',
        'bottom-end',
        'bottom-start',
    ];

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
            $user = $socialAccount->user;

            $this->fillMissingAvatar($user, $socialiteUser);

            Auth::login($user);

            $this->flashSuccessToast($user);

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
                'password_set' => false,
                'user_image' => $socialiteUser->getAvatar(),
                'is_verified' => true,
            ],
        );

        $this->fillMissingAvatar($user, $socialiteUser);

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => (string) $socialiteUser->getId(),
            'token' => $socialiteUser->token ?? null,
        ]);

        Auth::login($user);

        $this->flashSuccessToast($user);

        return redirect()->intended(route('home', absolute: false));
    }

    private function nameFor(SocialiteUser $socialiteUser, string $email): string
    {
        return $socialiteUser->getName()
            ?: $socialiteUser->getNickname()
            ?: Str::before($email, '@');
    }

    private function fillMissingAvatar(User $user, SocialiteUser $socialiteUser): void
    {
        $avatar = $socialiteUser->getAvatar();

        if ($user->user_image || ! $avatar) {
            return;
        }

        $user->forceFill([
            'user_image' => $avatar,
        ])->save();
    }

    private function toastPositionFor(User $user): string
    {
        $position = $user->settings()->value('noti_location');

        return in_array($position, self::TOAST_POSITIONS, true)
            ? $position
            : self::DEFAULT_TOAST_POSITION;
    }

    private function flashSuccessToast(User $user): void
    {
        Swal::fire([
            'toast' => true,
            'position' => $this->toastPositionFor($user),
            'showConfirmButton' => false,
            'timer' => 1000,
            'timerProgressBar' => true,
            'icon' => 'success',
            'title' => 'Signed in successfully',
            'text' => 'Welcome back to SiteSphere.',
            'didOpen' => '(toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }',
        ]);
    }
}
