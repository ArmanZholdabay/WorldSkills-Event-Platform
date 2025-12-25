<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Registration extends Model
{
    protected $fillable = [
        'attendee_id',
        'ticket_id',
        'registration_time',
    ];

    protected $casts = [
        'registration_time' => 'datetime',
    ];

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'ticket_id');
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_registrations')
            ->withTimestamps();
    }
}
