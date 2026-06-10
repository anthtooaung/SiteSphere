<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppearanceRequest;
use App\Models\CustomThemes;
use App\Models\Fonts;
use App\Models\Settings;
use App\Models\Themes;
use App\Models\User;
use App\ThemePreferences;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppearanceController extends Controller
{
    private const PRESET_THEMES = [
        '#6c5ce7' => 'Default Purple',
        '#DC2626' => 'Crimson Red',
        '#f4c543' => 'Golden Yellow',
        '#059669' => 'Emerald Green',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $settings = $this->settingsFor($user);
        $currentFont = $user->currentFonts()
            ->latest('user_current_fonts.created_at')
            ->first();
        $customTheme = CustomThemes::query()->where('user_id', $user->id)->first();

        return view('layout.menu.appearance', [
            'appearanceSettings' => $settings->load(['theme', 'customTheme']),
            'customTheme' => $customTheme,
            'currentFontId' => $currentFont?->id,
            'fonts' => Fonts::query()->orderBy('sort_order')->orderBy('display_name')->get(),
            'presetThemes' => $this->presetThemes(),
        ]);
    }

    public function update(UpdateAppearanceRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $settings = $this->settingsFor($user);

        $settings->forceFill([
            'dark_mode' => (bool) $validated['dark_mode'],
            'menuBar_location' => $validated['menuBar_location'],
            'noti_location' => $validated['noti_location'],
            'use_custom_theme' => (bool) $validated['use_custom_theme'],
        ]);

        if ((bool) $validated['use_custom_theme']) {
            $customTheme = CustomThemes::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'background_color' => $validated['background_color'],
                    'text_color' => $validated['text_color'],
                    'accent_color' => $validated['accent_color'],
                ],
            );

            $settings->custom_theme_id = $customTheme->id;
        } else {
            $settings->theme_id = (int) $validated['theme_id'];
        }

        $settings->save();
        $user->currentFonts()->sync([(int) $validated['font_id']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appearance settings saved.',
            ]);
        }

        return back()->with('success', 'Appearance settings saved.');
    }

    private function settingsFor(User $user): Settings
    {
        $theme = Themes::query()->firstOrCreate([
            'accent_color' => ThemePreferences::DEFAULT_ACCENT_COLOR,
        ]);

        return $user->settings()->firstOrCreate(
            [],
            [
                'menuBar_location' => 'left',
                'noti_location' => 'top-end',
                'dark_mode' => false,
                'user_post_visible' => false,
                'theme_id' => $theme->id,
            ],
        );
    }

    /**
     * @return Collection<int, array{id: int, name: string, accent_color: string}>
     */
    private function presetThemes(): Collection
    {
        $accentColors = array_keys(self::PRESET_THEMES);
        $themes = Themes::query()
            ->whereIn('accent_color', $accentColors)
            ->get()
            ->keyBy('accent_color');

        return collect(self::PRESET_THEMES)
            ->map(function (string $name, string $accentColor) use ($themes): array {
                $theme = $themes->get($accentColor) ?? Themes::query()->create([
                    'accent_color' => $accentColor,
                ]);

                return [
                    'id' => $theme->id,
                    'name' => $name,
                    'accent_color' => $accentColor,
                ];
            })
            ->values();
    }
}
