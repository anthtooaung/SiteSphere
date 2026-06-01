<?php

namespace App\View\Components\layout;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class UploadPost extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Collection $categories) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layout.upload-post');
    }
}
