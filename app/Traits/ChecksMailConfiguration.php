<?php

namespace App\Traits;

trait ChecksMailConfiguration
{
    /**
     * Determine if the current mail driver is properly configured.
     *
     * Supports both Resend (local/staging) and SMTP/Gmail (production).
     * Returns false if the required credentials are missing, so callers
     * can bail out early rather than throwing an uncaught exception.
     */
    private function isMailConfigured(): bool
    {
        return match (config('mail.default')) {
            'resend' => (string) config('services.resend.key') !== '',
            'smtp' => (string) config('mail.mailers.smtp.password') !== '',
            default => true,
        };
    }
}
