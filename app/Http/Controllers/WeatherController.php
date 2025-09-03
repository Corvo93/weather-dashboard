<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\WeatherRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WeatherController extends Controller
{
    /**
     * Mostra la dashboard con form e lista città
     *
     * @return void
     */
    public function dashboard()
    {
        $cities = City::all();
        return view('dashboard', compact('cities'));
    }

    /**
     * Filtra i dati secondo città e intervallo date
     *
     * @param Request $request
     * @return void
     */
    public function showDashboard(Request $request)
    {

        if ($request->from > $request->to) {
            return redirect()->route('dashboard')->with('error', 'La data di inizio non può essere maggiore di quella finale.');
        }

        $city = City::findOrFail($request->city_id);

        $records = WeatherRecord::where('city_id', $city->id)
            ->whereBetween('timestamp', [$request->from, $request->to])
            ->orderBy('timestamp')
            ->get();

        if ($records->isEmpty()) {
            $records = null;
            $avg = $min = $max = null;
            return redirect()->route('dashboard')->with('error', 'Nessun dato disponibile per il range selezionato.');
        }

        $avg = $records->avg('temperature');
        $min = $records->min('temperature');
        $max = $records->max('temperature');

        $cities = City::all();

        return view('dashboard', compact('cities', 'records', 'city', 'avg', 'min', 'max', 'request'));
    }

    /**
     * Aggiunge una città e fa fetch dati se non presente
     *
     * @param Request $request
     * @return void
     */
    public function addCity(Request $request)
    {
        $cityName = ucfirst(strtolower($request->city));

        $geo = Http::get('https://geocoding-api.open-meteo.com/v1/search', [
            'name' => $cityName,
        ])->json();

        if (!isset($geo['results'][0])) {
            return redirect()->route('dashboard')->with('error', "Città '$cityName' non trovata.");
        }

        $city = City::firstOrCreate(
            ['name' => $cityName],
            ['country' => $geo['results'][0]['country'] ?? 'IT']
        );

        $this->fetchWeather($city);

        return redirect()->route('dashboard')->with('success', "Dati salvati per {$city->name}");
    }

    /**
     * Scarica i dati meteo storici da Open-Meteo per la città specificata
     * e li salva nel database. Se la città è già aggiornata, non fa nulla.
     * 
     * @param City $city
     * @return void
     */
    public function fetchWeather(City $city)
    {

        $geo = Http::get('https://geocoding-api.open-meteo.com/v1/search', [
            'name' => $city->name,
        ])->json();

        if (!isset($geo['results'][0])) {
            return "Città non trovata";
        }

        $lat = $geo['results'][0]['latitude'];
        $lon = $geo['results'][0]['longitude'];

        $lastRecord = WeatherRecord::where('city_id', $city->id)
            ->orderBy('timestamp', 'desc')
            ->first();

        $start_date = $lastRecord ? Carbon::parse($lastRecord->timestamp)->addDay()->format('Y-m-d') : '2025-08-01';
        $end_date = Carbon::now()->format('Y-m-d');

        if ($start_date > $end_date) {
            return "Dati già aggiornati fino a oggi.";
        }

        $weather = Http::get('https://archive-api.open-meteo.com/v1/archive', [
            'latitude' => $lat,
            'longitude' => $lon,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'hourly' => 'temperature_2m',
        ])->json();

        if (isset($weather['hourly']['time'])) {
            foreach ($weather['hourly']['time'] as $i => $timestamp) {
                $temp = $weather['hourly']['temperature_2m'][$i] ?? null;
                if ($temp === null) continue;

                WeatherRecord::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'timestamp' => $timestamp,
                    ],
                    ['temperature' => $temp]
                );
            }
        }
        return redirect()->route('dashboard')->with('success', "Dati aggiornati fino a {$end_date} per {$city->name}");
    }
}
