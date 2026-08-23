<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Notifications\CategoryStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'order', 'is_active', 'status', 'created_by'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => CategoryStatus::class,
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approve(): void
    {
        $this->update(['status' => CategoryStatus::Aprovado, 'is_active' => true]);

        if ($this->creator) {
            Notification::send($this->creator, new CategoryStatusUpdated($this));
        }
    }

    public function reject(): void
    {
        $this->update(['status' => CategoryStatus::Rejeitado]);

        if ($this->creator) {
            Notification::send($this->creator, new CategoryStatusUpdated($this));
        }
    }
}
