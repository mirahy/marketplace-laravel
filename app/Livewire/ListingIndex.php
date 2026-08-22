<?php

namespace App\Livewire;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class ListingIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public ?float $minPrice = null;

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public ?string $state = null;

    #[Url]
    public ?string $condition = null;

    #[Url]
    public string $sort = 'recent';

    public function mount(?Category $category = null): void
    {
        if ($category) {
            $this->categoryId = $category->id;
        }
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $listings = Listing::query()
            ->with(['images', 'category'])
            ->where('status', ListingStatus::Ativo)
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->minPrice, fn ($q) => $q->where('price', '>=', $this->minPrice))
            ->when($this->maxPrice, fn ($q) => $q->where('price', '<=', $this->maxPrice))
            ->when($this->state, fn ($q) => $q->where('state', $this->state))
            ->when($this->condition, fn ($q) => $q->where('condition', $this->condition))
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when($this->sort === 'recent', fn ($q) => $q->latest('published_at'))
            ->paginate(12);

        return view('livewire.listing-index', [
            'listings' => $listings,
            'categories' => Category::query()->where('is_active', true)->orderBy('order')->get(),
            'conditions' => ListingCondition::cases(),
        ]);
    }
}
