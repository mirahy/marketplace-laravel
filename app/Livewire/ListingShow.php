<?php

namespace App\Livewire;

use App\Enums\ListingStatus;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ListingShow extends Component
{
    public Listing $listing;

    public function mount(Listing $listing): void
    {
        $this->listing = $listing->load(['images', 'category', 'user']);

        $viewedKey = "viewed_listing_{$listing->id}";

        if (! session()->has($viewedKey)) {
            $listing->increment('views_count');
            session()->put($viewedKey, true);
        }
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

    public function render()
    {
        $related = Listing::query()
            ->with('images')
            ->where('status', ListingStatus::Ativo)
            ->where('category_id', $this->listing->category_id)
            ->whereKeyNot($this->listing->id)
            ->take(4)
            ->get();

        return view('livewire.listing-show', [
            'related' => $related,
        ]);
    }
}
