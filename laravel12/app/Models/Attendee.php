<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendee extends Model
{
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'registration_code',
        'login_token',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function eventRatings(): HasMany
    {
        return $this->hasMany(EventRating::class);
    }

    public function sessionRatings(): HasMany
    {
        return $this->hasMany(SessionRating::class);
    }
}
