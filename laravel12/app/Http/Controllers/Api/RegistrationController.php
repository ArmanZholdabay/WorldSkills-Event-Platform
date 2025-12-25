<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\Organizer;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function register(Request $request, $organizerSlug, $eventSlug)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'User not logged in'], 401);
        }

        $attendee = Attendee::where('login_token', $token)->first();

        if (!$attendee) {
            return response()->json(['message' => 'User not logged in'], 401);
        }

        $organizer = Organizer::where('slug', $organizerSlug)->first();

        if (!$organizer) {
            return response()->json(['message' => 'Organizer not found'], 404);
        }

        $event = Event::where('organizer_id', $organizer->id)
            ->where('slug', $eventSlug)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if already registered
        $existingRegistration = Registration::where('attendee_id', $attendee->id)
            ->whereHas('ticket', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })
            ->first();

        if ($existingRegistration) {
            return response()->json(['message' => 'User already registered'], 401);
        }

        $request->validate([
            'ticket_id' => 'required|exists:event_tickets,id',
            'session_ids' => 'nullable|array',
            'session_ids.*' => 'exists:sessions,id',
        ]);

        $ticket = EventTicket::find($request->ticket_id);

        // Verify ticket belongs to event
        if ($ticket->event_id !== $event->id) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        // Check ticket availability
        $available = true;
        if ($ticket->special_validity) {
            if (isset($ticket->special_validity['type'])) {
                if ($ticket->special_validity['type'] === 'date') {
                    $date = \Carbon\Carbon::parse($ticket->special_validity['date']);
                    $available = now()->lte($date);
                } elseif ($ticket->special_validity['type'] === 'amount') {
                    $totalAmount = $ticket->special_validity['amount'];
                    $soldCount = $ticket->registrations()->count();
                    $available = $soldCount < $totalAmount;
                }
            }
        }

        if (!$available) {
            return response()->json(['message' => 'Ticket is no longer available'], 401);
        }

        DB::beginTransaction();
        try {
            $registration = Registration::create([
                'attendee_id' => $attendee->id,
                'ticket_id' => $ticket->id,
                'registration_time' => now(),
            ]);

            if ($request->has('session_ids') && is_array($request->session_ids)) {
                // Verify sessions belong to the event
                $eventSessionIds = $event->channels->flatMap(function ($channel) {
                    return $channel->rooms->flatMap(function ($room) {
                        return $room->sessions->pluck('id');
                    });
                })->toArray();

                $validSessionIds = array_intersect($request->session_ids, $eventSessionIds);

                if (!empty($validSessionIds)) {
                    $registration->sessions()->attach($validSessionIds);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Registration successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    public function index(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'User not logged in'], 401);
        }

        $attendee = Attendee::where('login_token', $token)->first();

        if (!$attendee) {
            return response()->json(['message' => 'User not logged in'], 401);
        }

        $registrations = Registration::where('attendee_id', $attendee->id)
            ->with([
                'ticket.event.organizer',
                'sessions:id'
            ])
            ->orderBy('id', 'asc')
            ->get();

        $formattedRegistrations = $registrations->map(function ($registration) {
            return [
                'event' => [
                    'id' => $registration->ticket->event->id,
                    'name' => $registration->ticket->event->name,
                    'slug' => $registration->ticket->event->slug,
                    'date' => $registration->ticket->event->date->format('Y-m-d'),
                    'organizer' => [
                        'id' => $registration->ticket->event->organizer->id,
                        'name' => $registration->ticket->event->organizer->name,
                        'slug' => $registration->ticket->event->organizer->slug,
                    ],
                ],
                'session_ids' => $registration->sessions->pluck('id')->toArray(),
            ];
        });

        return response()->json(['registrations' => $formattedRegistrations]);
    }
}
