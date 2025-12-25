<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    protected $fillable = [
        'organizer_id',
        'name',
        'slug',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(EventTicket::class);
    }

    public function registrations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Registration::class,
            EventTicket::class,
            'event_id', // Foreign key on event_tickets table
            'ticket_id' // Foreign key on registrations table
        );
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(EventRating::class);
    }
}
