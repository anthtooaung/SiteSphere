<?php

namespace App\Services;

use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseStorageService
{
    private $bucket;

    public function __construct()
    {
        $this->bucket = Firebase::project()->storage()->getBucket();
    }

    /**
     * Upload a base64 encoded image to Firebase Storage.
     *
     * @return string The public download URL
     */
    public function uploadBase64Image(string $dataUrl, string $folder = 'profile_images'): string
    {
        preg_match('/^data:image\/(png|jpe?g|gif);base64,/', $dataUrl, $matches);

        $extension = match (strtolower($matches[1] ?? 'png')) {
            'jpeg', 'jpg' => 'jpg',
            'gif' => 'gif',
            default => 'png',
        };

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $fileName = Str::uuid()->toString().'.'.$extension;
        $path = $folder.'/'.$fileName;

        $this->bucket->upload(
            base64_decode($base64, true),
            [
                'name' => $path,
                'metadata' => [
                    'contentType' => 'image/'.$extension,
                    'cacheControl' => 'public, max-age=31536000',
                ],
            ]
        );

        // Make the file publicly accessible
        $this->bucket->object($path)->update(['acl' => []], [' predefinedAcl' => 'publicRead']);

        return $this->getPublicUrl($path);
    }

    /**
     * Upload a file from a UploadedFile instance to Firebase Storage.
     *
     * @return string The public download URL
     */
    public function uploadFile($file, string $folder = 'profile_images'): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid()->toString().'.'.$extension;
        $path = $folder.'/'.$fileName;

        $this->bucket->upload(
            file_get_contents($file->getRealPath()),
            [
                'name' => $path,
                'metadata' => [
                    'contentType' => $file->getMimeType(),
                    'cacheControl' => 'public, max-age=31536000',
                ],
            ]
        );

        $this->bucket->object($path)->update(['acl' => []], [' predefinedAcl' => 'publicRead']);

        return $this->getPublicUrl($path);
    }

    /**
     * Delete a file from Firebase Storage by its path or URL.
     */
    public function delete(string $pathOrUrl): void
    {
        $path = $this->extractPath($pathOrUrl);

        if ($path && $this->bucket->object($path)->exists()) {
            $this->bucket->object($path)->delete();
        }
    }

    /**
     * Get the public download URL for a file.
     */
    public function getPublicUrl(string $path): string
    {
        return $this->bucket->object($path)->signedUrl(now()->addYears(10));
    }

    /**
     * Extract the storage path from a full URL or return the path as-is.
     */
    private function extractPath(string $pathOrUrl): ?string
    {
        if (Str::startsWith($pathOrUrl, ['http://', 'https://'])) {
            // Extract path from Firebase Storage URL
            $parsed = parse_url($pathOrUrl);
            if (isset($parsed['path'])) {
                // Firebase URLs have format: /v0/b/{bucket}/o/{encoded-path}
                $path = urldecode($parsed['path']);
                $matches = [];
                if (preg_match('#/o/(.+)$#', $path, $matches)) {
                    return $matches[1];
                }
            }
            return null;
        }

        return $pathOrUrl;
    }
}
