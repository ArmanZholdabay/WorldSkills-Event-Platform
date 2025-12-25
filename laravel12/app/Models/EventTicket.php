<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTicket extends Model
{
    protected $table = 'event_tickets';

    protected $fillable = [
        'event_id',
        'name',
        'cost',
        'special_validity',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'special_validity' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'ticket_id');
    }
}
