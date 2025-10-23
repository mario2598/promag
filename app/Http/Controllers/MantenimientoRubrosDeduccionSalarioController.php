<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoRubrosDeduccionSalarioController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantRubDedSal";

    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mant.rubrosdeduccionsalario', compact('data'));
    }

    /**
     * Carga todos los rubros de deducción salarial
     */
    public function cargarRubrosAjax(Request $request)
    {
        try {
            $rubros = DB::table('rubro_deduccion_salario')
                ->select('rubro_deduccion_salario.*')
                ->orderBy('rubro_deduccion_salario.nombre', 'asc')
                ->get();

            return $this->responseAjaxSuccess("", $rubros);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error al cargar los rubros", []);
        }
    }

    /**
     * Guarda o actualiza un rubro de deducción salarial
     */
    public function guardarRubroAjax(Request $request)
    {
        try {
            $id = $request->input('id');
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            $porcentaje = $request->input('porcentaje_deduccion');

            // Validaciones
            if (empty($nombre)) {
                return $this->responseAjaxServerError("El nombre es requerido", []);
            }

            if (empty($porcentaje) || $porcentaje < 0 || $porcentaje > 100) {
                return $this->responseAjaxServerError("El porcentaje debe estar entre 0 y 100", []);
            }

            DB::beginTransaction();

            if (empty($id) || $id == '-1') {
                // Crear nuevo rubro
                DB::table('rubro_deduccion_salario')->insertGetId([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'porcentaje_deduccion' => $porcentaje,
                    'estado' => 1, // Estado activo
                    'fecha_creacion' => date("Y-m-d H:i:s")
                ]);
            } else {
                // Actualizar rubro existente
                DB::table('rubro_deduccion_salario')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'porcentaje_deduccion' => $porcentaje,
                        'fecha_modificacion' => date("Y-m-d H:i:s")
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("Rubro guardado correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar el rubro: " . $ex->getMessage(), []);
        }
    }

    /**
     * Elimina (desactiva) un rubro de deducción salarial
     */
    public function eliminarRubroAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            if (empty($id)) {
                return $this->responseAjaxServerError("ID no proporcionado", []);
            }

            DB::beginTransaction();

            $rubro = DB::table('rubro_deduccion_salario')->where('id', '=', $id)->first();
            if (!$rubro) {
                DB::rollBack();
                return $this->responseAjaxServerError("Rubro no encontrado", []);
            }

            // Eliminar directamente el rubro
            DB::table('rubro_deduccion_salario')
                ->where('id', '=', $id)
                ->delete();

            DB::commit();
            return $this->responseAjaxSuccess("Rubro eliminado correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al eliminar el rubro", []);
        }
    }
}
