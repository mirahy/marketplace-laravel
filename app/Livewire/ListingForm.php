<?php

namespace App\Livewire;

use App\Enums\CategoryStatus;
use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\NewCategoryRequested;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
class ListingForm extends Component
{
    use WithFileUploads;

    public ?Listing $listing = null;

    public string $title = '';

    public string $description = '';

    public ?float $price = null;

    public string $condition = 'usado';

    public ?int $categoryId = null;

    public bool $showNewCategoryForm = false;

    public string $newCategoryName = '';

    public string $city = '';

    public string $state = '';

    /** @var array<int, mixed> */
    public array $newPhotos = [];

    /** @var array<int, \App\Models\ListingImage> */
    public array $existingImages = [];

    public function mount(?Listing $listing = null): void
    {
        if ($listing?->exists) {
            $this->authorize('update', $listing);

            $this->listing = $listing;
            $this->title = $listing->title;
            $this->description = $listing->description;
            $this->price = (float) $listing->price;
            $this->condition = $listing->condition->value;
            $this->categoryId = $listing->category_id;
            $this->city = $listing->city;
            $this->state = $listing->state;
            $this->existingImages = $listing->images()->orderBy('order')->get()->all();
        }
    }

    public function createCategory(): void
    {
        $data = $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255'],
        ]);

        $category = Category::create([
            'name' => $data['newCategoryName'],
            'slug' => Str::slug($data['newCategoryName']).'-'.Str::random(6),
            'is_active' => false,
            'status' => CategoryStatus::Pendente,
            'created_by' => Auth::id(),
        ]);

        Notification::send(User::where('is_admin', true)->get(), new NewCategoryRequested($category));

        $this->categoryId = $category->id;
        $this->newCategoryName = '';
        $this->showNewCategoryForm = false;

        session()->flash('status', 'Categoria enviada para aprovação e já selecionada neste anúncio.');
    }

    public function removeExistingImage(int $imageId): void
    {
        $image = collect($this->existingImages)->firstWhere('id', $imageId);

        if (! $image) {
            return;
        }

        if (! Str::startsWith($image->path, 'http')) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        $this->existingImages = collect($this->existingImages)
            ->reject(fn ($img) => $img->id === $imageId)
            ->values()
            ->all();
    }

    public function save()
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'in:novo,usado'],
            'categoryId' => ['required', 'exists:categories,id'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:2'],
            'newPhotos.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'condition' => $data['condition'],
            'category_id' => $data['categoryId'],
            'city' => $data['city'],
            'state' => strtoupper($data['state']),
        ];

        if ($this->listing) {
            $this->authorize('update', $this->listing);
            $this->listing->update($payload);
            $listing = $this->listing;
        } else {
            $listing = Auth::user()->listings()->create($payload + [
                'status' => ListingStatus::EmAnalise,
            ]);
        }

        $startOrder = count($this->existingImages);

        foreach ($this->newPhotos as $i => $photo) {
            $path = $photo->store('listings', 'public');
            $listing->images()->create([
                'path' => $path,
                'order' => $startOrder + $i,
            ]);
        }

        session()->flash('status', 'Anúncio salvo com sucesso.');

        return redirect()->route('listings.mine');
    }

    public function render()
    {
        $categories = Category::query()->where('is_active', true)->orderBy('order')->get();

        if ($this->categoryId && ! $categories->contains('id', $this->categoryId)) {
            $selected = Category::find($this->categoryId);

            if ($selected) {
                $categories->push($selected);
            }
        }

        return view('livewire.listing-form', [
            'categories' => $categories,
            'conditions' => ListingCondition::cases(),
        ]);
    }
}
