<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerReportController extends Controller
{
    public function roomCapacity(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $event->load('channels.rooms.sessions');
        
        $roomData = [];
        foreach ($event->channels as $channel) {
            foreach ($channel->rooms as $room) {
                $registrationsCount = 0;
                foreach ($room->sessions as $session) {
                    // Count registrations for this session through session_registrations
                    $registrationsCount += \App\Models\SessionRegistration::where('session_id', $session->id)->count();
                }
                
                $roomData[] = [
                    'name' => $room->name,
                    'capacity' => $room->capacity,
                    'registrations' => $registrationsCount,
                    'percentage' => $room->capacity > 0 ? round(($registrationsCount / $room->capacity) * 100, 2) : 0,
                ];
            }
        }

        return view('organizer.reports.room-capacity', compact('event', 'organizer', 'roomData'));
    }
}
