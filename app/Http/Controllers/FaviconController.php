<?php

namespace App\Http\Controllers;

use App\ThemePreferences;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaviconController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ThemePreferences $themePreferences): Response
    {
        $accentColor = $themePreferences->accentColorFor($request->user());
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 88.5 99.5" fill="none" role="img" aria-label="SiteSphere">
            <path fill="{$accentColor}" d="M44.5 28.75L28.75 37.25L28.75 38.75L63.25 58.5L66.5 60.75L65.75 62.5L43.75 74.25L9.75 54.25L7.75 53.25L6 54L6.25 72L43.75 93.5L46 93.25L82.5 71.75L82 50Z"/>
            <path fill="{$accentColor}" d="M43.25 6L6.25 27.75L6.25 49L41 69.25L46.25 69.75L60.25 61.5L56.25 58L22 39L22 37.75L45 25.25L82 46.25L82.5 27.75L60.5 14L45.5 6Z"/>
        </svg>
        SVG;

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'private, no-store');
    }
}
