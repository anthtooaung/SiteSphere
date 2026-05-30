<?php

namespace App\Providers;

use App\Models\Themes;
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
            $colors = [];
            $toastPosition = 'top-end';
            $fontFamily = null;
            $menuBarLocation = 'left';
            $isDarkMode = false;

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

                if ($settings && $settings->custom_theme_id && $settings->customTheme) {
                    $colors = [
                        'accent' => $settings->customTheme->accent_color,
                    ];
                } elseif ($settings && $settings->theme) {
                    $colors = [

                        'accent' => $settings->theme->accent_color,
                    ];
                }
            } else {
                $theme = Themes::query()->first();
                if ($theme) {
                    $colors = [
                        'accent' => $theme->accent_color,
                    ];
                }
            }

            $colors['background'] = $isDarkMode ? '#000000' : '#ffffff';
            $colors['text'] = $isDarkMode ? '#ffffff' : '#0d1b2a';

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
