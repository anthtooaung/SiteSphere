<?php

namespace App\Providers;

use App\ThemePreferences;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->numbers()
                ->symbols();

            return app()->environment('production')
                ? $rule->uncompromised()
                : $rule;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Cache the Agent instance per request so @mobile/@desktop don't re-parse the User-Agent.
        $agent = new Agent;

        // Share user preference values with root layout views only (not every partial/component).
        View::composer('*', function ($view) {
            static $cachedData = null;
            static $cachedUserId = null;
            static $cachedAuthCheck = null;

            $currentUserId = Auth::id();
            $currentAuthCheck = Auth::check();

            if ($cachedData === null || $cachedUserId !== $currentUserId || $cachedAuthCheck !== $currentAuthCheck) {
                $user = Auth::user();
                $toastPosition = 'top-end';
                $fontFamily = null;
                $googleFamily = null;
                $menuBarLocation = 'left';
                $isDarkMode = false;

                // Load settings once and share with ThemePreferences to avoid a duplicate query.
                $settings = null;

                if ($user) {
                    $settings = $user->settings()->with(['theme', 'customTheme'])->first();
                    $font = $user->currentFonts()
                        ->latest('user_current_fonts.created_at')
                        ->first(['fonts.font_family', 'fonts.google_family']);

                    if ($font) {
                        $fontFamily = $font->font_family;
                        $googleFamily = $font->google_family;
                    }

                    if ($fontFamily) {
                        $fontFamily = Str::of((string) preg_replace('/[^A-Za-z0-9\s,\-_"\'()]/', '', $fontFamily))
                            ->trim()
                            ->toString();
                    }

                    if ($settings && in_array($settings->noti_location, ['top-start', 'top-end', 'bottom-end', 'bottom-start'], true)) {
                        $toastPosition = $settings->noti_location;
                    }

                    if ($settings && in_array($settings->menuBar_location, ['top', 'right', 'bottom', 'left'], true)) {
                        $menuBarLocation = $settings->menuBar_location;
                    }

                    $isDarkMode = (bool) ($settings?->dark_mode ?? false);
                } else {
                    try {
                        $font = DB::table('fonts')
                            ->where('is_default', true)
                            ->first(['font_family', 'google_family']);
                        if ($font) {
                            $fontFamily = $font->font_family;
                            $googleFamily = $font->google_family;
                        }
                    } catch (\Throwable $e) {
                        $fontFamily = null;
                        $googleFamily = null;
                    }
                }

                $themePreferences = app(ThemePreferences::class);
                $colors = $themePreferences->colorsFor($user, $settings);

                $cachedData = [
                    'themeColors' => $colors,
                    'toastPosition' => $toastPosition,
                    'fontFamily' => $fontFamily,
                    'googleFamily' => $googleFamily,
                    'menuBarLocation' => $menuBarLocation,
                    'isDarkMode' => $isDarkMode,
                ];
                $cachedUserId = $currentUserId;
                $cachedAuthCheck = $currentAuthCheck;
            }

            $view->with($cachedData);
        });

        Blade::if('mobile', function () {
            return (new Agent)->isMobile();
        });

        Blade::if('desktop', function () {
            return (new Agent)->isDesktop();
        });
    }
}
