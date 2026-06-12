<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AboutUsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('layout.about-us');
    }
}
