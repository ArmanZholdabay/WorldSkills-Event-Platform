<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerTicketController extends Controller
{
    public function create(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }
        return view('organizer.tickets.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if ($event->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'cost' => 'required|numeric|min:0',
            'special_validity' => 'nullable|in:amount,date',
            'amount' => 'required_if:special_validity,amount|integer|min:1',
            'valid_until' => 'required_if:special_validity,date|date',
        ]);

        $specialValidity = null;
        if ($request->special_validity === 'amount') {
            $specialValidity = [
                'type' => 'amount',
                'amount' => $validated['amount'],
            ];
        } elseif ($request->special_validity === 'date') {
            $specialValidity = [
                'type' => 'date',
                'date' => $validated['valid_until'],
            ];
        }

        EventTicket::create([
            'event_id' => $event->id,
            'name' => $validated['name'],
            'cost' => $validated['cost'],
            'special_validity' => $specialValidity ? json_encode($specialValidity) : null,
        ]);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Ticket successfully created');
    }
}
