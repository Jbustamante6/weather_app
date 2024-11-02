<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

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
    
        $client = new \GuzzleHttp\Client();
        $query = $request['q'];
        $weather_token = env('WEATHER_API_TOKEN');
        $endpoint = "https://pro.openweathermap.org/data/2.5/forecast?q=$query&lang=es&appid=$weather_token";
        
        try {
            $request = $client->get($endpoint, []);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json(['error' => 'API Error'], 500);
        }
    
        $response = $request->getBody();
        return json_decode($response);
    }
}
