<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index()
    {
        $countries = $this->countryService->getAllCountries();
        return response()->json($countries);
    }

    public function showByName($name)
    {
        $country = $this->countryService->getCountryByName($name);
        return response()->json($country);
    }

    public function showByCode($code)
    {
        $country = $this->countryService->getCountryByCode($code);
        return response()->json($country);
    }

    public function getByRegion($region)
    {
        $countries = $this->countryService->getCountriesByRegion($region);
        return response()->json($countries);
    }
}
