<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;


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

    public function listarCiudades(Request $request)
    {
        try {
            // Ruta del archivo JSON
            $filePath = public_path('documents/city.list.json');

            // Verificar si el archivo existe
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            // Cargar y decodificar el archivo JSON
            $data = json_decode(file_get_contents($filePath), true);

            // Verificar si la decodificación fue exitosa
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar el archivo JSON');
            }

            // Parámetro de filtro opcional
            $pais = 'CO';
            $nombreCiudad = strtolower($request->input('q', ''));

            // Filtrar las ciudades por país y nombre
            $ciudadesFiltradas = array_filter($data, function ($ciudad) use ($pais, $nombreCiudad) {
                $coincidePais = $ciudad['country'] === $pais;
                $coincideNombre = empty($nombreCiudad) || stripos($ciudad['name'], $nombreCiudad) !== false;
                return $coincidePais && $coincideNombre;
            });

            // Convertir el array filtrado a una lista indexada correctamente
            $ciudadesFiltradas = array_values($ciudadesFiltradas);

            // Ordenar las ciudades en orden alfabético por nombre
            usort($ciudadesFiltradas, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            // Verificar si hay resultados filtrados
            if (count($ciudadesFiltradas) === 0) {
                return response()->json(['message' => 'No se encontraron resultados'], 200);
            }

            // Paginación
            $pagina = max((int)$request->input('page', 1), 1); // Asegura que la página sea al menos 1
            $porPagina = 20; // Número de resultados por página
            $totalResultados = count($ciudadesFiltradas);
            $inicio = ($pagina - 1) * $porPagina;

            // Verificar si la página solicitada está fuera de rango
            if ($inicio >= $totalResultados) {
                return response()->json(['message' => 'Página fuera de rango'], 200);
            }

            // Obtener la porción paginada de los resultados
            $ciudadesPaginadas = array_slice($ciudadesFiltradas, $inicio, $porPagina);

            // Crear la instancia de LengthAwarePaginator para la respuesta
            $paginacion = new \Illuminate\Pagination\LengthAwarePaginator(
                $ciudadesPaginadas,
                $totalResultados,
                $porPagina,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json($paginacion, 200);
        } catch (Exception $e) {
            Log::error('Error al listar ciudades: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al procesar la solicitud. Intente de nuevo más tarde.'], 500);
        }
    }



}
