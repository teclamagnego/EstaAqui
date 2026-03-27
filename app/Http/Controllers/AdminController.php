<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use App\Models\Articulo;
use App\Models\ClickLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['user' => Auth::guard('web')->user()]);
        }

        return response()->json([
            'errors' => ['email' => ['Credenciales incorrectas']]
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me(): JsonResponse
    {
        if (Auth::guard('web')->check()) {
            return response()->json(['user' => Auth::guard('web')->user()]);
        }
        return response()->json(['message' => 'No session'], 401);
    }

    // -- Dashboard methods --

    public function comercios(): JsonResponse
    {
        // Get all comercios along with statistics
        $comercios = Comercio::withCount('articulos')
            ->withCount(['clickLogs as total_clicks'])
            ->withCount(['clickLogs as whatsapp_clicks' => function($query) {
                $query->whereIn('tipo', ['click_whatsapp_comercio', 'click_whatsapp_articulo']);
            }])
            ->orderBy('id', 'desc')
            ->get();
            
        // Add created_at formatted "fechas de ingreso" implies seeing their creation date
        $comercios->transform(function($c) {
            $c->fecha_ingreso = $c->created_at->format('Y-m-d H:i');
            return $c;
        });

        return response()->json($comercios);
    }

    public function toggleComercioStatus(int $id): JsonResponse
    {
        $comercio = Comercio::findOrFail($id);
        $comercio->activo = !$comercio->activo;
        $comercio->save();
        return response()->json(['message' => 'Estado cambiado', 'activo' => $comercio->activo]);
    }

    public function deleteComercio(int $id): JsonResponse
    {
        $comercio = Comercio::findOrFail($id);
        $comercio->delete();
        return response()->json(['message' => 'Comercio eliminado']);
    }

    public function resetComercioPassword(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|min:6',
        ]);
        $comercio = Comercio::findOrFail($id);
        $comercio->password = Hash::make($validated['password']);
        $comercio->save();
        return response()->json(['message' => 'Contraseña reseteada']);
    }

    public function comercioInformes(int $id): JsonResponse
    {
        $comercio = Comercio::findOrFail($id);

        $total_views = ClickLog::where([['comercio_id', '=', $comercio->id], ['tipo', '=', 'vista_comercio']])->count();
        $total_whatsapp = ClickLog::where([['comercio_id', '=', $comercio->id], ['tipo', '=', 'click_whatsapp_comercio']])->count();

        $articulos_clicks = ClickLog::with('articulo:id,nombre_producto')
            ->where([
                ['comercio_id', '=', $comercio->id],
                ['tipo', '=', 'click_whatsapp_articulo'],
            ])
            ->whereNotNull('articulo_id')
            ->selectRaw('articulo_id, count(*) as clicks')
            ->groupBy('articulo_id')
            ->orderByDesc('clicks')
            ->get()
            ->map(function ($log) {
                return [
                    'articulo_id' => $log->articulo_id,
                    'nombre' => $log->articulo ? $log->articulo->nombre_producto : 'Desconocido',
                    'clicks' => $log->clicks,
                ];
            });

        $ultimos_clicks = ClickLog::with('articulo:id,nombre_producto')
            ->where('comercio_id', '=', $comercio->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
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
            'articulos' => $articulos_clicks,
            'recientes' => $ultimos_clicks,
        ]);
    }

    public function updateOrden(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'orden' => 'required|integer',
        ]);
        $comercio = Comercio::findOrFail($id);
        $comercio->orden = $validated['orden'];
        $comercio->save();
        return response()->json(['message' => 'Orden actualizado']);
    }

    public function comercioArticulos(int $id): JsonResponse
    {
        $comercio = Comercio::findOrFail($id);
        $articulos = $comercio->articulos()->orderBy('orden', 'asc')->get();
        return response()->json($articulos);
    }

    public function getSettings(): JsonResponse
    {
        return response()->json([
            'app_icon' => Setting::get('app_icon', '/icon.png'),
            'app_name' => Setting::get('app_name', 'EstaAqui'),
        ]);
    }

    public function updateIcon(Request $request): JsonResponse
    {
        $request->validate([
            'icon' => 'required|image|max:5120',
        ]);

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'app_icon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public', $filename);
            $url = url('storage/' . $filename);
            
            Setting::set('app_icon', $url);
            
            return response()->json(['url' => $url]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:50',
        ]);

        if (isset($validated['app_name'])) {
            Setting::set('app_name', $validated['app_name']);
        }
        
        return response()->json(['message' => 'Configuración actualizada']);
    }
}
