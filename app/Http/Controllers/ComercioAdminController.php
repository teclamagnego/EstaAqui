<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ComercioAdminController extends Controller
{
    /**
     * Lista artículos del comercio logueado.
     */
    public function index(): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();
        $articulos = $comercio->articulos()->with('imagenes')->orderBy('orden', 'asc')->get();

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
            'imagenes_file' => 'nullable|array',
            'imagenes_file.*' => 'image|max:10240',
            'orden' => 'nullable|integer',
        ]);

        $comercio = Auth::guard('comercio')->user();

        if ($request->hasFile('imagen_file')) {
            $file = $request->file('imagen_file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $file->storeAs("fotos/{$comercio->id}", $filename, 'public');
            $validated['imagen_url'] = url("storage/fotos/{$comercio->id}/{$filename}");
        }

        $articulo = $comercio->articulos()->create($validated);

        if ($request->hasFile('imagenes_file')) {
            foreach ($request->file('imagenes_file') as $file) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs("fotos/{$comercio->id}", $filename, 'public');
                $articulo->imagenes()->create([
                    'url' => url("storage/fotos/{$comercio->id}/{$filename}"),
                    'orden' => 0
                ]);
            }
        }

        return response()->json($articulo->load('imagenes'), 201);
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
            'imagenes_file' => 'nullable|array',
            'imagenes_file.*' => 'image|max:10240',
            'activo' => 'sometimes|boolean',
            'orden' => 'sometimes|integer',
        ]);

        if ($request->hasFile('imagen_file')) {
            $file = $request->file('imagen_file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $file->storeAs("fotos/{$comercio->id}", $filename, 'public');
            $validated['imagen_url'] = url("storage/fotos/{$comercio->id}/{$filename}");
        }

        $articulo->update($validated);

        if ($request->hasFile('imagenes_file')) {
            foreach ($request->file('imagenes_file') as $file) {
                $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs("fotos/{$comercio->id}", $filename, 'public');
                $articulo->imagenes()->create([
                    'url' => url("storage/fotos/{$comercio->id}/{$filename}"),
                    'orden' => 0
                ]);
            }
        }

        return response()->json($articulo->load('imagenes'));
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
            'logo_file' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs("fotos/{$comercio->id}", $filename, 'public');
            $validated['logo_url'] = url("storage/fotos/{$comercio->id}/{$filename}");
        }

        $comercio->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado',
            'comercio' => $comercio->fresh()->only('id', 'nombre', 'slug', 'email', 'zona_barrio', 'whatsapp', 'descripcion', 'direccion', 'categoria_comercio', 'logo_url'),
        ]);
    }

    /**
     * Descargar Excel de ejemplo para importar productos.
     */
    public function downloadExcelExample(): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        try {
            require_once app_path('Libraries/SimpleXLSXGen.php');
            $data = [
                ['nombre_producto', 'descripcion_articulo', 'precio_ars', 'categoria'],
                ['Hamburguesa Completa', 'Medallón 200g, cheddar, bacon, lechuga, tomate', '8500', 'Comida Rápida'],
                ['Papas Fritas Grandes', 'Porción para 2 personas', '3500', 'Guarniciones'],
            ];
            
            $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
            $filename = "ejemplo_productos.xlsx";
            $path = storage_path("app/public/{$filename}");
            $xlsx->saveAs($path);

            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generando archivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Importar Excel de productos.
     */
    public function importExcel(Request $request): JsonResponse
    {
        $request->validate([
            'excel_file' => 'required|file|max:10240',
        ]);

        $comercio = Auth::guard('comercio')->user();
        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($path, "r");
            if ($handle !== FALSE) {
                // intentar detectar delimitador
                $firstLine = fgets($handle);
                $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
                rewind($handle);
                
                while (($data = fgetcsv($handle, 4000, $delimiter)) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } elseif ($extension === 'xlsx') {
            require_once app_path('Libraries/SimpleXLSX.php');
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($path)) {
                $rows = $xlsx->rows();
            } else {
                return response()->json(['message' => \Shuchkin\SimpleXLSX::parseError()], 400);
            }
        } else {
            return response()->json(['message' => 'Por favor, subí un archivo .xlsx o .csv válido'], 400);
        }

        if (count($rows) < 2) {
            return response()->json(['message' => 'El archivo está vacío o no tiene la estructura correcta (requiere encabezados y al menos un producto)'], 400);
        }

        $header = array_map('strtolower', array_map('trim', array_shift($rows)));
        
        // Find indexes based on expected names
        $idx_nombre = array_search('nombre_producto', $header);
        $idx_desc = array_search('descripcion_articulo', $header);
        $idx_precio = array_search('precio_ars', $header);
        $idx_cat = array_search('categoria', $header);

        if ($idx_nombre === false || $idx_precio === false || $idx_cat === false) {
            return response()->json(['message' => 'Columnas faltantes. Asegurate de tener "nombre_producto", "precio_ars" y "categoria". Podés descargar el archivo de ejemplo para guiarte.'], 400);
        }

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $nombre = isset($row[$idx_nombre]) ? trim($row[$idx_nombre]) : '';
            $desc = ($idx_desc !== false && isset($row[$idx_desc])) ? trim($row[$idx_desc]) : null;
            $precio = isset($row[$idx_precio]) ? floatval(str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $row[$idx_precio]))) : 0;
            $categoria = isset($row[$idx_cat]) ? trim($row[$idx_cat]) : '';

            // Saltamos filas vacías
            if (empty($nombre) && empty($precio)) {
                 continue;
            }

            if (empty($nombre) || empty($precio) || empty($categoria)) {
                $errors[] = "Fila " . ($index + 2) . ": Faltan datos obligatorios (nombre, precio o categoría).";
                continue;
            }

            try {
                $comercio->articulos()->create([
                    'nombre_producto' => substr($nombre, 0, 255),
                    'descripcion_articulo' => $desc ? substr($desc, 0, 2000) : null,
                    'precio_ars' => $precio,
                    'categoria' => substr($categoria, 0, 255),
                    'activo' => true
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($index + 2) . ": Error al guardar en base de datos.";
            }
        }

        return response()->json([
            'message' => "Se importaron $imported productos exitosamente.",
            'errors' => $errors
        ]);
    }

    /**
     * Obtener informes de clicks para el comercio.
     */
    public function informes(): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();

        // Totales del comercio
        $total_views = \App\Models\ClickLog::where('comercio_id', $comercio->id)->where('tipo', 'vista_comercio')->count();
        $total_whatsapp = \App\Models\ClickLog::where('comercio_id', $comercio->id)->where('tipo', 'click_whatsapp_comercio')->count();

        // Clicks por artículo (whatsapp link)
        $articulos_clicks = \App\Models\ClickLog::with('articulo:id,nombre_producto')
            ->where([
                ['comercio_id', '=', $comercio->id],
                ['tipo', '=', 'click_whatsapp_articulo'],
            ])
            ->whereNotNull('articulo_id')
            ->selectRaw('articulo_id, count(*) as clicks')
            ->groupBy('articulo_id')
            ->orderByDesc('clicks')
            ->get();
        
        $articulos_stats = $articulos_clicks->map(function ($log) {
            return [
                'articulo_id' => $log->articulo_id,
                'nombre' => $log->articulo ? $log->articulo->nombre_producto : 'Desconocido',
                'clicks' => $log->clicks,
            ];
        });

        // Ultimos 50 clicks detallados
        $ultimos_clicks = \App\Models\ClickLog::with('articulo:id,nombre_producto')
            ->where('comercio_id', '=', $comercio->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                    'tipo' => $log->tipo,
                    'articulo' => $log->articulo ? $log->articulo->nombre_producto : null,
                    'ip' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                ];
            });

        return response()->json([
            'totales' => [
                'vistas_perfil' => $total_views,
                'clicks_whatsapp_perfil' => $total_whatsapp,
            ],
            'articulos' => $articulos_stats,
            'recientes' => $ultimos_clicks,
        ]);
    }

    /**
     * Eliminar una imagen secundaria.
     */
    public function deleteImagen(int $articuloId, int $imagenId): JsonResponse
    {
        $comercio = Auth::guard('comercio')->user();
        $articulo = $comercio->articulos()->findOrFail($articuloId);
        $imagen = $articulo->imagenes()->findOrFail($imagenId);
        
        // Opcional: eliminar el archivo de storage
        $path = str_replace(url('storage/'), '', $imagen->url);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);

        $imagen->delete();

        return response()->json(['message' => 'Imagen eliminada']);
    }
}
