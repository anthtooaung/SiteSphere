<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSecurityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function edit(Request $request): View
    {
        return view('layout.menu.security', [
            'securityUser' => $request->user()->loadMissing('settings'),
        ]);
    }

    public function update(UpdateSecurityRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->forceFill([
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ])->save();

        if (! $user->settings()->exists()) {
            $user->provisionDefaultPreferences();
        }

        $user->settings()->update([
            'user_post_visible' => $request->boolean('user_post_visible'),
        ]);

        if (! empty($validated['password'])) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'password_set' => true,
            ])->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Security settings saved successfully.',
            ]);
        }

        return redirect()
            ->route('security')
            ->with('success', 'Security settings saved successfully.');
    }
}
