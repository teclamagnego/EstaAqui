<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComercioAdminController extends Controller
{
    /**
     * Lista artículos del comercio logueado.
     */
    public function index(): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();
        $articulos = $comercio->articulos()->orderBy('created_at', 'desc')->get();

        return response()->json($articulos);
    }

    /**
     * Crear nuevo artículo.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'descripcion_articulo' => 'nullable|string|max:2000',
            'precio_ars' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:255',
            'imagen_url' => 'nullable|url|max:500',
            'imagen_file' => 'nullable|image|max:10240',
        ]);

        $comercio = Auth::guard('comercio')->user();

        if ($request->hasFile('imagen_file')) {
            $file = $request->file('imagen_file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $file->storeAs("fotos/{$comercio->id}", $filename, 'local');
            $validated['imagen_url'] = url("fotos/{$comercio->id}/{$filename}");
        }

        $articulo = $comercio->articulos()->create($validated);

        return response()->json($articulo, 201);
    }

    /**
     * Actualizar artículo propio.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();
        $articulo = $comercio->articulos()->findOrFail($id);

        $validated = $request->validate([
            'nombre_producto' => 'sometimes|required|string|max:255',
            'descripcion_articulo' => 'nullable|string|max:2000',
            'precio_ars' => 'sometimes|required|numeric|min:0',
            'categoria' => 'sometimes|required|string|max:255',
            'imagen_url' => 'nullable|url|max:500',
            'imagen_file' => 'nullable|image|max:10240',
            'activo' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('imagen_file')) {
            $file = $request->file('imagen_file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $file->storeAs("fotos/{$comercio->id}", $filename, 'local');
            $validated['imagen_url'] = url("fotos/{$comercio->id}/{$filename}");
        }

        $articulo->update($validated);

        return response()->json($articulo);
    }

    /**
     * Eliminar artículo propio.
     */
    public function destroy(int $id): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();
        $articulo = $comercio->articulos()->findOrFail($id);
        $articulo->delete();

        return response()->json(['message' => 'Artículo eliminado']);
    }

    /**
     * Actualizar perfil del comercio.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'whatsapp' => 'sometimes|required|string|max:20',
            'zona_barrio' => 'sometimes|required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'categoria_comercio' => 'nullable|string|max:255',
            'logo_url' => 'nullable|url|max:500',
        ]);

        $comercio->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado',
            'comercio' => $comercio->fresh()->only('id', 'nombre', 'slug', 'email', 'zona_barrio', 'whatsapp', 'descripcion', 'direccion', 'categoria_comercio', 'logo_url'),
        ]);
    }
}
