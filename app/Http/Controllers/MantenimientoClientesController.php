<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoClientesController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantCli";

    public function __construct() {}
    public function index()
    {
        return view('mant.clientes');
    }

    /**
     * Guarda o actualiza un Cliente.
     */
    public function guardarCliente(Request $request)
    {
        $validar = $this->validarCliente($request);
        if (!$validar['estado']) {
            return $this->responseAjaxServerError($validar['mensaje'], []);
        }

        $correo = $request->input('mdl_generico_ipt_correo');
        $nombre_completo = $request->input('mdl_generico_ipt_nombre');
        $id = $request->input('mdl_generico_ipt_id');

        try {
            DB::beginTransaction();
            
            if ($id == '-1' || $id == null || $this->isEmpty($id)) {
                // Crear nuevo cliente
                $idCliente = DB::table('cliente')->insertGetId([
                    'nombre_completo' => $nombre_completo,
                    'correo' => $correo,
                    'estado' => SisEstadoController::getIdEstadoByCodGeneral("USU_ACT"),
                    'fecha_registro' => date("Y-m-d H:i:s")
                ]);
                
                DB::commit();
                return $this->responseAjaxSuccess("El cliente se guardó correctamente.", $idCliente);
            } else {
                // Actualizar cliente existente
                $clienteAux = DB::table('cliente')->where('id', '=', $id)->get()->first();
                if ($clienteAux == null) {
                    DB::rollBack();
                    return $this->responseAjaxServerError("El cliente no existe.", []);
                }

                DB::table('cliente')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre_completo' => $nombre_completo,
                        'correo' => $correo
                    ]);
                
                DB::commit();
                return $this->responseAjaxSuccess("El cliente se actualizó correctamente.", $id);
            }
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar el cliente: " . $ex->getMessage(), []);
        }
    }

    /**
     * Elimina un Cliente.
     */
    public function eliminarCliente(Request $request)
    {
        $id = $request->input('cliente_id') ?? $request->input('idGenericoEliminar');

        if ($id == null || $id == '' || $id < 1) {
            return $this->responseAjaxServerError('Identificador inválido.', []);
        }

        try {
            DB::beginTransaction();
            $cliente = DB::table('cliente')->where('id', '=', $id)->get()->first();
            if ($cliente == null) {
                return $this->responseAjaxServerError('No existe el cliente a eliminar.', []);
            } else {
                // Marcar cliente como inactivo
                DB::table('cliente')
                    ->where('id', '=', $id)
                    ->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("USU_INACTIVO")]);
            }
            DB::commit();
            return $this->responseAjaxSuccess('El cliente se eliminó correctamente.', []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Ocurrió un error eliminando el cliente.', []);
        }
    }

    public function validarCliente(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('mdl_generico_ipt_nombre')) || $this->isEmpty($r->input('mdl_generico_ipt_nombre'))) {
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        /*if($this->isNull($r->input('mdl_generico_ipt_tel')) || $this->isEmpty($r->input('mdl_generico_ipt_tel'))){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= "Teléfono ";
            $valido = false;
            $esPrimero = false;
        }*/

        $requeridos .= "] ";
        if (!$valido) {
            return $this->responseAjaxServerError("Campos Requeridos: " . $requeridos, []);
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_nombre'), 500)) {
            return $this->responseAjaxServerError("El nombre completo del cliente es de máximo 500 caracteres.", []);
        }

        if (!$this->isNull($r->input('mdl_generico_ipt_correo')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_correo'), 100)) {
            return $this->responseAjaxServerError("El correo es de máximo 100 caracteres.", []);
        }
        // Validar que el correo no exista (si se proporciona)
        $correo = $r->input('mdl_generico_ipt_correo');
        if (!$this->isNull($correo) && !$this->isEmpty($correo)) {
            $clienteExistente = DB::table('cliente')
                ->join('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
                ->where('cliente.correo', $correo)
                ->where('sis_estado.cod_general', '=', 'USU_ACT');

            // Si es edición, excluir el cliente actual
            $id = $r->input('mdl_generico_ipt_id');
            if ($id != '-1' && !$this->isEmpty($id)) {
                $clienteExistente->where('cliente.id', '!=', $id);
            }

            if ($clienteExistente->exists()) {
                return $this->responseAjaxServerError("El correo electrónico ya está registrado en el sistema.", []);
            }
        }

        return $this->responseAjaxSuccess("", []);
    }

    /**
     * Obtiene los clientes para DataTables con paginación AJAX
     */
    public function obtenerClientesAjax(Request $request)
    {
        try {
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $searchValue = $request->input('search.value');
            $orderColumn = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');

            // Columnas de la tabla
            $columns = ['nombre_completo', 'correo', 'codigo_actividad'];

            // Query base
            $query = DB::table('cliente')
                ->leftJoin('cliente_fe_info', 'cliente.id', '=', 'cliente_fe_info.cliente_id')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
                ->where('sis_estado.cod_general', '=', 'USU_ACT')
                ->select(
                    'cliente.id',
                    'cliente.nombre_completo',
                    'cliente.correo',
                    'cliente_fe_info.codigo_actividad',
                    'cliente_fe_info.tipo_identificacion',
                    'cliente_fe_info.identificacion as cedula',
                    'cliente_fe_info.direccion as ubicacion'
                );

            // Aplicar búsqueda
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('cliente.nombre_completo', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente.correo', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente_fe_info.identificacion', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente_fe_info.direccion', 'like', '%' . $searchValue . '%');
                });
            }

            // Contar total de registros
            $totalRecords = DB::table('cliente')
                ->join('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
                ->where('sis_estado.cod_general', '=', 'USU_ACT')
                ->count();

            // Contar registros filtrados
            $filteredRecords = $query->count();

            // Aplicar ordenamiento
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            } else {
                $query->orderBy('cliente.nombre_completo', 'asc');
            }

            // Aplicar paginación
            $clientes = $query->skip($start)->take($length)->get();

            // Formatear datos para DataTables
            $data = [];
            foreach ($clientes as $cliente) {
                $data[] = [
                    'nombre_completo' => $cliente->nombre_completo ?? '',
                    'cedula' => $cliente->cedula ?? '-',
                    'telefono' => '-',
                    'correo' => $cliente->correo ?? '',
                    'ubicacion' => $cliente->ubicacion ?? '-',
                    'fe_configurado' => $cliente->codigo_actividad ? 'Sí' : 'No',
                    'fe_badge' => $cliente->codigo_actividad ?
                        '<span class="badge badge-success">Sí</span>' :
                        '<span class="badge badge-warning">No</span>',
                    'acciones' => $cliente->id
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos'
            ]);
        }
    }

    /**
     * Obtiene un cliente específico por ID
     */
    public function obtenerCliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        try {
            $cliente = DB::table('cliente')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
                ->where('cliente.id', $clienteId)
                ->where('sis_estado.cod_general', '=', 'USU_ACT')
                ->select('cliente.*')
                ->first();

            if (!$cliente) {
                return $this->responseAjaxServerError('Cliente no encontrado', []);
            }

            $cliente->info_fe = DB::table('cliente_fe_info')
                ->where('cliente_id', $clienteId)
                ->first();

            return $this->responseAjaxSuccess('Cliente obtenido correctamente', $cliente);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Error al obtener el cliente', []);
        }
    }

    /**
     * Obtiene la información de facturación electrónica de un cliente
     */
    public function obtenerInfoFECliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        try {
            $infoFE = DB::table('cliente_fe_info')
                ->join('cliente', 'cliente_fe_info.cliente_id', '=', 'cliente.id')
                ->where('cliente_id', $clienteId)
                ->select(
                    'cliente_fe_info.*',
                    'cliente.correo'
                )
                ->first();

            return $this->responseAjaxSuccess('Información FE obtenida correctamente', $infoFE);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Error al obtener la información de facturación electrónica', []);
        }
    }

    /**
     * Guarda o actualiza la información de facturación electrónica de un cliente
     */
    public function guardarInfoFECliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $codigoActividad = $request->input('codigo_actividad');
        $tipoIdentificacion = $request->input('tipo_identificacion');
        $nombreComercial = $request->input('nombre_comercial');
        $direccion = $request->input('direccion');

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        // Validar que el cliente existe
        $cliente = DB::table('cliente')
            ->join('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
            ->where('cliente.id', $clienteId)
            ->where('sis_estado.cod_general', '=', 'USU_ACT')
            ->select('cliente.*')
            ->first();
            
        if (!$cliente) {
            return $this->responseAjaxServerError('Cliente no encontrado', []);
        }

        try {
            DB::beginTransaction();

            // Verificar si ya existe información FE para este cliente
            $infoExistente = DB::table('cliente_fe_info')
                ->where('cliente_id', $clienteId)
                ->first();

            $dataFE = [
                'cliente_id' => $clienteId,
                'codigo_actividad' => $codigoActividad ?: '722003',
                'tipo_identificacion' => $tipoIdentificacion ?: '01',
                'nombre_comercial' => $nombreComercial,
                'direccion' => $direccion,
                'fecha_modificacion' => now()
            ];

            if ($infoExistente) {
                // Actualizar información existente
                DB::table('cliente_fe_info')
                    ->where('cliente_id', $clienteId)
                    ->update($dataFE);
            } else {
                // Crear nueva información
                $dataFE['fecha_creacion'] = now();
                DB::table('cliente_fe_info')->insertGetId($dataFE);
            }

            DB::commit();

            return $this->responseAjaxSuccess('Información de facturación electrónica guardada correctamente', []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Error al guardar la información de facturación electrónica', []);
        }
    }
}
