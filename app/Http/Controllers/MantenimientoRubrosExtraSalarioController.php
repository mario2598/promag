<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoRubrosExtraSalarioController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantRubExtSal";

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
        return view('mant.rubrosextrasalario', compact('data'));
    }

    /**
     * Carga todos los rubros extra salariales
     */
    public function cargarRubrosAjax(Request $request)
    {
        try {
            $rubros = DB::table('rubro_extra_salario')
                ->where('estado', '=', 'A')
                ->orderBy('nombre', 'asc')
                ->get();

            return $this->responseAjaxSuccess("", $rubros);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error al cargar los rubros", []);
        }
    }

    /**
     * Guarda o actualiza un rubro extra salarial
     */
    public function guardarRubroAjax(Request $request)
    {
        try {
            $id = $request->input('id');
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            $multiplicador = $request->input('multiplicador');

            // Validaciones
            if (empty($nombre)) {
                return $this->responseAjaxServerError("El nombre es requerido", []);
            }

            if (empty($multiplicador) || $multiplicador <= 0) {
                return $this->responseAjaxServerError("El multiplicador debe ser mayor a 0", []);
            }

            DB::beginTransaction();

            if (empty($id) || $id == '-1') {
                // Crear nuevo rubro
                DB::table('rubro_extra_salario')->insertGetId([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'multiplicador' => $multiplicador,
                    'estado' => 'A',
                    'fecha_creacion' => date("Y-m-d H:i:s")
                ]);
            } else {
                // Actualizar rubro existente
                DB::table('rubro_extra_salario')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'multiplicador' => $multiplicador,
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
     * Elimina (desactiva) un rubro extra salarial
     */
    public function eliminarRubroAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            if (empty($id)) {
                return $this->responseAjaxServerError("ID no proporcionado", []);
            }

            DB::beginTransaction();

            $rubro = DB::table('rubro_extra_salario')->where('id', '=', $id)->first();
            if (!$rubro) {
                DB::rollBack();
                return $this->responseAjaxServerError("Rubro no encontrado", []);
            }

            DB::table('rubro_extra_salario')
                ->where('id', '=', $id)
                ->update(['estado' => 'I']);

            DB::commit();
            return $this->responseAjaxSuccess("Rubro eliminado correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al eliminar el rubro", []);
        }
    }
}

