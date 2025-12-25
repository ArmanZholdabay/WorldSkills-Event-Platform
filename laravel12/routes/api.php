<?php

use App\Http\Controllers\Api\AttendeeAuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use Illuminate\Support\Facades\Route;

// Attendee Authentication
Route::post('/v1/login', [AttendeeAuthController::class, 'login']);
Route::post('/v1/logout', [AttendeeAuthController::class, 'logout']);

// Event API
Route::get('/v1/organizers/{organizerSlug}/events/{eventSlug}', [EventController::class, 'show']);

// Registration API
Route::post('/v1/organizers/{organizerSlug}/events/{eventSlug}/registration', [RegistrationController::class, 'register']);
Route::get('/v1/registrations', [RegistrationController::class, 'index']);
