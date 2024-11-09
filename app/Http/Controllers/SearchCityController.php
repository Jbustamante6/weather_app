<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;

class SearchCityController extends Controller
{
    public function searchCities(Request $request){

        $validator = Validator::make($request->all(), [
            'q' => 'required|min:3',
        ]);
    
        if ($validator->fails()) {
            // Retorna una respuesta JSON con el error sin redirigir
            return response()->json(['error' => 'El campo "q" es obligatorio y debe tener al menos 3 caracteres.'], 422);
        }
    
        
        
        try {
            $response = Cache::remember("city-".$request['q'], 1800, function () use ($request) {
                $client = new \GuzzleHttp\Client();
                $query = $request['q'];
                $weather_token = env('WEATHER_API_TOKEN');
                $endpoint = "https://pro.openweathermap.org/data/2.5/forecast?q=$query&lang=es&appid=$weather_token";
                $request = $client->get($endpoint, []);
                $response = $request->getBody();
                return json_decode($response);
            });
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json(['error' => 'API Error'], 500);
        }
    
        return $response;
    }
}
