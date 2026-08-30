<?php

namespace App\Models;

use App\Enums\AddressType;
use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Concerns\LogsAllChanges;
use App\Notifications\ListingStatusUpdated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory, SoftDeletes, LogsAllChanges;

    protected $fillable = [
        'category_id', 'title', 'description', 'price', 'condition',
        'status', 'city', 'state', 'is_featured', 'published_at',
        'address_type', 'address_street', 'address_number',
        'address_neighborhood', 'address_complement',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'condition' => ListingCondition::class,
            'status' => ListingStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'address_type' => AddressType::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            if (! $listing->slug) {
                $listing->slug = Str::slug($listing->title).'-'.Str::random(6);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('order');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', ListingStatus::Ativo);
    }

    public function approve(): void
    {
        $this->update(['status' => ListingStatus::Ativo, 'published_at' => now()]);

        if ($this->user) {
            Notification::send($this->user, new ListingStatusUpdated($this));
        }
    }

    public function reject(): void
    {
        $this->update(['status' => ListingStatus::Rejeitado]);

        if ($this->user) {
            Notification::send($this->user, new ListingStatusUpdated($this));
        }
    }

    /**
     * @return array<int, string>
     */
    public function addressLines(): array
    {
        $lines = [];

        $street = collect([$this->address_type?->getLabel(), $this->address_street])
            ->filter()
            ->implode(' ');

        if ($street !== '' && $this->address_number) {
            $lines[] = "{$street}, {$this->address_number}";
        } elseif ($street !== '') {
            $lines[] = $street;
        } elseif ($this->address_number) {
            $lines[] = $this->address_number;
        }

        $cityState = collect([$this->city, $this->state])->filter()->implode('/');
        $neighborhoodLine = collect([$this->address_neighborhood, $cityState])->filter()->implode(' - ');

        if ($neighborhoodLine !== '') {
            $lines[] = $neighborhoodLine;
        }

        if ($this->address_complement) {
            $lines[] = $this->address_complement;
        }

        return $lines;
    }
}
