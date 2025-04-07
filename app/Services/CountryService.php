<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CountryService
{
    protected $baseUrl = 'https://restcountries.com/v3.1';

    public function getAllCountries()
    {
        $response = Http::get("{$this->baseUrl}/all");
        return $response->json();
    }

    public function getCountryByName($name)
    {
        $response = Http::get("{$this->baseUrl}/name/{$name}");
        return $response->json();
    }

    public function getCountryByCode($code)
    {
        $response = Http::get("{$this->baseUrl}/alpha/{$code}");
        return $response->json();
    }

    public function getCountriesByRegion($region)
    {
        $response = Http::get("{$this->baseUrl}/region/{$region}");
        return $response->json();
    }
}
