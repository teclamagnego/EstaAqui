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
        $query = Articulo::with('comercio:id,nombre,slug,whatsapp,zona_barrio,logo_url')
            ->activo()
            ->whereHas('comercio', fn($q) => $q->activo());

        // Búsqueda por texto (server-side LIKE para complementar fuzzy del cliente)
        if ($search = $request->input('q')) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre_producto', 'like', $term)
                  ->orWhere('descripcion_articulo', 'like', $term)
                  ->orWhere('categoria', 'like', $term);
            });
        }

        // Filtro por categoría
        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        // Filtro por comercio
        if ($comercioId = $request->input('comercio')) {
            $query->where('comercio_id', $comercioId);
        }

        $articulos = $query->orderBy('created_at', 'desc')->paginate(20);

        // Agregar whatsapp_link a cada artículo
        $articulos->getCollection()->transform(function ($articulo) {
            $articulo->whatsapp_link = $articulo->whatsapp_link;
            return $articulo;
        });

        return response()->json($articulos);
    }

    /**
     * Lista comercios activos.
     */
    public function comercios(): JsonResponse
    {
        $comercios = Comercio::activo()
            ->withCount(['articulos' => fn($q) => $q->activo()])
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
            ->activo()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($articulo) {
                $articulo->whatsapp_link = $articulo->whatsapp_link;
                return $articulo;
            });

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
}
