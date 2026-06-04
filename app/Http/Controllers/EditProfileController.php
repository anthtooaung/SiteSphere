<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEditProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('layout.menu.edit-profile', [
            'profileUser' => $request->user(),
        ]);
    }

    public function update(UpdateEditProfileRequest $request): RedirectResponse
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
            $user->user_image = $this->storeCroppedAvatar($validated['cropped_avatar']);
        }

        $user->save();

        if ($previousImage && $previousImage !== $user->user_image && $this->isLocalProfileImage($previousImage)) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', 'Profile changes saved successfully.');
    }

    private function storeCroppedAvatar(string $dataUrl): string
    {
        preg_match('/^data:image\/(png|jpe?g|gif);base64,/', $dataUrl, $matches);

        $extension = match (strtolower($matches[1] ?? 'png')) {
            'jpeg', 'jpg' => 'jpg',
            'gif' => 'gif',
            default => 'png',
        };

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $path = 'profile_images/'.Str::uuid()->toString().'.'.$extension;

        Storage::disk('public')->put($path, base64_decode($base64, true));

        return $path;
    }

    private function isLocalProfileImage(string $path): bool
    {
        return Str::startsWith($path, 'profile_images/')
            && ! Str::startsWith($path, ['http://', 'https://']);
    }
}
