<?php

if (! function_exists('maskEmail')) {
    /**
     * Mask an email address for privacy.
     *
     * Examples:
     * - anthony@gmail.com → an***ny@gmail.com
     * - ab@gmail.com → *b@gmail.com
     * - test12345@example.com → te***45@example.com
     */
    function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 4) {
            return str_repeat('*', strlen($local) - 1).substr($local, -1).'@'.$domain;
        }

        return substr($local, 0, 2).str_repeat('*', strlen($local) - 4).substr($local, -2).'@'.$domain;
    }
}
