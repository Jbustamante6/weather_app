<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;

class FavoritesController extends Controller
{
    public function agregarFavorito(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Obtener el usuario autenticado
            $usuario = Auth::user();

            // Crear un nuevo registro de ciudad favorita
            $favorite = new Favorite();
            $favorite->user_id = $usuario->id;
            $favorite->name = $request->name;
            $favorite->save();

            return response()->json([
                'message' => 'Ciudad añadida a favoritos exitosamente',
                'ciudad' => $favorite
            ], 201);
        } catch (QueryException $e) {
            // Manejo de errores de la base de datos
            \Log::error('Error en la base de datos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocurrió un problema al guardar la ciudad en la base de datos. Intente de nuevo más tarde.'
            ], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            \Log::error('Error inesperado: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocurrió un error inesperado. Intente nuevamente.'
            ], 500);
        }
    }

    public function listarFavoritos()
    {
        try {
            // Obtener el usuario autenticado
            $usuario = Auth::user();

            // Verificar si se obtuvo un usuario autenticado
            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no autenticado'
                ], 401);
            }

            // Obtener las ciudades favoritas del usuario
            $favoritos = Favorite::where('user_id', $usuario->id)->get();

            return response()->json([
                'favorites' => $favoritos
            ], 200);
        } catch (QueryException $e) {
            // Manejo de errores de la base de datos
            \Log::error('Error en la base de datos al listar favoritos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocurrió un problema al obtener los datos de la base de datos. Intente de nuevo más tarde.'
            ], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            \Log::error('Error inesperado al listar favoritos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocurrió un error inesperado. Intente nuevamente.'
            ], 500);
        }
    }

    public function eliminarFavorito($id)
    {
        try {
            // Obtener el usuario autenticado
            $usuario = Auth::user();

            // Buscar el registro de la ciudad favorita del usuario autenticado
            $favorite = Favorite::where('user_id', $usuario->id)->where('id', $id)->first();

            // Verificar si el favorito existe
            if (!$favorite) {
                return response()->json(['error' => 'Favorite not found or does not belong to the user'], 404);
            }

            // Eliminar el favorito
            $favorite->delete();

            return response()->json(['message' => 'Favorite deleted successfully'], 200);
        } catch (QueryException $e) {
            // Manejo de errores de la base de datos
            Log::error('Error en la base de datos al eliminar favorito: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete the favorite. Please try again later.'], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            Log::error('Error inesperado al eliminar favorito: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}
