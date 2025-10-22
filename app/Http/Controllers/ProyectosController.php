<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class ProyectosController extends Controller
{
    use SpaceUtil;

    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
       
        return view('proyectos.proyectos');
    }

    /**
     * Vista de Proyectos Asignados (proyectos donde el usuario está involucrado)
     */
    public function proyectosAsignados()
    {
         return view('proyectos.proyectos_asignados');
    }

    /**
     * Vista de Autorización de Horas
     */
    public function autorizarHoras()
    {
        return view('proyectos.autorizar_horas');
    }

    /**
     * Carga las líneas de presupuesto de un proyecto
     */
    public function cargarLineasPresupuestoAjax(Request $request)
    {
        try {
            $proyectoId = $request->input('proyecto_id');

            $lineas = DB::table('proyecto_linea_presupuesto')
                ->where('proyecto', '=', $proyectoId)
                ->where('estado', '=', 'A')
                ->orderBy('numero_linea', 'asc')
                ->get();

            return $this->responseAjaxSuccess("", $lineas);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando líneas de presupuesto", []);
        }
    }

    /**
     * Guarda o actualiza una línea de presupuesto
     */
    public function guardarLineaPresupuestoAjax(Request $request)
    {
        try {
            $id = $request->input('id');
            $proyectoId = $request->input('proyecto_id');
            $numeroLinea = $request->input('numero_linea');
            $descripcion = $request->input('descripcion');
            $montoAutorizado = $request->input('monto_autorizado', 0);

            // Validaciones
            if (empty($proyectoId) || empty($numeroLinea) || empty($descripcion)) {
                return $this->responseAjaxServerError("Todos los campos son requeridos", []);
            }

            if ($montoAutorizado < 0) {
                return $this->responseAjaxServerError("El monto autorizado debe ser mayor o igual a 0", []);
            }

            // Verificar que el proyecto esté activo
            $proyecto = DB::table('proyecto')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->where('proyecto.id', '=', $proyectoId)
                ->select('proyecto.*', 'sis_estado.cod_general as estado_codigo')
                ->first();

            if (!$proyecto) {
                return $this->responseAjaxServerError("Proyecto no encontrado", []);
            }

            if ($proyecto->estado_codigo != 'PROY_ACTIVO') {
                return $this->responseAjaxServerError("No se pueden modificar líneas de presupuesto en proyectos que no están Activos", []);
            }

            DB::beginTransaction();

            if (empty($id) || $id == '0' || $id == '-1') {
                // Crear nueva línea
                DB::table('proyecto_linea_presupuesto')->insertGetId([
                    'proyecto' => $proyectoId,
                    'numero_linea' => $numeroLinea,
                    'descripcion' => $descripcion,
                    'monto_autorizado' => $montoAutorizado,
                    'monto_consumido' => 0,
                    'estado' => 'A',
                    'fecha_creacion' => date("Y-m-d H:i:s")
                ]);
            } else {
                // Actualizar línea existente
                DB::table('proyecto_linea_presupuesto')
                    ->where('id', '=', $id)
                    ->update([
                        'numero_linea' => $numeroLinea,
                        'descripcion' => $descripcion,
                        'monto_autorizado' => $montoAutorizado,
                        'fecha_modificacion' => date("Y-m-d H:i:s")
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("Línea de presupuesto guardada correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar línea de presupuesto: " . $ex->getMessage(), []);
        }
    }

    /**
     * Elimina (desactiva) una línea de presupuesto
     */
    public function eliminarLineaPresupuestoAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            if (empty($id)) {
                return $this->responseAjaxServerError("ID no proporcionado", []);
            }

            DB::beginTransaction();

            // Verificar que la línea no tenga consumo
            $linea = DB::table('proyecto_linea_presupuesto')->where('id', '=', $id)->first();
            if (!$linea) {
                DB::rollBack();
                return $this->responseAjaxServerError("Línea no encontrada", []);
            }

            if ($linea->monto_consumido > 0) {
                DB::rollBack();
                return $this->responseAjaxServerError("No se puede eliminar una línea con consumo registrado", []);
            }

            // Verificar que el proyecto esté activo
            $proyecto = DB::table('proyecto')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->where('proyecto.id', '=', $linea->proyecto)
                ->select('proyecto.*', 'sis_estado.cod_general as estado_codigo')
                ->first();

            if ($proyecto && $proyecto->estado_codigo != 'PROY_ACTIVO') {
                DB::rollBack();
                return $this->responseAjaxServerError("No se pueden eliminar líneas de presupuesto en proyectos que no están Activos", []);
            }

            DB::table('proyecto_linea_presupuesto')
                ->where('id', '=', $id)
                ->update(['estado' => 'I']);

            DB::commit();
            return $this->responseAjaxSuccess("Línea de presupuesto eliminada correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al eliminar línea de presupuesto", []);
        }
    }

    /**
     * Carga todos los proyectos con información relacionada
     */
    public function cargarProyectosAjax()
    {
        try {
            $proyectos = DB::table('proyecto')
                ->join('cliente', 'cliente.id', '=', 'proyecto.cliente')
                ->join('usuario', 'usuario.id', '=', 'proyecto.usuario_encargado')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->select(
                    'proyecto.*',
                    'cliente.nombre_completo as cliente_nombre',
                    DB::raw("CONCAT(usuario.nombre, ' ', usuario.ape1) as encargado_nombre"),
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo'
                )
                ->orderBy('proyecto.id', 'desc')
                ->get();

            return $this->responseAjaxSuccess("", $proyectos);
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'ProyectosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando los proyectos", []);
        }
    }

    /**
     * Carga un proyecto específico
     */
    public function cargarProyectoAjax(Request $request)
    {
        try {
            $id = $request->input('idProyecto');
            if ($id < 1) {
                return $this->responseAjaxSuccess("", []);
            }

            $proyecto = DB::table('proyecto')
                ->where('proyecto.id', '=', $id)
                ->get()->first();

            if ($proyecto == null) {
                return $this->responseAjaxServerError("No se encontró el proyecto", []);
            }

            // Cargar usuarios asignados
            $usuarios = DB::table('proyecto_usuario')
                ->join('usuario', 'usuario.id', '=', 'proyecto_usuario.usuario')
                ->join('rol', 'rol.id', '=', 'usuario.rol')
                ->where('proyecto_usuario.proyecto', '=', $id)
                ->select(
                    'usuario.id', 
                    DB::raw("CONCAT(usuario.nombre, ' ', usuario.ape1, ' ', COALESCE(usuario.ape2, '')) as nombre_completo"),
                    'rol.rol as rol_nombre',
                    'usuario.precio_hora'
                )
                ->get();

            $proyecto->usuarios_asignados = $usuarios;

            return $this->responseAjaxSuccess("", $proyecto);
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'ProyectosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando el proyecto", []);
        }
    }

    /**
     * Guarda o actualiza un proyecto
     */
    public function guardarProyectoAjax(Request $request)
    {
        $proyectoR = $request->input('proyecto');
        $id = $proyectoR['id'];
        $actualizar = ($id > 0);

        try {
            DB::beginTransaction();

            // Si es actualización, verificar que el proyecto esté activo
            if ($actualizar) {
                $proyectoActual = DB::table('proyecto')
                    ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                    ->where('proyecto.id', '=', $id)
                    ->select('proyecto.*', 'sis_estado.cod_general as estado_codigo')
                    ->first();

                if ($proyectoActual && $proyectoActual->estado_codigo != 'PROY_ACTIVO') {
                    DB::rollBack();
                    return $this->responseAjaxServerError("No se puede modificar un proyecto que no está en estado Activo", []);
                }
            }

            $datosProyecto = [
                'cliente' => $proyectoR['cliente'],
                'nombre' => $proyectoR['nombre'],
                'descripcion' => $proyectoR['descripcion'] ?? '',
                'usuario_encargado' => $proyectoR['usuario_encargado'],
                'ubicacion' => $proyectoR['ubicacion'] ?? '',
                'estado' => isset($proyectoR['estado']) && !empty($proyectoR['estado']) 
                    ? $proyectoR['estado'] 
                    : SisEstadoController::getIdEstadoByCodGeneral("PROY_ACTIVO")
            ];

            if ($actualizar) {
                // Actualizar proyecto existente
                DB::table('proyecto')
                    ->where('id', '=', $id)
                    ->update($datosProyecto);
            } else {
                // Crear nuevo proyecto
                $datosProyecto['fecha_creacion'] = date("Y-m-d H:i:s");
                $id = DB::table('proyecto')->insertGetId($datosProyecto);
            }

            // Actualizar usuarios asignados
            if (isset($proyectoR['usuarios_asignados']) && count($proyectoR['usuarios_asignados']) > 0) {
                // Eliminar asignaciones anteriores
                DB::table('proyecto_usuario')
                    ->where('proyecto', '=', $id)
                    ->delete();

                // Insertar nuevas asignaciones
                foreach ($proyectoR['usuarios_asignados'] as $usuarioId) {
                    DB::table('proyecto_usuario')->insertGetId([
                        'proyecto' => $id,
                        'usuario' => $usuarioId,
                        'fecha_asignacion' => date("Y-m-d H:i:s")
                    ]);
                }
            }

            DB::commit();
            return $this->responseAjaxSuccess("Proyecto guardado correctamente", $id);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar el proyecto: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene los clientes activos
     */
    public function cargarClientesAjax()
    {
        try {
            $clientes = DB::table('cliente')
                ->join('sis_estado', 'sis_estado.id', '=', 'cliente.estado')
                ->where('sis_estado.cod_general', '=', 'USU_ACT')
                ->select('cliente.id', 'cliente.nombre_completo as nombre')
                ->get();

            return $this->responseAjaxSuccess("", $clientes);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando clientes", []);
        }
    }

    /**
     * Obtiene los usuarios activos con su rol y precio por hora
     */
    public function cargarUsuariosActivosAjax()
    {
        try {
            $usuarios = DB::table('usuario')
                ->join('sis_estado', 'sis_estado.id', '=', 'usuario.estado')
                ->join('rol', 'rol.id', '=', 'usuario.rol')
                ->where('sis_estado.cod_general', '=', 'USU_ACT')
                ->select(
                    'usuario.id', 
                    DB::raw("CONCAT(usuario.nombre, ' ', usuario.ape1, ' ', COALESCE(usuario.ape2, '')) as nombre_completo"),
                    'rol.rol as rol_nombre',
                    'usuario.precio_hora'
                )
                ->orderBy('usuario.nombre', 'asc')
                ->get();

            return $this->responseAjaxSuccess("", $usuarios);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando usuarios", []);
        }
    }

    /**
     * Obtiene los estados de proyectos
     */
    public function cargarEstadosProyectoAjax()
    {
        try {
            $estados = DB::table('sis_estado')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_estado.clase')
                ->where('sis_clase.cod_general', '=', 'EST_PROYECTOS')
                ->select('sis_estado.id', 'sis_estado.nombre', 'sis_estado.cod_general')
                ->orderBy('sis_estado.id', 'asc')
                ->get();

            return $this->responseAjaxSuccess("", $estados);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando estados", []);
        }
    }

    /**
     * Carga los proyectos donde el usuario actual está involucrado
     * (como encargado o como parte del equipo)
     */
    public function cargarProyectosAsignadosAjax()
    {
        try {
            $usuarioId = $this->getUsuarioAuth()['id'];

            // Obtener IDs de proyectos donde el usuario es encargado
            $proyectosEncargado = DB::table('proyecto')
                ->where('usuario_encargado', '=', $usuarioId)
                ->pluck('id');

            // Combinar ambos arrays y eliminar duplicados
            $proyectosIds = $proyectosEncargado->unique()->values();

            if ($proyectosIds->isEmpty()) {
                return $this->responseAjaxSuccess("", []);
            }

            // Obtener información completa de los proyectos
            $proyectos = DB::table('proyecto')
                ->join('cliente', 'cliente.id', '=', 'proyecto.cliente')
                ->join('usuario', 'usuario.id', '=', 'proyecto.usuario_encargado')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->whereIn('proyecto.id', $proyectosIds)
                ->select(
                    'proyecto.*',
                    'cliente.nombre_completo as cliente_nombre',
                    DB::raw("CONCAT(usuario.nombre, ' ', usuario.ape1) as encargado_nombre"),
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo'
                )
                ->orderBy('proyecto.id', 'desc')
                ->get();

            // Agregar información de si es encargado o parte del equipo
            foreach ($proyectos as $proyecto) {
                $proyecto->es_encargado = ($proyecto->usuario_encargado == $usuarioId);
                
                // Contar usuarios asignados
                $proyecto->total_usuarios = DB::table('proyecto_usuario')
                    ->where('proyecto', '=', $proyecto->id)
                    ->count();
            }

            return $this->responseAjaxSuccess("", $proyectos);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando proyectos asignados", []);
        }
    }

    /**
     * Recalcula el monto consumido de una línea de presupuesto
     * basado en las bitácoras aprobadas
     */
    private function recalcularMontoConsumidoLinea($lineaPresupuestoId)
    {
        try {
            // Obtener la línea de presupuesto
            $linea = DB::table('proyecto_linea_presupuesto')->where('id', '=', $lineaPresupuestoId)->first();
            if (!$linea) {
                return;
            }

            // Obtener ID del estado aprobado
            $estadoAprobado = DB::table('sis_estado')
                ->where('cod_general', '=', 'BIT_PROY_APROBADA')
                ->first();

            if (!$estadoAprobado) {
                return;
            }

            // Calcular el total consumido de las bitácoras aprobadas
            $bitacoras = DB::table('bit_usuario_proyecto')
                ->leftJoin('usuario', 'usuario.id', '=', 'bit_usuario_proyecto.usuario')
                ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                ->where('bit_usuario_proyecto.linea_presupuesto', '=', $lineaPresupuestoId)
                ->where('bit_usuario_proyecto.estado', '=', $estadoAprobado->id)
                ->select(
                    'bit_usuario_proyecto.*',
                    'usuario.precio_hora',
                    'rubro_extra_salario.multiplicador'
                )
                ->get();

            $totalConsumido = 0;
            foreach ($bitacoras as $bitacora) {
                // Calcular horas trabajadas
                $horaEntrada = strtotime($bitacora->hora_entrada);
                $horaSalida = strtotime($bitacora->hora_salida);
                $horas = ($horaSalida - $horaEntrada) / 3600;

                // Calcular costo con multiplicador
                $precioHora = $bitacora->precio_hora ?? 0;
                $multiplicador = $bitacora->multiplicador ?? 1.00;
                $costo = $horas * $precioHora * $multiplicador;

                $totalConsumido += $costo;
            }

            // Actualizar monto consumido de la línea
            DB::table('proyecto_linea_presupuesto')
                ->where('id', '=', $lineaPresupuestoId)
                ->update(['monto_consumido' => $totalConsumido]);

        } catch (QueryException $ex) {
            // Log error but don't fail the main operation
        }
    }

    /**
     * Carga los rubros extra salariales activos
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
            return $this->responseAjaxServerError("Error cargando rubros", []);
        }
    }

    /**
     * Carga la bitácora de un usuario en un proyecto
     */
    public function cargarBitacoraUsuarioAjax(Request $request)
    {
        try {
            $proyectoId = $request->input('proyecto_id');
            $usuarioId = $request->input('usuario_id');

            $bitacoras = DB::table('bit_usuario_proyecto')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'bit_usuario_proyecto.estado')
                ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                ->leftJoin('proyecto_linea_presupuesto', 'proyecto_linea_presupuesto.id', '=', 'bit_usuario_proyecto.linea_presupuesto')
                ->where('bit_usuario_proyecto.proyecto', '=', $proyectoId)
                ->where('bit_usuario_proyecto.usuario', '=', $usuarioId)
                ->select(
                    'bit_usuario_proyecto.*',
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo',
                    'rubro_extra_salario.nombre as rubro_nombre',
                    'rubro_extra_salario.multiplicador as rubro_multiplicador',
                    'proyecto_linea_presupuesto.numero_linea as linea_numero',
                    'proyecto_linea_presupuesto.descripcion as linea_descripcion'
                )
                ->orderBy('bit_usuario_proyecto.fecha', 'desc')
                ->get();

            return $this->responseAjaxSuccess("", $bitacoras);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando bitácora: " . $ex->getMessage(), []);
        }
    }

    /**
     * Guarda o actualiza un registro de bitácora
     */
    public function guardarBitacoraAjax(Request $request)
    {
        try {
            $bitacoraId = $request->input('bitacora_id'); // ID para edición
            $proyectoId = $request->input('proyecto_id');
            $usuarioId = $request->input('usuario_id');
            $fecha = $request->input('fecha');
            $horaEntrada = $request->input('hora_entrada');
            $horaSalida = $request->input('hora_salida');
            $descripcion = $request->input('descripcion');
            $rubroId = $request->input('rubro_id', 1); // Default: Hora Normal
            $lineaPresupuestoId = $request->input('linea_presupuesto_id', null);
            $usuarioRegistro = $this->getUsuarioAuth()['id'];

            // Verificar que el proyecto esté activo
            $proyecto = DB::table('proyecto')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->where('proyecto.id', '=', $proyectoId)
                ->select('proyecto.*', 'sis_estado.cod_general as estado_codigo')
                ->first();

            if (!$proyecto) {
                return $this->responseAjaxServerError("Proyecto no encontrado", []);
            }

            if ($proyecto->estado_codigo != 'PROY_ACTIVO') {
                return $this->responseAjaxServerError("No se pueden registrar bitácoras en proyectos que no están Activos", []);
            }

            DB::beginTransaction();

            $registroExistente = null;

            // Si viene bitacora_id, es una edición
            if ($bitacoraId) {
                $registroExistente = DB::table('bit_usuario_proyecto')
                    ->where('id', '=', $bitacoraId)
                    ->first();
            } else {
                // Si no, verificar si ya existe un registro para esta fecha y rubro
                $registroExistente = DB::table('bit_usuario_proyecto')
                    ->where('proyecto', '=', $proyectoId)
                    ->where('usuario', '=', $usuarioId)
                    ->where('fecha', '=', $fecha)
                    ->where('rubro_extra_salario', '=', $rubroId)
                    ->first();
            }

            // Obtener ID del estado pendiente
            $estadoPendiente = DB::table('sis_estado')
                ->where('cod_general', '=', 'BIT_PROY_PENDIENTE')
                ->first();
                
            if (!$estadoPendiente) {
                DB::rollBack();
                return $this->responseAjaxServerError("Error: Estado BIT_PROY_PENDIENTE no encontrado en la base de datos. Ejecute el script SQL primero.", []);
            }

            $datosBitacora = [
                'hora_entrada' => $horaEntrada,
                'hora_salida' => $horaSalida,
                'descripcion' => $descripcion,
                'rubro_extra_salario' => $rubroId,
                'linea_presupuesto' => $lineaPresupuestoId,
                'usuario_registro' => $usuarioRegistro,
                'fecha_registro' => date("Y-m-d H:i:s"),
                'estado' => $estadoPendiente->id
            ];

            if ($registroExistente) {
                // Actualizar registro existente (solo si está pendiente)
                if ($registroExistente->estado != $estadoPendiente->id) {
                    DB::rollBack();
                    return $this->responseAjaxServerError("No se puede editar una bitácora que ya fue aprobada o rechazada", []);
                }
                
                DB::table('bit_usuario_proyecto')
                    ->where('id', '=', $registroExistente->id)
                    ->update($datosBitacora);
            } else {
                // Crear nuevo registro
                $datosBitacora['proyecto'] = $proyectoId;
                $datosBitacora['usuario'] = $usuarioId;
                $datosBitacora['fecha'] = $fecha;
                DB::table('bit_usuario_proyecto')->insertGetId($datosBitacora);
            }

            DB::commit();
            return $this->responseAjaxSuccess("Bitácora guardada correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar la bitácora: " . $ex->getMessage(), []);
        }
    }

    /**
     * Elimina un registro de bitácora (solo si está pendiente)
     */
    public function eliminarBitacoraAjax(Request $request)
    {
        try {
            $bitacoraId = $request->input('bitacora_id');

            DB::beginTransaction();
            
            $bitacora = DB::table('bit_usuario_proyecto')->where('id', '=', $bitacoraId)->first();
            if (!$bitacora) {
                DB::rollBack();
                return $this->responseAjaxServerError("Registro no encontrado", []);
            }

            // Verificar que el proyecto esté activo
            $proyecto = DB::table('proyecto')
                ->join('sis_estado', 'sis_estado.id', '=', 'proyecto.estado')
                ->where('proyecto.id', '=', $bitacora->proyecto)
                ->select('proyecto.*', 'sis_estado.cod_general as estado_codigo')
                ->first();

            if ($proyecto && $proyecto->estado_codigo != 'PROY_ACTIVO') {
                DB::rollBack();
                return $this->responseAjaxServerError("No se pueden eliminar bitácoras en proyectos que no están Activos", []);
            }
            
            // Obtener ID del estado pendiente
            $estadoPendiente = DB::table('sis_estado')
                ->where('cod_general', '=', 'BIT_PROY_PENDIENTE')
                ->first();
            
            // Solo se puede eliminar si está pendiente
            if ($estadoPendiente && $bitacora->estado != $estadoPendiente->id) {
                DB::rollBack();
                return $this->responseAjaxServerError("No se puede eliminar una bitácora que ya fue aprobada o rechazada", []);
            }
            
            DB::table('bit_usuario_proyecto')
                ->where('id', '=', $bitacoraId)
                ->delete();
            DB::commit();

            return $this->responseAjaxSuccess("Registro eliminado correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al eliminar el registro", []);
        }
    }

    /**
     * Cambia el estado de una bitácora (aprobar o rechazar)
     */
    public function cambiarEstadoBitacoraAjax(Request $request)
    {
        try {
            $bitacoraId = $request->input('bitacora_id');
            $accion = $request->input('accion'); // 'aprobar' o 'rechazar'
            $usuarioAutoriza = session()->get('usuario')['id'] ?? 0;

            DB::beginTransaction();

            $estadoCodigo = $accion === 'aprobar' ? 'BIT_PROY_APROBADA' : 'BIT_PROY_RECHAZADA';
            $estado = DB::table('sis_estado')
                ->where('cod_general', '=', $estadoCodigo)
                ->first();
                
            if (!$estado) {
                DB::rollBack();
                return $this->responseAjaxServerError("Error: Estado {$estadoCodigo} no encontrado. Ejecute el script SQL primero.", []);
            }

            // Obtener la bitácora antes de actualizar
            $bitacora = DB::table('bit_usuario_proyecto')->where('id', '=', $bitacoraId)->first();

            DB::table('bit_usuario_proyecto')
                ->where('id', '=', $bitacoraId)
                ->update([
                    'estado' => $estado->id,
                    'usuario_autoriza' => $usuarioAutoriza,
                    'fecha_autorizacion' => date("Y-m-d H:i:s")
                ]);

            // Recalcular monto consumido de la línea de presupuesto si está asignada
            if ($bitacora && $bitacora->linea_presupuesto) {
                $this->recalcularMontoConsumidoLinea($bitacora->linea_presupuesto);
            }

            DB::commit();
            
            $mensaje = $accion === 'aprobar' ? 'Bitácora aprobada correctamente' : 'Bitácora rechazada correctamente';
            return $this->responseAjaxSuccess($mensaje, []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al cambiar el estado: " . $ex->getMessage(), []);
        }
    }

    /**
     * Carga todas las bitácoras para autorización
     */
    public function cargarBitacorasAutorizacionAjax(Request $request)
    {
        try {
            $filtroEstado = $request->input('filtro_estado', 'PENDIENTE'); // PENDIENTE, TODAS, APROBADAS, RECHAZADAS

            $query = DB::table('bit_usuario_proyecto')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'bit_usuario_proyecto.estado')
                ->leftJoin('proyecto', 'proyecto.id', '=', 'bit_usuario_proyecto.proyecto')
                ->leftJoin('usuario as u', 'u.id', '=', 'bit_usuario_proyecto.usuario')
                ->leftJoin('cliente', 'cliente.id', '=', 'proyecto.cliente')
                ->leftJoin('usuario as u_autoriza', 'u_autoriza.id', '=', 'bit_usuario_proyecto.usuario_autoriza')
                ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                ->leftJoin('proyecto_linea_presupuesto', 'proyecto_linea_presupuesto.id', '=', 'bit_usuario_proyecto.linea_presupuesto')
                ->select(
                    'bit_usuario_proyecto.*',
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo',
                    'proyecto.nombre as proyecto_nombre',
                    'cliente.nombre_completo as cliente_nombre',
                    DB::raw("CONCAT(u.nombre, ' ', u.ape1, IFNULL(CONCAT(' ', u.ape2), '')) as usuario_nombre"),
                    DB::raw("CONCAT(u_autoriza.nombre, ' ', u_autoriza.ape1, IFNULL(CONCAT(' ', u_autoriza.ape2), '')) as autorizado_por"),
                    'rubro_extra_salario.nombre as rubro_nombre',
                    'rubro_extra_salario.multiplicador as rubro_multiplicador',
                    'proyecto_linea_presupuesto.numero_linea as linea_numero',
                    'proyecto_linea_presupuesto.descripcion as linea_descripcion'
                );

            // Aplicar filtro de estado
            if ($filtroEstado === 'PENDIENTE') {
                $estadoPendiente = DB::table('sis_estado')
                    ->where('cod_general', '=', 'BIT_PROY_PENDIENTE')
                    ->first();
                if ($estadoPendiente) {
                    $query->where('bit_usuario_proyecto.estado', '=', $estadoPendiente->id);
                }
            } elseif ($filtroEstado === 'APROBADAS') {
                $estadoAprobada = DB::table('sis_estado')
                    ->where('cod_general', '=', 'BIT_PROY_APROBADA')
                    ->first();
                if ($estadoAprobada) {
                    $query->where('bit_usuario_proyecto.estado', '=', $estadoAprobada->id);
                }
            } elseif ($filtroEstado === 'RECHAZADAS') {
                $estadoRechazada = DB::table('sis_estado')
                    ->where('cod_general', '=', 'BIT_PROY_RECHAZADA')
                    ->first();
                if ($estadoRechazada) {
                    $query->where('bit_usuario_proyecto.estado', '=', $estadoRechazada->id);
                }
            }
            // Si es 'TODAS', no aplicamos filtro

            $bitacoras = $query->orderBy('bit_usuario_proyecto.fecha', 'desc')
                ->orderBy('bit_usuario_proyecto.id', 'desc')
                ->get();

            return $this->responseAjaxSuccess("", $bitacoras);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Error cargando bitácoras: " . $ex->getMessage(), []);
        }
    }
}
