<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;

class FavoritesController extends Controller
{
    public function agregarFavorito(Request $request)
    {
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
    }

    public function listarFavoritos()
    {
        // Obtener el usuario autenticado
        $usuario = Auth::user();

        // Obtener las ciudades favoritas del usuario
        $favoritos = Favorite::where('user_id', $usuario->id)->get();

        return response()->json([
            'favorites' => $favoritos
        ], 200);
    }
}
