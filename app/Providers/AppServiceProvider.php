<?php

namespace App\Providers;

use App\Models\Themes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
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
        // Share theme color values with all views so Blade can easily read them.
        View::composer('*', function ($view) {
            $colors = [];

            if (Auth::check()) {
                $user = Auth::user();
                $settings = $user->settings()->with(['theme', 'customTheme'])->first();

                if ($settings && $settings->custom_theme_id && $settings->customTheme) {
                    $colors = [
                        'background' => $settings->customTheme->background_color,
                        'text' => $settings->customTheme->text_color,
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

            $view->with('themeColors', $colors);
        });

        Blade::if('mobile', function () {
            return (new Agent())->isMobile();
        });

        Blade::if('desktop', function () {
            return (new Agent())->isDesktop();
        });
    }
}
