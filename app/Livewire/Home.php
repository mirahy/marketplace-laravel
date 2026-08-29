<?php

namespace App\Livewire;

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->whereHas('listings', fn ($q) => $q->active())
                ->inRandomOrder()
                ->limit(10)
                ->get(),
            'featured' => Listing::query()
                ->with(['images', 'category'])
                ->where('status', ListingStatus::Ativo)
                ->where('is_featured', true)
                ->latest('published_at')
                ->take(8)
                ->get(),
            'recent' => Listing::query()
                ->with(['images', 'category'])
                ->where('status', ListingStatus::Ativo)
                ->latest('published_at')
                ->take(12)
                ->get(),
        ]);
    }
}
