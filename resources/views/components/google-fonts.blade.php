<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@php
    $selectedFont = $fontFamily ?? 'Figtree';
    // Extract just the primary font family name (before any comma/fallback)
    $primaryFont = trim(explode(',', $selectedFont)[0], " \t\n\r\0\x0B'\"");
    // Map of available fonts to their Google Fonts URL-encoded names
    $availableFonts = [
        'Figtree' => 'Figtree:wght@400;500;600;700',
        'Inter' => 'Inter:wght@400;500;600;700',
        'Nunito' => 'Nunito:wght@400;500;600;700',
        'Open Sans' => 'Open+Sans:wght@400;500;600;700',
        'Poppins' => 'Poppins:wght@400;500;600;700',
        'Roboto' => 'Roboto:wght@400;500;600;700',
    ];
    $fontParam = $availableFonts[$primaryFont] ?? $availableFonts['Figtree'];
@endphp
<link href="https://fonts.googleapis.com/css2?family={{ $fontParam }}&display=swap" rel="stylesheet">
