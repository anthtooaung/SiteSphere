<?php

namespace App\View\Components;

use App\Models\Categories;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class CategoryBtn extends Component
{
    /**
     * @var Collection<int, Categories>
     */
    public Collection $categories;

    public function __construct(public string $mobileMode = 'both')
    {
        $this->categories = once(fn (): Collection => Categories::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get());
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.category-btn');
    }
}
