<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Event Backend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const specialValiditySelect = document.getElementById('selectSpecialValidity');
            const amountDiv = document.getElementById('amountDiv');
            const dateDiv = document.getElementById('dateDiv');
            
            function toggleFields() {
                if (specialValiditySelect.value === 'amount') {
                    amountDiv.style.display = 'block';
                    dateDiv.style.display = 'none';
                } else if (specialValiditySelect.value === 'date') {
                    amountDiv.style.display = 'none';
                    dateDiv.style.display = 'block';
                } else {
                    amountDiv.style.display = 'none';
                    dateDiv.style.display = 'none';
                }
            }
            
            specialValiditySelect.addEventListener('change', toggleFields);
            toggleFields();
        });
    </script>
</head>
<body>
<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="{{ route('organizer.events.index') }}">Event Platform</a>
    <span class="navbar-organizer w-100">{{ Auth::guard('organizer')->user()->name }}</span>
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
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4" style="margin-top: 60px;">
            <div class="border-bottom mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h1 class="h2">{{ $event->name }}</h1>
                </div>
                <span class="h6">{{ $event->date ? $event->date->format('F j, Y') : 'No date' }}</span>
            </div>

            <div class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Create new ticket</h2>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('organizer.tickets.store', $event) }}">
                @csrf
                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputName">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="inputName" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputCost">Cost</label>
                        <input type="number" class="form-control @error('cost') is-invalid @enderror" id="inputCost" name="cost" value="{{ old('cost', 0) }}" min="0" step="0.01" required>
                        @error('cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="selectSpecialValidity">Special Validity</label>
                        <select class="form-control" id="selectSpecialValidity" name="special_validity">
                            <option value="" {{ old('special_validity') == '' ? 'selected' : '' }}>None</option>
                            <option value="amount" {{ old('special_validity') == 'amount' ? 'selected' : '' }}>Limited amount</option>
                            <option value="date" {{ old('special_validity') == 'date' ? 'selected' : '' }}>Purchaseable till date</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="amountDiv" style="display: none;">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputAmount">Maximum amount of tickets to be sold</label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="inputAmount" name="amount" value="{{ old('amount', 0) }}" min="1">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="dateDiv" style="display: none;">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputValidTill">Tickets can be sold until</label>
                        <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" id="inputValidTill" name="valid_until" value="{{ old('valid_until') }}">
                        @error('valid_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="mb-4">
                <button class="btn btn-primary" type="submit">Save ticket</button>
                <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-link">Cancel</a>
            </form>
        </main>
    </div>
</div>
</body>
</html>

