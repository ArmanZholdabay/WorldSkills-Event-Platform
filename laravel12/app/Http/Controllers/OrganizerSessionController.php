<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerSessionController extends Controller
{
    public function create(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }
        
        $rooms = $event->channels->flatMap->rooms;
        return view('organizer.sessions.create', compact('event', 'rooms'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'speaker' => 'nullable|string|max:45',
            'room_id' => 'required|exists:rooms,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'type' => 'required|in:talk,workshop',
            'cost' => 'nullable|numeric|min:0',
        ]);

        // Verify room belongs to event
        $room = Room::find($validated['room_id']);
        if ($room->channel->event_id !== $event->id) {
            abort(403);
        }

        Session::create($validated);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Session successfully created');
    }

    public function edit(Event $event, Session $session)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        // Verify session belongs to event
        if ($session->room->channel->event_id !== $event->id) {
            abort(403);
        }

        $rooms = $event->channels->flatMap->rooms;
        return view('organizer.sessions.edit', compact('event', 'session', 'rooms'));
    }

    public function update(Request $request, Event $event, Session $session)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        if ($session->room->channel->event_id !== $event->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'speaker' => 'nullable|string|max:45',
            'room_id' => 'required|exists:rooms,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'type' => 'required|in:talk,workshop',
            'cost' => 'nullable|numeric|min:0',
        ]);

        // Verify room belongs to event
        $room = Room::find($validated['room_id']);
        if ($room->channel->event_id !== $event->id) {
            abort(403);
        }

        $session->update($validated);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Session successfully updated');
    }
}
