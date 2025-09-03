<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherRecord extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'timestamp', 'temperature'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}