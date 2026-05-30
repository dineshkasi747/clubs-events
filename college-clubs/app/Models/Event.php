<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'title',
        'description',
        'venue',
        'place',
        'start_time',
        'end_time',
        'date_string',
        'volunteers',
        'price',
        'status',
        'capacity',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    // Helper to check if event is full
    public function isFull(): bool
    {
        return $this->registrations()->where('status', '!=', 'cancelled')->count() >= $this->capacity;
    }

    // Helper to get spots remaining
    public function spotsRemaining(): int
    {
        $activeRegistrationsCount = $this->registrations()->where('status', '!=', 'cancelled')->count();
        return max(0, $this->capacity - $activeRegistrationsCount);
    }

    // Helper to print a unified event date representation
    public function getFormattedDateAttribute(): string
    {
        if ($this->start_time) {
            return $this->start_time->format('M d, Y @ h:i A');
        }

        return $this->date_string ?? 'Date Pending';
    }

    // Helper to print a unified event venue/place representation
    public function getFormattedVenueAttribute(): string
    {
        return $this->venue ?? ($this->place ?? 'Venue Pending');
    }
}
