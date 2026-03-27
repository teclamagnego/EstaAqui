<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ComercioAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:comercios,email',
            'password' => 'required|string|min:6|confirmed',
            'whatsapp' => 'required|string|max:20',
            'zona_barrio' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'categoria_comercio' => 'nullable|string|max:255',
        ]);

        $comercio = Comercio::create($validated);

        Auth::guard('comercio')->login($comercio);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Comercio registrado exitosamente',
            'comercio' => $comercio->only('id', 'nombre', 'slug', 'email', 'zona_barrio'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $comercio = Comercio::where('email', $request->email)->first();

        if (!$comercio || !Hash::check($request->password, $comercio->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!$comercio->activo) {
            throw ValidationException::withMessages([
                'email' => ['Este comercio se encuentra suspendido. Por favor, contacte con el administrador.'],
            ]);
        }

        Auth::guard('comercio')->login($comercio);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login exitoso',
            'comercio' => $comercio->only('id', 'nombre', 'slug', 'email', 'zona_barrio'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('comercio')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me(Request $request): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();

        if (!$comercio) {
            return response()->json(['comercio' => null]);
        }

        return response()->json([
            'comercio' => $comercio->only('id', 'nombre', 'slug', 'email', 'zona_barrio', 'whatsapp', 'descripcion', 'direccion', 'categoria_comercio', 'logo_url'),
        ]);
    }
}
