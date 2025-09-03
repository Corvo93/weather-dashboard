# Weather History Dashboard

## Descrizione
Applicazione Laravel che permette di cercare una città, scaricare i dati storici delle temperature orarie tramite le API di Open-Meteo e visualizzarli in forma tabellare, aggregata e grafica. Il progetto permette di inserire un qualsiasi numero di città e gestisce quelle non presenti nel database mostrando un messaggio di errore se non vengono trovate.

---

## Funzionalità implementate
- Ricerca città tramite API Open-Meteo Geocoding  
- Salvataggio città nel database  
- Download dati storici temperatura (Archive API)  
- Salvataggio dati storici nel database  
- Frontend con tabella dati e box statistiche (media, min, max)  
- Grafico delle temperature giornaliere tramite Chart.js  
- Gestione errori per città non trovate  
- Controllo dei range di date (es. date future o inizio > fine)  

---

## Struttura del codice
Il progetto è stato organizzato secondo le best practice Laravel:  
- **Controller:** `WeatherController` gestisce tutte le operazioni tra frontend, database e API esterne. Metodi utilizzati:
  - `dashboard` -> mostra la vista principale con form e lista delle città salvate;
  - `addCity` -> valida input, interroga la Geocoding API e salva la città se valida;
  - `fetchWeather` -> recupera lat/lon, scarica i dati da Archive API e li salva nel DB;
  - `showDashboard` -> valida input, filtra i record in base a città e range di date e calcola statistiche;            
- **Model e relazioni:** `City` e `WeatherRecord` con relazioni Eloquent (uno a molti).  
- **View Blade:** `dashboard.blade.php` per visualizzare i dati e gestire i form.  
- **Service interno:** chiamate HTTP verso le API esterne e logica di fetch/storaggio dei dati.  

---

## Scelte tecniche
- **Gestione errori API:** quando la città non viene trovata, viene mostrato un alert all’utente e non viene inserita nel database.  
- **Validazione form:** controllo dei range di date e gestione di input futuri o incongruenti.  
- **Persistenza dati:** utilizzo di `updateOrCreate` per aggiornare le temperature senza creare duplicati.  
- **Frontend:** Bootstrap 5 per layout reattivo e Chart.js per visualizzazione grafica dei dati.  
- **Ottimizzazioni:** minimizzazione delle chiamate API e validazione dei dati prima del salvataggio.
