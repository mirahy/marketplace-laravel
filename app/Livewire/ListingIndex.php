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
        $words = preg_split('/\s+/', trim($this->search), -1, PREG_SPLIT_NO_EMPTY);

        if (! $this->categoryId) {
            $bestMatch = Category::query()
                ->where('is_active', true)
                ->get(['id', 'name'])
                ->map(fn ($c) => ['category' => $c, 'range' => $this->findWordSequence($words, $c->name)])
                ->filter(fn ($pair) => $pair['range'] !== null)
                ->sortByDesc(fn ($pair) => $pair['range'][1] - $pair['range'][0])
                ->first();

            if ($bestMatch) {
                $this->categoryId = $bestMatch['category']->id;
                $words = $this->removeWordRange($words, $bestMatch['range']);
            }
        }

        if (! $this->condition) {
            foreach (ListingCondition::cases() as $case) {
                $range = $this->findWordSequence($words, $case->getLabel());

                if ($range) {
                    $this->condition = $case->value;
                    $words = $this->removeWordRange($words, $range);
                    break;
                }
            }
        }

        if (! $this->state) {
            foreach (BrazilianState::cases() as $case) {
                $range = $this->findWordSequence($words, $case->value)
                    ?? $this->findWordSequence($words, $case->getLabel());

                if ($range) {
                    $this->state = $case->value;
                    $words = $this->removeWordRange($words, $range);
                    break;
                }
            }
        }

        $this->search = implode(' ', $words);
    }

    /**
     * Finds $needle (matched word-by-word, case/accent-insensitive) as a
     * contiguous run inside $words, returning its [start, end] indexes
     * (inclusive) or null when it isn't present.
     *
     * @param  array<int, string>  $words
     * @return array{0: int, 1: int}|null
     */
    protected function findWordSequence(array $words, string $needle): ?array
    {
        $needleWords = preg_split('/\s+/', trim($needle), -1, PREG_SPLIT_NO_EMPTY);
        $needleCount = count($needleWords);

        if ($needleCount === 0) {
            return null;
        }

        $normalize = fn (string $word) => Str::lower(Str::ascii($word));
        $normalizedWords = array_map($normalize, $words);
        $normalizedNeedle = array_map($normalize, $needleWords);

        for ($i = 0; $i <= count($normalizedWords) - $needleCount; $i++) {
            if (array_slice($normalizedWords, $i, $needleCount) === $normalizedNeedle) {
                return [$i, $i + $needleCount - 1];
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $words
     * @param  array{0: int, 1: int}  $range
     * @return array<int, string>
     */
    protected function removeWordRange(array $words, array $range): array
    {
        [$start, $end] = $range;
        array_splice($words, $start, $end - $start + 1);

        return array_values($words);
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
