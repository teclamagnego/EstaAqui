<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Comercio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Lista artículos con búsqueda y filtros.
     */
    public function articulos(Request $request): JsonResponse
    {
        $query = Articulo::with(['comercio:id,nombre,slug,whatsapp,zona_barrio,logo_url', 'imagenes'])
            ->activo()
            ->whereHas('comercio', fn($q) => $q->activo());

        // Búsqueda por texto (server-side LIKE para complementar fuzzy del cliente)
        if ($search = $request->input('q')) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('articulos.nombre_producto', 'like', $term)
                  ->orWhere('articulos.descripcion_articulo', 'like', $term)
                  ->orWhere('articulos.categoria', 'like', $term);
            });
        }

        // Filtro por categoría
        if ($categoria = $request->input('categoria')) {
            $query->where('articulos.categoria', '=', $categoria);
        }

        // Filtro por comercio
        if ($comercioId = $request->input('comercio')) {
            $query->where('articulos.comercio_id', '=', $comercioId);
        }

        $articulos = $query->join('comercios', 'articulos.comercio_id', '=', 'comercios.id')
            ->select('articulos.*')
            ->orderBy('comercios.orden', 'asc')
            ->orderBy('articulos.orden', 'asc')
            ->paginate(20);

        // Agregar whatsapp_link a cada artículo
        $articulos->getCollection()->each(fn($a) => $a->append('whatsapp_link'));

        return response()->json($articulos);
    }

    /**
     * Lista comercios activos.
     */
    public function comercios(): JsonResponse
    {
        $comercios = Comercio::activo()
            ->withCount(['articulos' => fn($q) => $q->activo()])
            ->orderBy('orden', 'asc')
            ->orderBy('nombre')
            ->get();

        return response()->json($comercios);
    }

    /**
     * Detalle de un comercio por slug + sus artículos.
     */
    public function comercioDetalle(string $slug): JsonResponse
    {
        $comercio = Comercio::activo()
            ->where('slug', $slug)
            ->firstOrFail();

        $articulos = $comercio->articulos()
            ->with('imagenes')
            ->activo()
            ->orderBy('orden', 'asc')
            ->get()
            ->each(fn($a) => $a->append('whatsapp_link'));

        return response()->json([
            'comercio' => $comercio,
            'articulos' => $articulos,
        ]);
    }

    /**
     * Lista categorías.
     */
    public function categorias(): JsonResponse
    {
        return response()->json(Categoria::orderBy('nombre')->get());
    }

    /**
     * Registra clicks para estadísticas.
     */
    public function trackClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:50',
            'comercio_id' => 'required|integer|exists:comercios,id',
            'articulo_id' => 'nullable|integer|exists:articulos,id',
        ]);

        \App\Models\ClickLog::create([
            'tipo' => $validated['tipo'],
            'comercio_id' => $validated['comercio_id'],
            'articulo_id' => $validated['articulo_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 1000),
            'city' => null, // Optional if we had CF-IPCountry header
        ]);

        return response()->json(['success' => true]);
    }
}
