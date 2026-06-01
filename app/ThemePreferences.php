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

        if ($user) {
            $settings = $user->settings()->with(['theme', 'customTheme'])->first();
            $isDarkMode = (bool) ($settings?->dark_mode ?? false);

            if ($settings?->custom_theme_id && $settings->customTheme) {
                $accentColor = $this->validAccentColor($settings->customTheme->accent_color);
            } elseif ($settings?->theme) {
                $accentColor = $this->validAccentColor($settings->theme->accent_color);
            }
        }

        return [
            'accent' => $accentColor,
            'background' => $isDarkMode ? '#000000' : '#ffffff',
            'text' => $isDarkMode ? '#ffffff' : '#0d1b2a',
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
}
