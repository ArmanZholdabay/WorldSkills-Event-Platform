<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Event Backend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="{{ route('organizer.events.index') }}">Event Platform</a>
    <span class="navbar-organizer w-100">{{ $organizer->name }}</span>
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <form method="POST" action="{{ route('organizer.logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-white border-0 p-0">Sign out</button>
            </form>
        </li>
    </ul>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="{{ route('organizer.events.index') }}">Manage Events</a></li>
                </ul>
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>{{ $event->name }}</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="{{ route('organizer.events.show', $event) }}">Overview</a></li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Reports</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('organizer.reports.room-capacity', $event) }}">Room capacity</a></li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4" style="margin-top: 60px;">
            <div class="border-bottom mb-3 pt-3 pb-2 event-title">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h1 class="h2">{{ $event->name }}</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-sm btn-outline-secondary">Edit event</a>
                        </div>
                    </div>
                </div>
                <span class="h6">{{ $event->date ? $event->date->format('F j, Y') : 'No date' }}</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Tickets -->
            <div id="tickets" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Tickets</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="{{ route('organizer.tickets.create', $event) }}" class="btn btn-sm btn-outline-secondary">Create new ticket</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row tickets">
                @forelse($event->tickets as $ticket)
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">{{ $ticket->name }}</h5>
                                <p class="card-text">{{ number_format($ticket->cost, 2) }}.-</p>
                                <p class="card-text">
                                    @if($ticket->special_validity)
                                        @if(isset($ticket->special_validity['type']) && $ticket->special_validity['type'] === 'date')
                                            Available until {{ \Carbon\Carbon::parse($ticket->special_validity['date'])->format('F j, Y') }}
                                        @elseif(isset($ticket->special_validity['type']) && $ticket->special_validity['type'] === 'amount')
                                            {{ $ticket->special_validity['amount'] }} tickets available
                                        @endif
                                    @else
                                        &nbsp;
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted">No tickets yet.</p>
                    </div>
                @endforelse
            </div>

            <!-- Sessions -->
            <div id="sessions" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Sessions</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="{{ route('organizer.sessions.create', $event) }}" class="btn btn-sm btn-outline-secondary">Create new session</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive sessions">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th class="w-100">Title</th>
                        <th>Speaker</th>
                        <th>Channel</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($event->channels as $channel)
                        @foreach($channel->rooms as $room)
                            @foreach($room->sessions as $session)
                                <tr>
                                    <td class="text-nowrap">{{ $session->start->format('H:i') }} - {{ $session->end->format('H:i') }}</td>
                                    <td>{{ ucfirst($session->type) }}</td>
                                    <td><a href="{{ route('organizer.sessions.edit', [$event, $session]) }}">{{ $session->title }}</a></td>
                                    <td class="text-nowrap">{{ $session->speaker ?? 'N/A' }}</td>
                                    <td class="text-nowrap">{{ $channel->name }} / {{ $room->name }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Channels -->
            <div id="channels" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Channels</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="{{ route('organizer.channels.create', $event) }}" class="btn btn-sm btn-outline-secondary">Create new channel</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row channels">
                @foreach($event->channels as $channel)
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">{{ $channel->name }}</h5>
                                <p class="card-text">
                                    {{ $channel->rooms->sum(fn($room) => $room->sessions->count()) }} sessions,
                                    {{ $channel->rooms->count() }} room(s)
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Rooms -->
            <div id="rooms" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Rooms</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="{{ route('organizer.rooms.create', $event) }}" class="btn btn-sm btn-outline-secondary">Create new room</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive rooms">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Capacity</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($event->channels as $channel)
                        @foreach($channel->rooms as $room)
                            <tr>
                                <td>{{ $room->name }}</td>
                                <td>{{ number_format($room->capacity) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</div>
</body>
</html>

