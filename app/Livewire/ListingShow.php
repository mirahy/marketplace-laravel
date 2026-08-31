<?php

namespace App\Livewire;

use App\Enums\ListingStatus;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ListingShow extends Component
{
    public Listing $listing;

    /** @var array<int, int> */
    public array $viewHistory = [];

    public function mount(Listing $listing): void
    {
        $this->listing = $listing->load(['images', 'category', 'user']);

        $viewedKey = "viewed_listing_{$listing->id}";

        if (! session()->has($viewedKey)) {
            $listing->increment('views_count');
            session()->put($viewedKey, true);
        }
    }

    /**
     * @param  array<int, mixed>  $ids
     */
    public function setViewHistory(array $ids): void
    {
        $this->viewHistory = collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->take(20)
            ->values()
            ->all();
    }

    public function sendMessage()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::id() === $this->listing->user_id) {
            return;
        }

        $conversation = Conversation::query()->firstOrCreate([
            'listing_id' => $this->listing->id,
            'buyer_id' => Auth::id(),
        ], [
            'seller_id' => $this->listing->user_id,
        ]);

        return redirect()->route('messages.show', $conversation);
    }

    public function pause(): void
    {
        $this->authorize('update', $this->listing);
        $this->listing->update(['status' => ListingStatus::Pausado]);
        $this->listing->refresh();
    }

    public function markAsSold(): void
    {
        $this->authorize('update', $this->listing);
        $this->listing->update(['status' => ListingStatus::Vendido]);
        $this->listing->refresh();
    }

    /**
     * Up to 10 suggestions: the first 2 from the current listing's category,
     * the rest from the categories of the recently viewed listings (most
     * recent first). If the current category has no more items, the whole
     * quota naturally falls through to the view-history-based listings.
     */
    protected function resolveRelated(): Collection
    {
        $target = 10;
        $excludeIds = [$this->listing->id, ...$this->viewHistory];

        $categoryItems = Listing::query()
            ->with('images')
            ->active()
            ->where('category_id', $this->listing->category_id)
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(2)
            ->get();

        $remaining = $target - $categoryItems->count();

        if ($remaining <= 0 || empty($this->viewHistory)) {
            return $categoryItems;
        }

        $historyCategoryIds = Listing::query()
            ->whereIn('id', $this->viewHistory)
            ->pluck('category_id', 'id');

        $orderedCategoryIds = collect($this->viewHistory)
            ->map(fn ($id) => $historyCategoryIds->get($id))
            ->filter()
            ->unique()
            ->values();

        if ($orderedCategoryIds->isEmpty()) {
            return $categoryItems;
        }

        $alreadyPicked = $categoryItems->pluck('id')->all();

        $superset = Listing::query()
            ->with('images')
            ->active()
            ->whereIn('category_id', $orderedCategoryIds)
            ->whereNotIn('id', [...$excludeIds, ...$alreadyPicked])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(60)
            ->get()
            ->groupBy('category_id');

        $historyItems = collect();

        foreach ($orderedCategoryIds as $categoryId) {
            if ($historyItems->count() >= $remaining) {
                break;
            }

            foreach ($superset->get($categoryId, collect()) as $item) {
                if ($historyItems->count() >= $remaining) {
                    break;
                }

                $historyItems->push($item);
            }
        }

        return $categoryItems->concat($historyItems);
    }

    protected function resolveOgImage(): string
    {
        $image = $this->listing->images->first();

        if (! $image) {
            return asset('img/adb.png');
        }

        return Str::startsWith($image->path, 'http') ? $image->path : Storage::url($image->path);
    }

    public function render()
    {
        return view('livewire.listing-show', [
            'related' => $this->resolveRelated(),
        ])->layoutData([
            'title' => $this->listing->title.' - '.config('app.name'),
            'ogTitle' => $this->listing->title,
            'ogDescription' => $this->listing->condition->getLabel().' · '.Number::currency($this->listing->price, in: 'BRL'),
            'ogImage' => $this->resolveOgImage(),
            'ogUrl' => route('listings.show', $this->listing),
            'ogType' => 'product',
        ]);
    }
}
