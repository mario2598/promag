<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SpaceUtil;

class CuentaPorPagarController extends Controller
{
    use SpaceUtil;

    protected $codigo_pantalla = "cxp";

    /**
     * Muestra la vista principal de Cuentas por Pagar
     */
    public function index()
    {

        return view('cxp.index');
    }

    /**
     * Muestra la vista de historial de pagos por proyecto
     */
    public function historialPagosProyectos()
    {
        return view('cxp.historial_pagos_proyectos');
    }

    /**
     * Obtiene el historial de pagos agrupado por proyectos
     */
    public function historialPagosProyectosAjax(Request $request)
    {
        try {
            // Obtener el tipo CXP_PAGO_HORAS
            $tipoCxP = DB::table('sis_tipo')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
                ->where('sis_clase.cod_general', '=', 'TIPOS_CXP')
                ->where('sis_tipo.cod_general', '=', 'CXP_PAGO_HORAS')
                ->select('sis_tipo.id')
                ->first();

            if (!$tipoCxP) {
                return $this->responseAjaxServerError("Tipo de CxP no encontrado", []);
            }

            // Obtener proyectos con sus pagos agrupados
            $proyectos = DB::table('bit_usuario_proyecto')
                ->join('cxp', 'cxp.id', '=', 'bit_usuario_proyecto.cxp')
                ->join('gasto', 'gasto.id', '=', 'cxp.gasto')
                ->join('proyecto', 'proyecto.id', '=', 'bit_usuario_proyecto.proyecto')
                ->where('cxp.tipo_cxp', '=', $tipoCxP->id)
                ->whereNotNull('cxp.gasto')
                ->select(
                    'proyecto.id as proyecto_id',
                    'proyecto.nombre as proyecto_nombre',
                    DB::raw('COUNT(DISTINCT gasto.id) as num_pagos'),
                    DB::raw('SUM(gasto.monto * gasto.tipo_cambio) as total_pagado')
                )
                ->groupBy('proyecto.id', 'proyecto.nombre')
                ->orderBy('total_pagado', 'desc')
                ->get();

            // Calcular resumen general
            $totalPagos = 0;
            $montoTotal = 0;
            
            foreach ($proyectos as $proyecto) {
                $totalPagos += intval($proyecto->num_pagos);
                $montoTotal += floatval($proyecto->total_pagado);
            }

            $resumen = [
                'total_pagos' => $totalPagos,
                'monto_total' => $montoTotal,
                'proyectos_con_pagos' => $proyectos->count()
            ];

            return $this->responseAjaxSuccess("Proyectos cargados correctamente", [
                'data' => $proyectos,
                'resumen' => $resumen
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar proyectos: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene los pagos de un proyecto específico
     */
    public function pagosProyectoAjax(Request $request)
    {
        try {
            $proyectoId = $request->input('proyecto_id');

            // Obtener el tipo CXP_PAGO_HORAS
            $tipoCxP = DB::table('sis_tipo')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
                ->where('sis_clase.cod_general', '=', 'TIPOS_CXP')
                ->where('sis_tipo.cod_general', '=', 'CXP_PAGO_HORAS')
                ->select('sis_tipo.id')
                ->first();

            if (!$tipoCxP) {
                return $this->responseAjaxServerError("Tipo de CxP no encontrado", []);
            }

            // Obtener gastos del proyecto
            $pagos = DB::table('bit_usuario_proyecto')
                ->join('cxp', 'cxp.id', '=', 'bit_usuario_proyecto.cxp')
                ->join('gasto', 'gasto.id', '=', 'cxp.gasto')
                ->join('sis_tipo as tipo_pago', 'tipo_pago.id', '=', 'gasto.tipo_pago')
                ->where('bit_usuario_proyecto.proyecto', '=', $proyectoId)
                ->where('cxp.tipo_cxp', '=', $tipoCxP->id)
                ->whereNotNull('cxp.gasto')
                ->select(
                    'gasto.id',
                    'gasto.fecha',
                    'gasto.monto',
                    'gasto.tipo_cambio',
                    'gasto.num_factura',
                    'gasto.descripcion',
                    'gasto.observacion',
                    'tipo_pago.nombre as tipo_pago_nombre',
                    DB::raw('GROUP_CONCAT(DISTINCT cxp.beneficiario SEPARATOR ", ") as beneficiarios'),
                    DB::raw('COUNT(DISTINCT cxp.id) as num_cxps')
                )
                ->groupBy('gasto.id', 'gasto.fecha', 'gasto.monto', 'gasto.tipo_cambio', 'gasto.num_factura', 
                         'gasto.descripcion', 'gasto.observacion', 'tipo_pago.nombre')
                ->orderBy('gasto.fecha', 'desc')
                ->get();

            // Calcular resumen
            $totalPagos = $pagos->count();
            $montoTotal = 0;
            $beneficiarios = [];
            
            foreach ($pagos as $pago) {
                $montoTotal += floatval($pago->monto) * floatval($pago->tipo_cambio);
                $benefs = explode(', ', $pago->beneficiarios);
                foreach ($benefs as $benef) {
                    if (!in_array($benef, $beneficiarios)) {
                        $beneficiarios[] = $benef;
                    }
                }
            }

            $resumen = [
                'total_pagos' => $totalPagos,
                'monto_total' => $montoTotal,
                'beneficiarios' => count($beneficiarios)
            ];

            return $this->responseAjaxSuccess("Pagos cargados correctamente", [
                'data' => $pagos,
                'resumen' => $resumen
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar pagos: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene el consumo por líneas de presupuesto de un proyecto
     */
    public function consumoLineasPresupuestoAjax(Request $request)
    {
        try {
            $proyectoId = $request->input('proyecto_id');

            // Obtener estados de bitácoras
            $estadoAprobada = DB::table('sis_estado')
                ->where('cod_general', '=', 'BIT_PROY_APROBADA')
                ->first();
            $estadoPendiente = DB::table('sis_estado')
                ->where('cod_general', '=', 'BIT_PROY_PENDIENTE')
                ->first();

            if (!$estadoAprobada || !$estadoPendiente) {
                return $this->responseAjaxServerError("Estados de bitácora no encontrados", []);
            }

            // Obtener todas las líneas de presupuesto del proyecto
            $lineas = DB::table('proyecto_linea_presupuesto')
                ->where('proyecto', '=', $proyectoId)
                ->where('estado', '=', 'A')
                ->orderBy('numero_linea', 'asc')
                ->get();

            $lineasConConsumo = [];

            foreach ($lineas as $linea) {
                // Obtener bitácoras aprobadas para esta línea
                $bitacorasAprobadas = DB::table('bit_usuario_proyecto')
                    ->leftJoin('usuario', 'usuario.id', '=', 'bit_usuario_proyecto.usuario')
                    ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                    ->where('bit_usuario_proyecto.linea_presupuesto', '=', $linea->id)
                    ->where('bit_usuario_proyecto.estado', '=', $estadoAprobada->id)
                    ->select(
                        'bit_usuario_proyecto.hora_entrada',
                        'bit_usuario_proyecto.hora_salida',
                        'usuario.precio_hora',
                        'rubro_extra_salario.multiplicador'
                    )
                    ->get();

                // Obtener bitácoras pendientes para esta línea
                $bitacorasPendientes = DB::table('bit_usuario_proyecto')
                    ->leftJoin('usuario', 'usuario.id', '=', 'bit_usuario_proyecto.usuario')
                    ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                    ->where('bit_usuario_proyecto.linea_presupuesto', '=', $linea->id)
                    ->where('bit_usuario_proyecto.estado', '=', $estadoPendiente->id)
                    ->select(
                        'bit_usuario_proyecto.hora_entrada',
                        'bit_usuario_proyecto.hora_salida',
                        'usuario.precio_hora',
                        'rubro_extra_salario.multiplicador'
                    )
                    ->get();

                // Calcular monto consumido (aprobadas)
                $montoConsumido = 0;
                foreach ($bitacorasAprobadas as $bitacora) {
                    $horas = $this->calcularHoras($bitacora->hora_entrada, $bitacora->hora_salida);
                    $precioHora = floatval($bitacora->precio_hora ?? 0);
                    $multiplicador = floatval($bitacora->multiplicador ?? 1.00);
                    $costo = $horas * $precioHora * $multiplicador;
                    $montoConsumido += $costo;
                }

                // Calcular monto pendiente (pendientes)
                $montoPendiente = 0;
                foreach ($bitacorasPendientes as $bitacora) {
                    $horas = $this->calcularHoras($bitacora->hora_entrada, $bitacora->hora_salida);
                    $precioHora = floatval($bitacora->precio_hora ?? 0);
                    $multiplicador = floatval($bitacora->multiplicador ?? 1.00);
                    $costo = $horas * $precioHora * $multiplicador;
                    $montoPendiente += $costo;
                }

                $montoAutorizado = floatval($linea->monto_autorizado ?? 0);
                $montoDisponible = $montoAutorizado - $montoConsumido;

                $lineasConConsumo[] = [
                    'id' => $linea->id,
                    'numero_linea' => $linea->numero_linea,
                    'descripcion' => $linea->descripcion,
                    'monto_autorizado' => $montoAutorizado,
                    'monto_consumido' => $montoConsumido,
                    'monto_pendiente' => $montoPendiente,
                    'monto_disponible' => $montoDisponible,
                    'porcentaje_consumido' => $montoAutorizado > 0 ? ($montoConsumido / $montoAutorizado) * 100 : 0,
                    'num_bitacoras_aprobadas' => $bitacorasAprobadas->count(),
                    'num_bitacoras_pendientes' => $bitacorasPendientes->count()
                ];
            }

            return $this->responseAjaxSuccess("Consumo por líneas cargado correctamente", [
                'data' => $lineasConConsumo
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar consumo por líneas: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene el detalle completo de un pago de proyecto
     */
    public function detallePagoProyectoAjax(Request $request)
    {
        try {
            $gastoId = $request->input('gasto_id');

            // Obtener información del gasto
            $gasto = DB::table('gasto')
                ->leftJoin('sis_tipo as tipo_pago', 'tipo_pago.id', '=', 'gasto.tipo_pago')
                ->where('gasto.id', '=', $gastoId)
                ->select(
                    'gasto.*',
                    'tipo_pago.nombre as tipo_pago_nombre'
                )
                ->first();

            if (!$gasto) {
                return $this->responseAjaxServerError("Gasto no encontrado", []);
            }

            // Obtener CxPs relacionadas con sus deducciones
            $cxps = DB::table('cxp')
                ->leftJoin('sis_tipo', 'sis_tipo.id', '=', 'cxp.tipo_cxp')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'cxp.estado')
                ->where('cxp.gasto', '=', $gastoId)
                ->select(
                    'cxp.*',
                    'sis_tipo.nombre as tipo_nombre',
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo'
                )
                ->get();

            // Para cada CxP, obtener sus deducciones y bitácoras relacionadas
            foreach ($cxps as $cxp) {
                $deducciones = DB::table('cxp_deduccion')
                    ->leftJoin('rubro_deduccion_salario', 'rubro_deduccion_salario.id', '=', 'cxp_deduccion.rubro_deduccion')
                    ->where('cxp_deduccion.cxp', $cxp->id)
                    ->select(
                        'cxp_deduccion.*',
                        'rubro_deduccion_salario.nombre as rubro_nombre',
                        'rubro_deduccion_salario.descripcion as rubro_descripcion'
                    )
                    ->get();
                
                $cxp->deducciones = $deducciones;

                // Obtener bitácoras relacionadas con información de línea de presupuesto
                $bitacoras = DB::table('bit_usuario_proyecto')
                    ->leftJoin('usuario', 'usuario.id', '=', 'bit_usuario_proyecto.usuario')
                    ->leftJoin('proyecto', 'proyecto.id', '=', 'bit_usuario_proyecto.proyecto')
                    ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                    ->leftJoin('proyecto_linea_presupuesto', 'proyecto_linea_presupuesto.id', '=', 'bit_usuario_proyecto.linea_presupuesto')
                    ->where('bit_usuario_proyecto.cxp', $cxp->id)
                    ->select(
                        'bit_usuario_proyecto.*',
                        DB::raw("CONCAT(usuario.nombre, ' ', usuario.ape1, IFNULL(CONCAT(' ', usuario.ape2), '')) as usuario_nombre"),
                        'usuario.precio_hora',
                        'proyecto.nombre as proyecto_nombre',
                        'rubro_extra_salario.nombre as rubro_nombre',
                        'rubro_extra_salario.multiplicador',
                        'proyecto_linea_presupuesto.numero_linea as linea_numero',
                        'proyecto_linea_presupuesto.descripcion as linea_descripcion'
                    )
                    ->orderBy('bit_usuario_proyecto.fecha', 'desc')
                    ->orderBy('bit_usuario_proyecto.hora_entrada', 'asc')
                    ->get();

                // Calcular costo de cada bitácora
                foreach ($bitacoras as $bitacora) {
                    $horas = $this->calcularHoras($bitacora->hora_entrada, $bitacora->hora_salida);
                    $precioHora = floatval($bitacora->precio_hora ?? 0);
                    $multiplicador = floatval($bitacora->multiplicador ?? 1.00);
                    $bitacora->horas_calculadas = $horas;
                    $bitacora->costo_calculado = $horas * $precioHora * $multiplicador;
                }

                $cxp->bitacoras = $bitacoras;
            }

            return $this->responseAjaxSuccess("Detalle cargado correctamente", [
                'gasto' => $gasto,
                'cxps' => $cxps
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar detalle: " . $ex->getMessage(), []);
        }
    }

    /**
     * Carga todas las CxP
     */
    public function cargarCxPAjax(Request $request)
    {
        try {
            $cxp = DB::table('cxp')
                ->leftJoin('sis_tipo', 'sis_tipo.id', '=', 'cxp.tipo_cxp')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'cxp.estado')
                ->leftJoin('usuario as usuario_creacion', 'usuario_creacion.id', '=', 'cxp.usuario_creacion')
                ->leftJoin('usuario as usuario_aprobacion', 'usuario_aprobacion.id', '=', 'cxp.usuario_aprobacion')
                ->leftJoin('usuario as usuario_beneficiario', 'usuario_beneficiario.id', '=', 'cxp.usuario_beneficiario')
                ->select(
                    'cxp.*',
                    'sis_tipo.nombre as tipo_nombre',
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo',
                    DB::raw("CONCAT(usuario_creacion.nombre, ' ', usuario_creacion.ape1, IFNULL(CONCAT(' ', usuario_creacion.ape2), '')) as usuario_creacion_nombre"),
                    DB::raw("CONCAT(usuario_aprobacion.nombre, ' ', usuario_aprobacion.ape1, IFNULL(CONCAT(' ', usuario_aprobacion.ape2), '')) as usuario_aprobacion_nombre"),
                    DB::raw("CONCAT(usuario_beneficiario.nombre, ' ', usuario_beneficiario.ape1, IFNULL(CONCAT(' ', usuario_beneficiario.ape2), '')) as usuario_beneficiario_nombre")
                )
                ->orderBy('cxp.fecha_creacion', 'desc')
                ->get();

            return $this->responseAjaxSuccess("CxP cargadas correctamente", $cxp);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar las CxP: " . $ex->getMessage(), []);
        }
    }

    /**
     * Crea una nueva CxP con bitácoras seleccionadas
     */
    public function crearCxPAjax(Request $request)
    {
        try {
            $bitacorasIds = $request->input('bitacoras_ids');
            $observaciones = $request->input('observaciones', '');
            $deducciones = $request->input('deducciones', []);

            if (empty($bitacorasIds) || !is_array($bitacorasIds)) {
                return $this->responseAjaxServerError("No se seleccionaron bitácoras", []);
            }

            DB::beginTransaction();

            // Obtener información de las bitácoras
            $bitacoras = DB::table('bit_usuario_proyecto')
                ->leftJoin('proyecto', 'proyecto.id', '=', 'bit_usuario_proyecto.proyecto')
                ->leftJoin('usuario', 'usuario.id', '=', 'bit_usuario_proyecto.usuario')
                ->leftJoin('cliente', 'cliente.id', '=', 'proyecto.cliente')
                ->leftJoin('rubro_extra_salario', 'rubro_extra_salario.id', '=', 'bit_usuario_proyecto.rubro_extra_salario')
                ->whereIn('bit_usuario_proyecto.id', $bitacorasIds)
                ->select(
                    'bit_usuario_proyecto.*',
                    'proyecto.nombre as proyecto_nombre',
                    'cliente.nombre_completo as cliente_nombre',
                    'usuario.precio_hora',
                    'rubro_extra_salario.multiplicador',
                    'rubro_extra_salario.nombre as rubro_nombre'
                )
                ->get();

            if ($bitacoras->isEmpty()) {
                DB::rollBack();
                return $this->responseAjaxServerError("No se encontraron las bitácoras seleccionadas", []);
            }

            // Agrupar por proyecto y usuario
            $agrupadas = $bitacoras->groupBy(function ($item) {
                return $item->proyecto . '_' . $item->usuario;
            });

            $cxpCreadas = [];

            foreach ($agrupadas as $grupo) {
                $primerBitacora = $grupo->first();
                $proyectoNombre = $primerBitacora->proyecto_nombre;
                $clienteNombre = $primerBitacora->cliente_nombre;
                
                // Crear nombre del beneficiario
                $beneficiario = "Trabajador - " . $primerBitacora->proyecto_nombre;

                // Generar número de CxP
                $numeroCxp = $this->generarNumeroCxP();

                // Calcular monto total
                $montoTotal = 0;
                $detalles = [];

                foreach ($grupo as $bitacora) {
                    $horas = $this->calcularHoras($bitacora->hora_entrada, $bitacora->hora_salida);
                    $multiplicador = $bitacora->multiplicador ?? 1;
                    $costo = $horas * $bitacora->precio_hora * $multiplicador;
                    $montoTotal += $costo;

                    // Crear descripción del detalle
                    $rubroNombre = $bitacora->rubro_nombre ?? 'Hora Normal';
                    $descripcion = "Bitácora del " . date('d/m/Y', strtotime($bitacora->fecha)) . 
                                 " - " . $rubroNombre . 
                                 " (" . $horas . " hrs × ₡" . number_format($bitacora->precio_hora, 2) . 
                                 " × " . $multiplicador . ")";

                    $detalles[] = [
                        'descripcion' => $descripcion,
                        'monto' => $costo,
                        'cantidad' => $horas,
                        'precio_unitario' => $bitacora->precio_hora * $multiplicador
                    ];
                }

                // Obtener información bancaria del usuario que trabajó (el que recibirá el pago)
                $usuarioInfo = DB::table('usuario')
                    ->where('id', $primerBitacora->usuario)
                    ->select(
                        'nombre_beneficiario', 
                        'numero_cuenta', 
                        'nombre_banco',
                        DB::raw("CONCAT(nombre, ' ', ape1, IFNULL(CONCAT(' ', ape2), '')) as nombre_completo")
                    )
                    ->first();

                // Construir nombre del beneficiario
                // Si tiene nombre_beneficiario configurado, usarlo; sino usar nombre completo
                $beneficiario = !empty($usuarioInfo->nombre_beneficiario) 
                    ? $usuarioInfo->nombre_beneficiario 
                    : ($usuarioInfo->nombre_completo ?? $beneficiario);

                // Calcular deducciones
                $totalDeducciones = 0;
                $deduccionesInfo = [];
                if (!empty($deducciones) && is_array($deducciones)) {
                    foreach ($deducciones as $deduccion) {
                        $porcentaje = floatval($deduccion['porcentaje'] ?? 0);
                        $montoDeduccion = ($montoTotal * $porcentaje) / 100;
                        $totalDeducciones += $montoDeduccion;
                        
                        $deduccionesInfo[] = [
                            'rubro_id' => $deduccion['rubro_id'],
                            'nombre' => $deduccion['nombre'],
                            'porcentaje' => $porcentaje,
                            'monto' => $montoDeduccion
                        ];
                    }
                }

                // Monto final después de deducciones
                $montoFinal = $montoTotal - $totalDeducciones;

                // Crear CxP con tipo "Pago de horas trabajadas"
                // usuario_creacion = Usuario que autoriza/crea la CxP
                // usuario_beneficiario = Usuario que trabajó las horas (recibe el pago)
                $cxpId = DB::table('cxp')->insertGetId([
                    'numero_cxp' => $numeroCxp,
                    'tipo_cxp' => $this->obtenerTipoCxP('CXP_PAGO_HORAS'),
                    'beneficiario' => $beneficiario,
                    'numero_cuenta' => $usuarioInfo->numero_cuenta ?? null,
                    'moneda' => 'CRC',
                    'monto_total' => $montoFinal,
                    'observaciones' => $observaciones . "\nProyecto: " . $proyectoNombre . "\nCliente: " . $clienteNombre . 
                                     ($totalDeducciones > 0 ? "\n\nDeducciones aplicadas: ₡" . number_format($totalDeducciones, 2) : ""),
                    'estado' => $this->obtenerEstadoCxP('CXP_PENDIENTE'),
                    'usuario_creacion' => session('usuario')['id'] ?? 1,  // Usuario que autoriza
                    'usuario_beneficiario' => $primerBitacora->usuario  // Usuario que recibe el pago
                ]);

                // Crear detalles de CxP
                foreach ($detalles as $detalle) {
                    DB::table('cxp_detalle')->insert([
                        'cxp' => $cxpId,
                        'descripcion' => $detalle['descripcion'],
                        'monto' => $detalle['monto'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario']
                    ]);
                }

                // Guardar deducciones aplicadas
                foreach ($deduccionesInfo as $deduccionInfo) {
                    DB::table('cxp_deduccion')->insert([
                        'cxp' => $cxpId,
                        'rubro_deduccion' => $deduccionInfo['rubro_id'],
                        'monto_base' => $montoTotal,
                        'porcentaje' => $deduccionInfo['porcentaje'],
                        'monto_deduccion' => $deduccionInfo['monto']
                    ]);
                }

                // Aprobar bitácoras y asociar a CxP
                foreach ($grupo as $bitacora) {
                    DB::table('bit_usuario_proyecto')
                        ->where('id', $bitacora->id)
                        ->update([
                            'estado' => $this->obtenerEstadoBitacora('BIT_PROY_APROBADA'),
                            'usuario_autoriza' => session('usuario')['id'] ?? 1,
                            'fecha_autorizacion' => now(),
                            'cxp' => $cxpId
                        ]);
                }

                $cxpCreadas[] = [
                    'id' => $cxpId,
                    'numero' => $numeroCxp,
                    'beneficiario' => $beneficiario,
                    'monto' => $montoTotal
                ];
            }

            DB::commit();
            return $this->responseAjaxSuccess("CxP creadas exitosamente", $cxpCreadas);

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al crear CxP: " . $ex->getMessage(), []);
        }
    }

    /**
     * Genera un número único para la CxP
     */
    private function generarNumeroCxP()
    {
        $fecha = date('Y-m-d');
        $contador = DB::table('cxp')
            ->whereDate('fecha_creacion', $fecha)
            ->count() + 1;

        return "CXP-" . date('Y') . "-" . str_pad($contador, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calcula las horas trabajadas entre dos horas (HH:MM)
     * Maneja correctamente el cruce de medianoche
     */
    private function calcularHoras($horaEntrada, $horaSalida)
    {
        if (empty($horaEntrada) || empty($horaSalida)) {
            return 0;
        }

        // Parsear horas y minutos
        $entradaParts = explode(':', $horaEntrada);
        $salidaParts = explode(':', $horaSalida);
        
        $horaEntradaNum = intval($entradaParts[0]);
        $minutoEntradaNum = intval($entradaParts[1] ?? 0);
        $horaSalidaNum = intval($salidaParts[0]);
        $minutoSalidaNum = intval($salidaParts[1] ?? 0);
        
        // Convertir a minutos totales
        $minutosEntrada = $horaEntradaNum * 60 + $minutoEntradaNum;
        $minutosSalida = $horaSalidaNum * 60 + $minutoSalidaNum;
        
        // Calcular diferencia
        $diferenciaMinutos = $minutosSalida - $minutosEntrada;
        
        // Si la diferencia es negativa, significa que cruzó medianoche (trabajo nocturno)
        // Agregar 24 horas (1440 minutos)
        if ($diferenciaMinutos < 0) {
            $diferenciaMinutos += 1440;
        }
        
        // Convertir minutos a horas
        return $diferenciaMinutos / 60;
    }

    /**
     * Obtiene el ID de un estado por su código
     */
    private function obtenerEstadoCxP($codigo)
    {
        $estado = DB::table('sis_estado')
            ->join('sis_clase', 'sis_clase.id', '=', 'sis_estado.clase')
            ->where('sis_estado.cod_general', $codigo)
            ->where('sis_clase.cod_general', 'EST_CXP')
            ->select('sis_estado.id')
            ->first();
        
        if (!$estado) {
            // Si no encuentra el estado, buscar el estado por defecto
            $estadoDefault = DB::table('sis_estado')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_estado.clase')
                ->where('sis_estado.cod_general', 'CXP_PENDIENTE')
                ->where('sis_clase.cod_general', 'EST_CXP')
                ->select('sis_estado.id')
                ->first();
            
            return $estadoDefault ? $estadoDefault->id : 1;
        }
        
        return $estado->id;
    }

    /**
     * Obtiene el ID de un estado de bitácora por su código
     */
    private function obtenerEstadoBitacora($codigo)
    {
        $estado = DB::table('sis_estado')
            ->join('sis_clase', 'sis_clase.id', '=', 'sis_estado.clase')
            ->where('sis_estado.cod_general', $codigo)
            ->where('sis_clase.cod_general', 'EST_BITACORA_PROY')
            ->select('sis_estado.id')
            ->first();
        
        return $estado ? $estado->id : 1;
    }

    /**
     * Obtiene el ID de un tipo de CxP por su código
     * @param string $codigo Código del tipo (ej: CXP_PAGO_HORAS, CXP_SERVICIOS, etc.)
     * @return int ID del tipo de CxP
     */
    private function obtenerTipoCxP($codigo)
    {
        $tipo = DB::table('sis_tipo')
            ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
            ->where('sis_tipo.cod_general', $codigo)
            ->where('sis_clase.cod_general', 'TIPOS_CXP')
            ->select('sis_tipo.id')
            ->first();
        
        if (!$tipo) {
            // Si no encuentra el tipo, buscar el tipo por defecto (Pago de horas)
            $tipoDefault = DB::table('sis_tipo')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
                ->where('sis_tipo.cod_general', 'CXP_PAGO_HORAS')
                ->where('sis_clase.cod_general', 'TIPOS_CXP')
                ->select('sis_tipo.id')
                ->first();
            
            return $tipoDefault ? $tipoDefault->id : 1;
        }
        
        return $tipo->id;
    }

    /**
     * Actualiza la información de una CxP
     */
    public function actualizarCxPAjax(Request $request)
    {
        try {
            $id = $request->input('id');
            $datos = $request->only([
                'beneficiario',
                'numero_cuenta',
                'moneda',
                'fecha_vencimiento',
                'observaciones'
            ]);

            // Filtrar valores nulos/vacíos
            $datos = array_filter($datos, function($value) {
                return $value !== null && $value !== '';
            });

            if (empty($datos)) {
                return $this->responseAjaxServerError("No se proporcionaron datos para actualizar", []);
            }

            DB::table('cxp')
                ->where('id', $id)
                ->update($datos);

            return $this->responseAjaxSuccess("CxP actualizada correctamente", []);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al actualizar CxP: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene los detalles de una CxP
     */
    public function obtenerDetalleCxPAjax(Request $request)
    {
        try {
            $id = $request->input('id');

            $cxp = DB::table('cxp')
                ->leftJoin('sis_tipo', 'sis_tipo.id', '=', 'cxp.tipo_cxp')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'cxp.estado')
                ->leftJoin('usuario as usuario_creacion', 'usuario_creacion.id', '=', 'cxp.usuario_creacion')
                ->leftJoin('usuario as usuario_aprobacion', 'usuario_aprobacion.id', '=', 'cxp.usuario_aprobacion')
                ->leftJoin('usuario as usuario_beneficiario', 'usuario_beneficiario.id', '=', 'cxp.usuario_beneficiario')
                ->where('cxp.id', $id)
                ->select(
                    'cxp.*',
                    'sis_tipo.nombre as tipo_nombre',
                    'sis_estado.nombre as estado_nombre',
                    'sis_estado.cod_general as estado_codigo',
                    DB::raw("CONCAT(usuario_creacion.nombre, ' ', usuario_creacion.ape1, IFNULL(CONCAT(' ', usuario_creacion.ape2), '')) as usuario_creacion_nombre"),
                    DB::raw("CONCAT(usuario_aprobacion.nombre, ' ', usuario_aprobacion.ape1, IFNULL(CONCAT(' ', usuario_aprobacion.ape2), '')) as usuario_aprobacion_nombre"),
                    DB::raw("CONCAT(usuario_beneficiario.nombre, ' ', usuario_beneficiario.ape1, IFNULL(CONCAT(' ', usuario_beneficiario.ape2), '')) as usuario_beneficiario_nombre")
                )
                ->first();

            if (!$cxp) {
                return $this->responseAjaxServerError("CxP no encontrada", []);
            }

            // Obtener detalles de la CxP
            $detalles = DB::table('cxp_detalle')
                ->where('cxp', $id)
                ->orderBy('fecha_creacion', 'asc')
                ->get();

            $cxp->detalles = $detalles;

            // Obtener deducciones de la CxP
            $deducciones = DB::table('cxp_deduccion')
                ->leftJoin('rubro_deduccion_salario', 'rubro_deduccion_salario.id', '=', 'cxp_deduccion.rubro_deduccion')
                ->where('cxp_deduccion.cxp', $id)
                ->select(
                    'cxp_deduccion.*',
                    'rubro_deduccion_salario.nombre as rubro_nombre',
                    'rubro_deduccion_salario.descripcion as rubro_descripcion'
                )
                ->orderBy('cxp_deduccion.fecha_creacion', 'asc')
                ->get();

            $cxp->deducciones = $deducciones;

            return $this->responseAjaxSuccess("", $cxp);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al obtener detalle de CxP: " . $ex->getMessage(), []);
        }
    }

    /**
     * Aprueba y marca como pagadas múltiples CxP
     */
    public function aprobarYPagarCxPAjax(Request $request)
    {
        try {
            $cxpIds = $request->input('cxp_ids');
            $observaciones = $request->input('observaciones', '');
            $tipoPagoId = $request->input('tipo_pago');
            $numComprobante = $request->input('num_comprobante', '');

            if (empty($cxpIds) || !is_array($cxpIds)) {
                return $this->responseAjaxServerError("No se seleccionaron CxP", []);
            }

            if (empty($tipoPagoId)) {
                return $this->responseAjaxServerError("Debe seleccionar un tipo de pago", []);
            }

            DB::beginTransaction();

            $estadoAprobada = $this->obtenerEstadoCxP('CXP_APROBADA');
            $estadoPagada = $this->obtenerEstadoCxP('CXP_PAGADA');
            $fechaActual = now();
            $usuarioId = session('usuario')['id'] ?? 1;

            // Obtener datos de las CxP para crear el gasto
            $cxps = DB::table('cxp')
                ->whereIn('id', $cxpIds)
                ->get();

            // Calcular totales y construir descripción
            $montoTotal = 0;
            $numerosCxP = [];
            $beneficiario = '';

            foreach ($cxps as $cxp) {
                $saldo = floatval($cxp->monto_total) - floatval($cxp->monto_pagado);
                $montoTotal += $saldo;
                $numerosCxP[] = $cxp->numero_cxp;
                if (empty($beneficiario)) {
                    $beneficiario = $cxp->beneficiario;
                }
            }

            // Descripción del gasto incluye los números de CxP
            $descripcionGasto = "Pago de CxP: " . implode(', ', $numerosCxP) . " - Beneficiario: " . $beneficiario;

            // Obtener sucursal del usuario
            $usuario = DB::table('usuario')->where('id', $usuarioId)->first();
            $sucursal = $usuario->sucursal ?? 1;

            // Obtener tipo de gasto para "Otros" o similar
            $tipoGasto = DB::table('sis_tipo')
                ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
                ->where('sis_clase.cod_general', 'GEN_GASTOS')
                ->where('sis_tipo.nombre', 'LIKE', '%Otro%')
                ->select('sis_tipo.id')
                ->first();

            if (!$tipoGasto) {
                // Si no existe, usar el primer tipo de gasto disponible
                $tipoGasto = DB::table('sis_tipo')
                    ->join('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
                    ->where('sis_clase.cod_general', 'GEN_GASTOS')
                    ->select('sis_tipo.id')
                    ->first();
            }

            $tipoGastoId = $tipoGasto ? $tipoGasto->id : 1;

            // Crear el gasto con el tipo de pago seleccionado por el usuario
            $estadoGasto = SisEstadoController::getIdEstadoByCodGeneral("EST_GASTO_APB");

            // Si no hay comprobante, usar los números de CxP
            $numeroFactura = !empty($numComprobante) ? $numComprobante : implode(', ', $numerosCxP);
           
            $idGasto = DB::table('gasto')->insertGetId([
                'id' => null,
                'monto' => $montoTotal,
                'descripcion' => $descripcionGasto,                    // CxP: CXP-001, CXP-002 - Beneficiario: Juan
                'num_factura' => $numeroFactura,                      // Número de comprobante o números de CxP
                'usuario' => $usuarioId,
                'proveedor' => null,
                'fecha' => $fechaActual,
                'tipo_pago' => $tipoPagoId,                           // Tipo de pago seleccionado por el usuario
                'tipo_gasto' => $tipoGastoId,                         // Tipo de gasto automático (Otros)
                'aprobado' => '',
                'observacion' => 'Pago de Cuentas por Pagar. ' . $observaciones,
                'sucursal' => $sucursal,
                'ingreso' => -1,
                'url_factura' => null,
                'estado' => $estadoGasto,
                'codigo_moneda' => 'CRC',
                'tipo_cambio' => 1.0000
            ]);

            // Actualizar las CxP con el gasto asociado
            foreach ($cxpIds as $cxpId) {
                $cxpActual = DB::table('cxp')->where('id', $cxpId)->first();
                $observacionesActuales = $cxpActual->observaciones ?? '';
                
                // Crear nueva observación
                $nuevaObservacion = $observacionesActuales . "\n\n[" . $fechaActual . "] Aprobada y pagada. Gasto #" . $idGasto . ". " . $observaciones;
                
                // Actualizar CxP a estado pagada
                DB::table('cxp')
                    ->where('id', $cxpId)
                    ->update([
                        'estado' => $estadoPagada,
                        'monto_pagado' => $cxpActual->monto_total,
                        'observaciones' => $nuevaObservacion,
                        'gasto' => $idGasto,
                        'usuario_aprobacion' => $usuarioId,
                        'fecha_aprobacion' => $fechaActual,
                        'fecha_modificacion' => $fechaActual
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("CxP aprobadas y marcadas como pagadas exitosamente. Gasto #" . $idGasto . " creado.", ['gasto_id' => $idGasto]);

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al aprobar y pagar CxP: " . $ex->getMessage(), []);
        }
    }

    /**
     * Rechaza múltiples CxP y sus bitácoras asociadas
     */
    public function rechazarCxPAjax(Request $request)
    {
        try {
            $cxpIds = $request->input('cxp_ids');
            $motivoRechazo = $request->input('motivo_rechazo', '');

            if (empty($cxpIds) || !is_array($cxpIds)) {
                return $this->responseAjaxServerError("No se seleccionaron CxP", []);
            }

            if (empty($motivoRechazo)) {
                return $this->responseAjaxServerError("Debe proporcionar un motivo de rechazo", []);
            }

            DB::beginTransaction();

            $estadoCancelada = $this->obtenerEstadoCxP('CXP_CANCELADA');
            $estadoBitacoraRechazada = $this->obtenerEstadoBitacora('BIT_PROY_RECHAZADA');
            $fechaActual = now();
            $usuarioId = session('usuario')['id'] ?? 1;

            foreach ($cxpIds as $cxpId) {
                // Obtener observaciones actuales
                $cxpActual = DB::table('cxp')->where('id', $cxpId)->first();
                $observacionesActuales = $cxpActual->observaciones ?? '';
                
                // Crear nueva observación
                $nuevaObservacion = $observacionesActuales . "\n\n[" . $fechaActual . "] Rechazada. Motivo: " . $motivoRechazo;
                
                // Actualizar CxP a estado cancelada
                DB::table('cxp')
                    ->where('id', $cxpId)
                    ->update([
                        'estado' => $estadoCancelada,
                        'observaciones' => $nuevaObservacion,
                        'usuario_aprobacion' => $usuarioId,
                        'fecha_aprobacion' => $fechaActual,
                        'fecha_modificacion' => $fechaActual
                    ]);

                // Rechazar bitácoras asociadas
                DB::table('bit_usuario_proyecto')
                    ->where('cxp', $cxpId)
                    ->update([
                        'estado' => $estadoBitacoraRechazada,
                        'observacion_rechazo' => $motivoRechazo,
                        'usuario_autoriza' => $usuarioId,
                        'fecha_autorizacion' => $fechaActual,
                        'cxp' => null // Desasociar la CxP
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("CxP rechazadas exitosamente. Las bitácoras asociadas han sido rechazadas", []);

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al rechazar CxP: " . $ex->getMessage(), []);
        }
    }
}