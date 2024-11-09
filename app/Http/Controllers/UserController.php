<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Creación del usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Retornar respuesta o token (según sea necesario)
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (QueryException $e) {
            // Manejo de errores de la base de datos
            Log::error('Error en la base de datos al registrar usuario: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un problema al guardar los datos en la base de datos. Intente de nuevo más tarde.'], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            Log::error('Error inesperado al registrar usuario: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error inesperado. Intente nuevamente.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validación de las credenciales
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);

            // Credenciales del usuario
            $credentials = $request->only('email', 'password');

            // Intentar autenticar y generar un token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Retornar el token si la autenticación fue exitosa
            return response()->json(['token' => $token], 200);

        } catch (JWTException $e) {
            // Error al intentar crear el token
            Log::error('Error al generar el token JWT: ' . $e->getMessage());
            return response()->json(['error' => 'Could not create token'], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            Log::error('Error inesperado en el proceso de inicio de sesión: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    // Método opcional para cerrar sesión y desactivar el token
    public function logout()
    {
        try {
            // Intentar invalidar el token JWT
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            // Error al intentar invalidar el token
            Log::error('Error al intentar cerrar sesión (JWTException): ' . $e->getMessage());
            return response()->json(['error' => 'Failed to log out, please try again.'], 500);
        } catch (Exception $e) {
            // Manejo de otras excepciones generales
            Log::error('Error inesperado al cerrar sesión: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}
