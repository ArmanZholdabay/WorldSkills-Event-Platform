<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function show($organizerSlug, $eventSlug)
    {
        $organizer = Organizer::where('slug', $organizerSlug)->first();

        if (!$organizer) {
            return response()->json(['message' => 'Organizer not found'], 404);
        }

        $event = Event::where('organizer_id', $organizer->id)
            ->where('slug', $eventSlug)
            ->with([
                'channels.rooms.sessions' => function ($query) {
                    $query->orderBy('start', 'asc');
                },
                'tickets'
            ])
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Format response
        $channels = $event->channels->map(function ($channel) {
            return [
                'id' => $channel->id,
                'name' => $channel->name,
                'rooms' => $channel->rooms->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'sessions' => $room->sessions->map(function ($session) {
                            return [
                                'id' => $session->id,
                                'title' => $session->title,
                                'description' => $session->description,
                                'speaker' => $session->speaker,
                                'start' => $session->start->format('Y-m-d H:i:s'),
                                'end' => $session->end->format('Y-m-d H:i:s'),
                                'type' => $session->type,
                                'cost' => $session->cost,
                            ];
                        }),
                    ];
                }),
            ];
        });

        $tickets = $event->tickets->map(function ($ticket) {
            $description = null;
            $available = true;

            if ($ticket->special_validity) {
                if (isset($ticket->special_validity['type'])) {
                    if ($ticket->special_validity['type'] === 'date') {
                        $date = \Carbon\Carbon::parse($ticket->special_validity['date']);
                        $description = 'Available until ' . $date->format('F j, Y');
                        $available = now()->lte($date);
                    } elseif ($ticket->special_validity['type'] === 'amount') {
                        $totalAmount = $ticket->special_validity['amount'];
                        $soldCount = $ticket->registrations()->count();
                        $description = $totalAmount . ' tickets available';
                        $available = $soldCount < $totalAmount;
                    }
                }
            }

            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'description' => $description,
                'cost' => (float) $ticket->cost,
                'available' => $available,
            ];
        });

        return response()->json([
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'date' => $event->date->format('Y-m-d'),
            'channels' => $channels,
            'tickets' => $tickets,
        ]);
    }
}
