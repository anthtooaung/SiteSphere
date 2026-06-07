<?php

namespace App;

use App\Models\User;

class ThemePreferences
{
    public const DEFAULT_ACCENT_COLOR = '#6c5ce7';

    /**
     * @return array{accent: string, background: string, text: string}
     */
    public function colorsFor(?User $user): array
    {
        $isDarkMode = false;
        $accentColor = self::DEFAULT_ACCENT_COLOR;
        $backgroundColor = '#ffffff';
        $textColor = '#0d1b2a';

        if ($user) {
            $settings = $user->settings()->with(['theme', 'customTheme'])->first();
            $isDarkMode = (bool) ($settings?->dark_mode ?? false);
            $backgroundColor = $isDarkMode ? '#000000' : '#ffffff';
            $textColor = $isDarkMode ? '#ffffff' : '#0d1b2a';

            if ($settings?->use_custom_theme && $settings->customTheme) {
                $accentColor = $this->validAccentColor($settings->customTheme->accent_color);
                $backgroundColor = $this->validThemeColor($settings->customTheme->background_color, $backgroundColor);
                $textColor = $this->validThemeColor($settings->customTheme->text_color, $textColor);
            } elseif ($settings?->theme) {
                $accentColor = $this->validAccentColor($settings->theme->accent_color);
            }
        }

        return [
            'accent' => $accentColor,
            'background' => $backgroundColor,
            'text' => $textColor,
        ];
    }

    public function accentColorFor(?User $user): string
    {
        return $this->colorsFor($user)['accent'];
    }

    public function validAccentColor(?string $accentColor): string
    {
        if ($accentColor && preg_match('/^#[0-9A-Fa-f]{6}$/', $accentColor) === 1) {
            return $accentColor;
        }

        return self::DEFAULT_ACCENT_COLOR;
    }

    private function validThemeColor(?string $color, string $fallback): string
    {
        if ($color && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1) {
            return $color;
        }

        return $fallback;
    }
}
