<?php

use App\Http\Controllers\OrganizerAuthController;
use App\Http\Controllers\OrganizerEventController;
use Illuminate\Support\Facades\Route;

// Organizer Authentication Routes
Route::get('/organizer/login', [OrganizerAuthController::class, 'showLoginForm'])->name('organizer.login');
Route::post('/organizer/login', [OrganizerAuthController::class, 'login']);
Route::post('/organizer/logout', [OrganizerAuthController::class, 'logout'])->name('organizer.logout');

// Organizer Protected Routes
Route::middleware(['auth:organizer'])->group(function () {
    // Events
    Route::get('/organizer/events', [OrganizerEventController::class, 'index'])->name('organizer.events.index');
    Route::get('/organizer/events/create', [OrganizerEventController::class, 'create'])->name('organizer.events.create');
    Route::post('/organizer/events', [OrganizerEventController::class, 'store'])->name('organizer.events.store');
    Route::get('/organizer/events/{event}', [OrganizerEventController::class, 'show'])->name('organizer.events.show');
    Route::get('/organizer/events/{event}/edit', [OrganizerEventController::class, 'edit'])->name('organizer.events.edit');
    Route::put('/organizer/events/{event}', [OrganizerEventController::class, 'update'])->name('organizer.events.update');
    Route::delete('/organizer/events/{event}', [OrganizerEventController::class, 'destroy'])->name('organizer.events.destroy');
    
    // Channels
    Route::get('/organizer/events/{event}/channels/create', [\App\Http\Controllers\OrganizerChannelController::class, 'create'])->name('organizer.channels.create');
    Route::post('/organizer/events/{event}/channels', [\App\Http\Controllers\OrganizerChannelController::class, 'store'])->name('organizer.channels.store');
    
    // Rooms
    Route::get('/organizer/events/{event}/rooms/create', [\App\Http\Controllers\OrganizerRoomController::class, 'create'])->name('organizer.rooms.create');
    Route::post('/organizer/events/{event}/rooms', [\App\Http\Controllers\OrganizerRoomController::class, 'store'])->name('organizer.rooms.store');
    
    // Sessions
    Route::get('/organizer/events/{event}/sessions/create', [\App\Http\Controllers\OrganizerSessionController::class, 'create'])->name('organizer.sessions.create');
    Route::post('/organizer/events/{event}/sessions', [\App\Http\Controllers\OrganizerSessionController::class, 'store'])->name('organizer.sessions.store');
    Route::get('/organizer/events/{event}/sessions/{session}/edit', [\App\Http\Controllers\OrganizerSessionController::class, 'edit'])->name('organizer.sessions.edit');
    Route::put('/organizer/events/{event}/sessions/{session}', [\App\Http\Controllers\OrganizerSessionController::class, 'update'])->name('organizer.sessions.update');
    
    // Tickets
    Route::get('/organizer/events/{event}/tickets/create', [\App\Http\Controllers\OrganizerTicketController::class, 'create'])->name('organizer.tickets.create');
    Route::post('/organizer/events/{event}/tickets', [\App\Http\Controllers\OrganizerTicketController::class, 'store'])->name('organizer.tickets.store');
    
    // Reports
    Route::get('/organizer/events/{event}/reports/room-capacity', [\App\Http\Controllers\OrganizerReportController::class, 'roomCapacity'])->name('organizer.reports.room-capacity');
});

Route::get('/', function () {
    return redirect()->route('organizer.login');
});
