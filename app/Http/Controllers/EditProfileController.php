<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEditProfileRequest;
use App\Services\FirebaseStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EditProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('layout.menu.edit-profile', [
            'profileUser' => $request->user(),
        ]);
    }

    public function update(UpdateEditProfileRequest $request, FirebaseStorageService $storage): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $previousImage = $user->user_image;

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_dob' => $validated['user_dob'] ?: null,
            'user_phone' => $validated['user_phone'] ?: null,
            'user_bio' => $validated['user_bio'] ?: null,
        ]);

        if (! empty($validated['cropped_avatar'])) {
            $user->user_image = $storage->uploadBase64Image($validated['cropped_avatar']);
        }

        $user->save();

        // Delete previous image from Firebase if it was a Firebase URL
        if ($previousImage && $previousImage !== $user->user_image && $this->isFirebaseImage($previousImage)) {
            $storage->delete($previousImage);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile changes saved successfully.',
            ]);
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', 'Profile changes saved successfully.');
    }

    private function isFirebaseImage(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
