<?php

namespace App\Providers;

use App\ThemePreferences;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
        // Share user preference values with all views so Blade can easily read them.
        View::composer('*', function ($view) {
            static $cachedData = null;
            static $cachedUserId = null;
            static $cachedAuthCheck = null;

            $currentUserId = Auth::id();
            $currentAuthCheck = Auth::check();

            if ($cachedData === null || $cachedUserId !== $currentUserId || $cachedAuthCheck !== $currentAuthCheck) {
                $themePreferences = app(ThemePreferences::class);
                $user = Auth::user();
                $colors = $themePreferences->colorsFor($user);
                $toastPosition = 'top-end';
                $fontFamily = null;
                $menuBarLocation = 'left';
                $isDarkMode = $colors['background'] === '#000000';

                if ($user) {
                    $settings = $user->settings()->with(['theme', 'customTheme'])->first();
                    $fontFamily = $user->currentFonts()
                        ->latest('user_current_fonts.created_at')
                        ->value('font_family');

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
                        $fontFamily = DB::table('fonts')
                            ->where('is_default', true)
                            ->value('font_family');
                    } catch (\Throwable $e) {
                        $fontFamily = null;
                    }
                }

                $cachedData = [
                    'themeColors' => $colors,
                    'toastPosition' => $toastPosition,
                    'fontFamily' => $fontFamily,
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
