<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerChannelController extends Controller
{
    public function create(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }
        return view('organizer.channels.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:45',
        ]);

        Channel::create([
            'event_id' => $event->id,
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Channel successfully created');
    }
}
