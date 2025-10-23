<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoMonedasController extends Controller
{
    use SpaceUtil;
    protected $SpaceSeg;
    public $codigo_pantalla = "mantMonedas";

    public function __construct() {}
    
    public function index()
    {
        return view('mant.monedas');
    }

    /**
     * Cargar monedas para DataTables
     */
    public function cargarMonedasAjax(Request $request)
    {
        try {
            $monedas = DB::table('sis_moneda as m')
                ->leftJoin('sis_estado as e', 'm.estado', '=', 'e.id')
                ->select(
                    'm.id',
                    'm.codigo',
                    'm.descripcion',
                    'm.tipo_cambio',
                    'm.fecha_creacion',
                    'e.nombre as estado_nombre'
                )
                ->orderBy('m.codigo', 'asc')
                ->get();

            return response()->json([
                'data' => $monedas,
                'recordsTotal' => $monedas->count(),
                'recordsFiltered' => $monedas->count()
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Error al cargar las monedas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar moneda (crear o actualizar)
     */
    public function guardarMonedaAjax(Request $request)
    {
        try {
            $id = $request->input('id');
            $codigo = strtoupper(trim($request->input('codigo')));
            $descripcion = trim($request->input('descripcion'));
            $tipo_cambio = $request->input('tipo_cambio');

            // Validaciones
            if (empty($codigo) || empty($descripcion)) {
                return $this->responseAjaxServerError("El código y la descripción son obligatorios.", []);
            }

            if ($tipo_cambio <= 0) {
                return $this->responseAjaxServerError("El tipo de cambio debe ser mayor a 0.", []);
            }

            DB::beginTransaction();

            if ($id > 0) {
                // Actualizar moneda existente
                $monedaExistente = DB::table('sis_moneda')->where('id', $id)->first();
                if (!$monedaExistente) {
                    return $this->responseAjaxServerError("La moneda no existe.", []);
                }

                // Verificar que el código no esté en uso por otra moneda
                $codigoEnUso = DB::table('sis_moneda')
                    ->where('codigo', $codigo)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($codigoEnUso) {
                    return $this->responseAjaxServerError("El código de moneda ya está en uso.", []);
                }

                DB::table('sis_moneda')
                    ->where('id', $id)
                    ->update([
                        'codigo' => $codigo,
                        'descripcion' => $descripcion,
                        'tipo_cambio' => $tipo_cambio,
                        'fecha_modificacion' => now()
                    ]);

                $mensaje = "Moneda actualizada correctamente.";

            } else {
                // Crear nueva moneda
                $codigoEnUso = DB::table('sis_moneda')->where('codigo', $codigo)->exists();
                if ($codigoEnUso) {
                    return $this->responseAjaxServerError("El código de moneda ya está en uso.", []);
                }

                DB::table('sis_moneda')->insert([
                    'codigo' => $codigo,
                    'descripcion' => $descripcion,
                    'tipo_cambio' => $tipo_cambio,
                    'estado' => 1, // Activo
                    'fecha_creacion' => now()
                ]);

                $mensaje = "Moneda creada correctamente.";
            }

            DB::commit();

            return $this->responseAjaxSuccess($mensaje, []);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar la moneda: " . $e->getMessage(), []);
        }
    }

    /**
     * Eliminar moneda
     */
    public function eliminarMonedaAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            $moneda = DB::table('sis_moneda')->where('id', $id)->first();
            if (!$moneda) {
                return $this->responseAjaxServerError("La moneda no existe.", []);
            }

            // No permitir eliminar la moneda CRC (principal)
            if ($moneda->codigo === 'CRC') {
                return $this->responseAjaxServerError("No se puede eliminar la moneda CRC (principal).", []);
            }

            // Verificar si la moneda está siendo usada en CxP
            $enUso = DB::table('cxp')->where('moneda', $moneda->codigo)->exists();
            if ($enUso) {
                return $this->responseAjaxServerError("No se puede eliminar la moneda porque está siendo utilizada en Cuentas por Pagar.", []);
            }

            DB::beginTransaction();

            DB::table('sis_moneda')->where('id', $id)->delete();

            DB::commit();

            return $this->responseAjaxSuccess("Moneda eliminada correctamente.", []);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al eliminar la moneda: " . $e->getMessage(), []);
        }
    }

    /**
     * Obtener datos de una moneda para edición
     */
    public function obtenerMonedaAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            $moneda = DB::table('sis_moneda')
                ->where('id', $id)
                ->first();

            if (!$moneda) {
                return $this->responseAjaxServerError("La moneda no existe.", []);
            }

            return $this->responseAjaxSuccess("Moneda obtenida correctamente.", $moneda);

        } catch (QueryException $e) {
            return $this->responseAjaxServerError("Error al obtener la moneda: " . $e->getMessage(), []);
        }
    }

    /**
     * Obtener monedas activas para formularios
     */
    public static function getMonedasActivas()
    {
        try {
            return DB::table('sis_moneda')
                ->where('estado', 1)
                ->orderBy('codigo', 'asc')
                ->get();
        } catch (QueryException $e) {
            return collect();
        }
    }
}
