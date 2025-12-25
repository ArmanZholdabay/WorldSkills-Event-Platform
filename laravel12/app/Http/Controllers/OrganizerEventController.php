<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrganizerEventController extends Controller
{
    public function index()
    {
        $organizer = Auth::guard('organizer')->user();
        $events = Event::where('organizer_id', $organizer->id)
            ->withCount('registrations')
            ->orderBy('date', 'asc')
            ->get();

        return view('organizer.events.index', compact('events', 'organizer'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        $organizer = Auth::guard('organizer')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'slug' => [
                'required',
                'string',
                'max:45',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('events')->where('organizer_id', $organizer->id),
            ],
            'date' => 'required|date',
        ], [
            'slug.regex' => 'Slug must not be empty and only contain a-z, 0-9 and \'-\'.',
            'slug.unique' => 'Slug is already used.',
        ]);

        $event = Event::create([
            'organizer_id' => $organizer->id,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'date' => $validated['date'],
        ]);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event successfully created');
    }

    public function show(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();

        // Ensure event belongs to organizer
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $event->load([
            'channels.rooms.sessions',
            'tickets',
            'registrations' => function ($query) {
                $query->orderBy('id', 'asc');
            }
        ]);

        $registrationsCount = $event->registrations()->count();

        return view('organizer.events.show', compact('event', 'organizer', 'registrationsCount'));
    }

    public function edit(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();

        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();

        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'slug' => [
                'required',
                'string',
                'max:45',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('events')->where('organizer_id', $organizer->id)->ignore($event->id),
            ],
            'date' => 'required|date',
        ], [
            'slug.regex' => 'Slug must not be empty and only contain a-z, 0-9 and \'-\'.',
            'slug.unique' => 'Slug is already used.',
        ]);

        $event->update($validated);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event successfully updated');
    }

    public function destroy(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();

        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event successfully deleted');
    }
}
