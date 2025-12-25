<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerRoomController extends Controller
{
    public function create(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }
        $channels = $event->channels;
        return view('organizer.rooms.create', compact('event', 'channels'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'channel_id' => 'required|exists:channels,id',
            'capacity' => 'required|integer|min:1',
        ]);

        // Verify channel belongs to event
        $channel = Channel::find($validated['channel_id']);
        if ($channel->event_id !== $event->id) {
            abort(403);
        }

        Room::create([
            'channel_id' => $validated['channel_id'],
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
        ]);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Room successfully created');
    }
}
