<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Weather Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light p-4">

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h1 class="mb-4">🌤️ Weather Dashboard</h1>

    <!-- Form aggiungi città -->
    <form action="{{ route('add.city') }}" method="POST" class="mb-3 d-flex gap-2 flex-nowrap">
        @csrf
        <input type="text" name="city" placeholder="Inserisci una città" required class="form-control flex-grow-1" style="max-width: 200px;">
        <button type="submit" class="btn btn-primary">Aggiungi / Cerca</button>
    </form>

    <!-- Form filtra dati -->
    <form action="{{ route('show.dashboard') }}" method="POST" class="mb-4 d-flex gap-2 flex-nowrap">
        @csrf
        <select name="city_id" required class="form-select" style="max-width: 150px;">
            <option value="">Seleziona città</option>
            @foreach($cities as $c)
                <option value="{{ $c->id }}" {{ isset($city) && $city->id==$c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ $request->from ?? '' }}" class="form-control" style="max-width: 150px;">
        <input type="date" name="to" value="{{ $request->to ?? '' }}" class="form-control" style="max-width: 150px;">
        <button type="submit" class="btn btn-success">Filtra</button>
    </form>

    @isset($records)
    <!-- Box statistiche -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded text-center">
                <strong>Media</strong>
                <div>{{ number_format($avg,1) }}°C</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded text-center">
                <strong>Min</strong>
                <div>{{ $min }}°C</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white border rounded text-center">
                <strong>Max</strong>
                <div>{{ $max }}°C</div>
            </div>
        </div>
    </div>

    <!-- Grafico temperature -->
    <div class="mb-4 bg-white p-3 border rounded">
        <canvas id="tempChart"></canvas>
    </div>

    <!-- Tabella temperature -->
    <div class="bg-white p-3 border rounded">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data/Ora</th>
                    <th>Temperatura (°C)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $rec)
                    <tr>
                        <td>{{ $rec->timestamp }}</td>
                        <td>{{ $rec->temperature }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        const ctx = document.getElementById('tempChart').getContext('2d');
        const tempChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($records->pluck('timestamp')) !!},
                datasets: [{
                    label: 'Temperatura (°C)',
                    data: {!! json_encode($records->pluck('temperature')) !!},
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { x: { display: true }, y: { display: true } }
            }
        });
    </script>
    @endisset

</div>

</body>
</html>
