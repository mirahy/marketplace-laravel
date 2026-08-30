<?php

namespace App\Livewire;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class MyListings extends Component
{
    use WithPagination;

    public function markAsSold(Listing $listing): void
    {
        $this->authorize('update', $listing);
        $listing->update(['status' => ListingStatus::Vendido]);
    }

    public function pause(Listing $listing): void
    {
        $this->authorize('update', $listing);
        $listing->update(['status' => ListingStatus::Pausado]);
    }

    public function reactivate(Listing $listing): void
    {
        $this->authorize('update', $listing);
        $listing->update(['status' => ListingStatus::Ativo]);
    }

    public function delete(Listing $listing): void
    {
        $this->authorize('delete', $listing);
        $listing->delete();
    }

    public function render()
    {
        return view('livewire.my-listings', [
            'listings' => Auth::user()->listings()->with('images')->latest()->paginate(10),
        ]);
    }
}
