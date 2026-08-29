<?php

namespace App\Livewire;

use App\Enums\BrazilianState;
use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Str;
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

    public ?Category $category = null;

    public function mount(?Category $category = null): void
    {
        if ($category) {
            abort_unless($category->is_active, 404);

            $this->categoryId = $category->id;
            $this->category = $category;
        }

        if ($this->search !== '') {
            $this->detectFiltersFromSearchTerm();
        }
    }

    protected function detectFiltersFromSearchTerm(): void
    {
        $normalized = Str::lower(Str::ascii($this->search));
        $words = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        if (! $this->categoryId) {
            $match = Category::query()
                ->where('is_active', true)
                ->get(['id', 'name'])
                ->filter(fn ($c) => str_contains($normalized, Str::lower(Str::ascii($c->name))))
                ->sortByDesc(fn ($c) => strlen($c->name))
                ->first();

            if ($match) {
                $this->categoryId = $match->id;
            }
        }

        if (! $this->condition) {
            foreach (ListingCondition::cases() as $case) {
                if (str_contains($normalized, Str::lower(Str::ascii($case->getLabel())))) {
                    $this->condition = $case->value;
                    break;
                }
            }
        }

        if (! $this->state) {
            foreach (BrazilianState::cases() as $case) {
                if (in_array(Str::lower($case->value), $words, true)
                    || str_contains($normalized, Str::lower(Str::ascii($case->getLabel())))) {
                    $this->state = $case->value;
                    break;
                }
            }
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

        $categoryOptions = Category::query()->where('is_active', true)->orderBy('order')->pluck('name', 'id');

        $stateOptions = collect(BrazilianState::cases())->mapWithKeys(fn ($state) => [
            $state->value => $state->value.' - '.$state->getLabel(),
        ]);

        return view('livewire.listing-index', [
            'listings' => $listings,
            'categoryOptions' => $categoryOptions,
            'stateOptions' => $stateOptions,
            'conditions' => ListingCondition::cases(),
        ]);
    }
}
