<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\Validator;

class IngresosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
        $data = [
            
            'datos' => [],
            'tipos_ingreso' => $this->getTiposIngreso()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }

    public function goIngreso(Request $request)
    {

        $id = $request->input('idIngreso');

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        
        $caja = CajaController::getByIdIngreso($id);

        if ($caja == null) {
            $efectivoReportado = null;
        }else{
            $efectivoReportado = $caja->efectivo_reportado ?? 0;
        }

        $ventas = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('orden.ingreso', '=', $id)->get();

        $tieneVentas = count($ventas) > 0;

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
        }

        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);

        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $ingreso->subtotal = $sinpe + $efectivo + $tarjeta;
        $ingreso->totalGeneral = $ingreso->subtotal;
        $ingreso->monto_tarjeta  = preg_replace('/\,/', '.', $ingreso->monto_tarjeta);
        $ingreso->monto_efectivo  = preg_replace('/\,/', '.', $ingreso->monto_efectivo);
        $ingreso->monto_sinpe  = preg_replace('/\,/', '.', $ingreso->monto_sinpe);

        $data = [
            
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneVentas' => $tieneVentas,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'efectivoReportado' => $efectivoReportado,
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST")
        ];

        return view('ingresos.ingreso.ingreso', compact('data'));
    }

    public function goIngresoById($id)
    {

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        
        $caja = CajaController::getByIdIngreso($id);

        if ($caja == null) {
            $efectivoReportado = null;
        }else{
            $efectivoReportado = $caja->efectivo_reportado ?? 0;
        }

        $ventas = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('orden.ingreso', '=', $id)->get();

        $tieneVentas = count($ventas) > 0;

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
        }

        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);

        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $ingreso->subtotal = $sinpe + $efectivo + $tarjeta;
        $ingreso->totalGeneral = $ingreso->subtotal;
        $ingreso->monto_tarjeta  = preg_replace('/\,/', '.', $ingreso->monto_tarjeta);
        $ingreso->monto_efectivo  = preg_replace('/\,/', '.', $ingreso->monto_efectivo);
        $ingreso->monto_sinpe  = preg_replace('/\,/', '.', $ingreso->monto_sinpe);

        $data = [
            
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneVentas' => $tieneVentas,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'efectivoReportado' => $efectivoReportado,
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST")
        ];
        return view('ingresos.ingreso.ingreso', compact('data'));
    }


    public function goIngresosAdmin()
    {

        $filtros = [
            'sucursal' => 'T',
            'aprobado' => 'T',
            'hasta' => "",
            'tipo_ingreso' => "",
            'desde' => "",
        ];

        if (session("filtrosIngresos") == null) {
            session(['filtrosIngresos' =>  $filtros]);
        } else {
            $filtros = session("filtrosIngresos");
            return $this->goIngresosAdminFiltro(new Request());
        }


        $data = [
            
            'ingresos' => [],
            'filtros' => $filtros,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'tipos_ingreso' => $this->getTiposIngreso(),
            'sucursales' => $this->getSucursales(),
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST")
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosAdminFiltro(Request $request)
    {
        if ($request->getRequestUri() == "") {
            $filtros = session("filtrosIngresos");
            $filtroSucursal = $filtros['sucursal'];
            $filtroAprobado = $filtros['aprobado'];
            $ingreso = $filtros['tipo_ingreso'];
            $hasta = $filtros['hasta'];
            $desde = $filtros['desde'];
        } else {
            $filtroSucursal = $request->input('sucursal');
            $filtroAprobado = $request->input('aprobado');
            $ingreso = $request->input('tipo_ingreso');
            $hasta = $request->input('hasta');
            $desde = $request->input('desde');
        }


        $ingresos =  DB::table('ingreso')
            ->leftjoin('sis_tipo', 'sis_tipo.id', '=', 'ingreso.tipo')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'sucursal.descripcion as nombreSucursal', 'sis_tipo.nombre as nombre_tipo_ingreso', 'usuario.usuario as nombreUsuario', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general');

        if ($ingreso >= 1  && !$this->isNull($ingreso)) {
            $ingresos = $ingresos->where('ingreso.tipo', '=', $ingreso);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ingresos = $ingresos->where('ingreso.sucursal', 'like', '%' . $filtroSucursal . '%');
        }

        if (!$this->isNull($filtroAprobado) && $filtroAprobado != 'T') {

            $ingresos = $ingresos->where('ingreso.estado', '=', $filtroAprobado);
        }

        if (!$this->isNull($desde)) {
            $ingresos = $ingresos->where('ingreso.fecha', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ingresos = $ingresos->where('ingreso.fecha', '<', $mod_date);
        }

        $ingresos = $ingresos->get();
        $totalIngresos = 0;
        foreach ($ingresos as $i) {

            $sinpe = $i->monto_sinpe ?? 0;
            $efectivo = $i->monto_efectivo ?? 0;
            $tarjeta = $i->monto_tarjeta ?? 0;
            $i->total = $sinpe + $efectivo + $tarjeta;
            $totalIngresos = $totalIngresos + $i->total;
            $i->fecha = $this->fechaFormat($i->fecha);
        }

        $filtros1 = [
            'sucursal' => $filtroSucursal,
            'aprobado' => $filtroAprobado,
            'tipo_ingreso' => $ingreso,
            'hasta' => $hasta,
            'desde' => $desde,
        ];

        session(['filtrosIngresos' =>  $filtros1]);
        $data = [
            
            'totalIngresos' => $totalIngresos,
            'ingresos' => $ingresos,
            'filtros' => $filtros1,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'sucursales' => $this->getSucursales(),
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST")
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosPendientes()
    {

        $ingresosSinAprobar =  DB::table('ingreso')
            ->join('sis_tipo', 'sis_tipo.id', '=', 'ingreso.tipo')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->select(
                'ingreso.id',
                'ingreso.fecha',
                'ingreso.monto_sinpe',
                'ingreso.monto_efectivo',
                'ingreso.monto_tarjeta',
                'ingreso.descripcion',
                'usuario.usuario as nombreUsuario',
                'sis_tipo.nombre as tipoIngreso'
            )
            ->where('ingreso.estado', '=', SisEstadoController::getIdEstadoByCodGeneral("ING_PEND_APB"))->orderby('ingreso.id', 'desc')->get();

        foreach ($ingresosSinAprobar as $i) {
            $caja = CajaController::getByIdIngreso($i->id);
            if($caja != null){
                $efectivo = $caja->efectivo_reportado ?? 0;
            }else{
                $efectivo = $i->monto_efectivo ?? 0;
            }
            $sinpe = $i->monto_sinpe ?? 0;
            $tarjeta = $i->monto_tarjeta ?? 0;
            $i->subTotal = $sinpe + $efectivo + $tarjeta;
            $i->total = $i->subTotal;
            $i->fecha = $this->fechaFormat($i->fecha);
        }

        $data = [
            
            'ingresosSinAprobar' => $ingresosSinAprobar
        ];

        return view('ingresos.ingresosPendientes', compact('data'));
    }



    public function returnNuevoIngresoWithData($datos)
    {

        $data = [
            
            'datos' => $datos,
            'tipos_ingreso' => $this->getTiposIngreso()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }



    /**
     * Guarda o actualiza un ingreso
     */
    public function guardarIngreso(Request $request)
    {

        $id = $request->input('id');

        if ($id < 1 || $this->isNull($id)) { // Nuevo ingreso
            $actualizar = false;
        } else { // editar ingreso
            $actualizar = true;
            $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

            if ($ingreso == null) {
                $this->setError('Guardar Ingreso', 'El ingreso a editar no existe!');
                return redirect('/');
            }
        }
        if ($this->validarIngreso($request)) {

            $monto_efectivo = $request->input('monto_efectivo') ?? 0;
            $monto_sinpe = $request->input('monto_sinpe') ?? 0;
            $monto_tarjeta = $request->input('monto_tarjeta') ?? 0;
            $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;
            $idUsuario = $this->getUsuarioAuth()['id'];
            $observacion = $request->input('observacion');
            $tipo_ingreso = $request->input('tipo_ingreso');
            $sucursal = $this->getSucursalUsuario();
            $descripcion = $request->input('descripcion');
            $cliente = $request->input('cliente');
            $fecha = $request->input('fecha');
            $cliente = ($cliente == "null") ? null : $cliente;
            $fecha_actual = date("Y-m-d H:i:s");
            $estado = SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO");

            try {
                DB::beginTransaction();
                if ($actualizar) {
                    DB::table('ingreso')->where('id', '=', $id)->update([
                        'monto_efectivo' => $monto_efectivo,
                        'monto_tarjeta' => $monto_tarjeta,
                        'monto_sinpe' => $monto_sinpe,
                        'observacion' => $observacion
                    ]);
                    $this->bitacoraMovimientos('ingreso', 'editar', $id, $total);
                } else {
                    $idIngreso = DB::table('ingreso')->insertGetId([
                        'id' => null,
                        'monto_efectivo' => $monto_efectivo,
                        'monto_tarjeta' => $monto_tarjeta,
                        'monto_sinpe' => $monto_sinpe,
                        'usuario' => $idUsuario,
                        'fecha' => $fecha_actual,
                        'tipo' => $tipo_ingreso,
                        'observacion' => $observacion,
                        'sucursal' => $sucursal,
                        'estado' => $estado,
                        'descripcion' => $descripcion
                    ]);
                    $this->bitacoraMovimientos('ingreso', 'nuevo', $idIngreso, $total, $fecha_actual);
                }

                DB::commit();
                $this->setSuccess('Guardar Ingreso', 'Se guardo el ingreso correctamente.');
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->goIngresoById($idIngreso);
                }
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Ingreso', 'Algo salío mal, reintentalo!');
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->returnNuevoIngresoWithData($request->all());
                }
            }
        } else {
            if ($actualizar) {
                return $this->goIngresoById($id);
            } else {
                return $this->returnNuevoIngresoWithData($request->all());
            }
        }
    }

    public function guardarIngresoArr(
        $monto_efectivo,
        $monto_sinpe,
        $monto_tarjeta,
        $observacion,
        $tipo_ingreso,
        $descripcion,
        $cliente = null,
        $fecha = null,
        $idSucursal = null,
        $doc_referencia = null
    ) {
        // Validación manual (puedes adaptar este código según tus necesidades de validación)
        if ($this->validarIngresoArr(compact('monto_efectivo', 'monto_sinpe', 'monto_tarjeta', 'observacion', 'tipo_ingreso', 'descripcion', 'cliente', 'fecha'))) {

            $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;
            $idUsuario = $this->getUsuarioAuth()['id'];
            $fecha_actual = $fecha ?? date("Y-m-d H:i:s");
            $estado = SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO");

            try {

                // Crear el nuevo ingreso
                $idIngreso = DB::table('ingreso')->insertGetId([
                    'monto_efectivo' => $monto_efectivo ?? 0,
                    'monto_tarjeta' => $monto_tarjeta ?? 0,
                    'monto_sinpe' => $monto_sinpe ?? 0,
                    'usuario' => $idUsuario,
                    'fecha' => $fecha_actual,
                    'tipo' => $tipo_ingreso,
                    'observacion' => $observacion,
                    'sucursal' => $idSucursal,
                    'estado' => $estado,
                    'descripcion' => $descripcion,
                    'cliente' => $cliente,
                    'doc_referencia' => $doc_referencia
                ]);

                // Registrar el movimiento en la bitácora
                $this->bitacoraMovimientos('ingreso', 'nuevo', $idIngreso, $total, $fecha_actual);

                return response()->json([
                    'estado' => true,
                    'mensaje' => 'Ingreso guardado correctamente.',
                    'datos' => $idIngreso
                ], 200);
            } catch (QueryException $ex) {
                DB::rollBack();
                DB::table('log')->insertGetId(['id' => null, 'documento' => 'IngresosController', 'descripcion' => $ex]);
                return response()->json([
                    'estado' => false,
                    'mensaje' => 'Algo salió mal, reinténtalo.',
                    'error' => $ex->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Validación fallida.',
                'errores' => $this->validarIngresoArr(compact('monto_efectivo', 'monto_sinpe', 'monto_tarjeta', 'observacion', 'tipo_ingreso', 'descripcion', 'cliente', 'fecha'))
            ], 422);
        }
    }

    public function validarIngresoArr(array $data)
    {
        // Realiza la validación usando el array $data
        $rules = [
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_sinpe' => 'required|numeric|min:0',
            'monto_tarjeta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'tipo_ingreso' => 'required|string',
            'descripcion' => 'nullable|string',
            'cliente' => 'nullable|integer',
            'fecha' => 'nullable|date',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return null; // Null si la validación fue exitosa
    }

    public function validarIngreso(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
        $monto_efectivo = $r->input('monto_efectivo') ?? 0;
        $monto_sinpe = $r->input('monto_sinpe') ?? 0;
        $monto_tarjeta = $r->input('monto_tarjeta') ?? 0;
        $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;

        if ($this->isNull($r->input('descripcion')) || $this->isEmpty($r->input('descripcion'))) {
            $requeridos .= " Descripción del ingreso ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if ($this->isNull($r->input('tipo_ingreso'))) {
            $this->setError('Error de integridad', "Tipo de ingreso invalido.");
            return false;
        }

        if (!$this->isLengthMinor($r->input('descripcion'), 300)) {
            $this->setError('Tamaño exedido', "La descripción del gasto debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('observacion'), 150)) {
            $this->setError('Tamaño exedido', "La observación debe ser de máximo 150 caracteres.");
            return false;
        }
        if ($total < 10) {
            $this->setError('Número incorrecto', "El total debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    }

    public function aprobarIngreso(Request $request)
    {
        $id = $request->input('idIngreso');
        $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

        if ($ingreso == null) {
            return $this->responseAjaxServerError("El ingreso no existe.", []);
        }

        $sinpe = $request->input('pago_sinpe');
        $efectivo = $request->input('pago_efectivo');
        $tarjeta = $request->input('pago_tarjeta');
        $total = $sinpe + $efectivo + $tarjeta;


        try {
            DB::beginTransaction();

          

            DB::table('ingreso')
                ->where('id', '=', $id)->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO"),'monto_tarjeta' =>  $tarjeta,'monto_sinpe' => $sinpe,'monto_efectivo' =>  $efectivo]);

            $this->bitacoraMovimientos('ingreso', 'Aprobar', $id, $total);

            DB::commit();
            $this->setSuccess("Aprobando ingreso", "Se aprobó el ingreso correctamente");
            return $this->responseAjaxSuccess("El ingreso se aprobo correctamente.",[]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal, reintentalo.", []);
        }
    }

}
