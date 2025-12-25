<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $fillable = [
        'room_id',
        'title',
        'description',
        'speaker',
        'start',
        'end',
        'type',
        'cost',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'session_registrations')
            ->withTimestamps();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(SessionRating::class);
    }
}
