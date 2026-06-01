<?php

namespace App\Providers;

use App\ThemePreferences;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
            $themePreferences = app(ThemePreferences::class);
            $colors = $themePreferences->colorsFor(Auth::user());
            $toastPosition = 'top-end';
            $fontFamily = null;
            $menuBarLocation = 'left';
            $isDarkMode = $colors['background'] === '#000000';

            if (Auth::check()) {
                $user = Auth::user();
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
            }

            $view->with('themeColors', $colors);
            $view->with('toastPosition', $toastPosition);
            $view->with('fontFamily', $fontFamily);
            $view->with('menuBarLocation', $menuBarLocation);
            $view->with('isDarkMode', $isDarkMode);
        });

        Blade::if('mobile', function () {
            return (new Agent)->isMobile();
        });

        Blade::if('desktop', function () {
            return (new Agent)->isDesktop();
        });
    }
}
