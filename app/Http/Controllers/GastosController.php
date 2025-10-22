<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\Storage;

class GastosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goNuevoGasto()
    {
        $data = [
            'datos' => []
        ];
        return view('gastos.registrar', compact('data'));
    }

    public function goEditarGasto(Request $request)
    {

        $id = $request->input('idGastoEditar');

        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        $data = [
            
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_gasto' => $this->getTiposGasto(),
            'tipos_pago' => $this->getTiposPago()
        ];
        return view('gastos.gasto', compact('data'));
    }

    public function goEditarGastoById(Request $request)
    {
        $id = $request->input('idGastoEditar');

        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }


        $tipo_gasto = $this->getTiposGasto();

        $data = [
            
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_gasto' => $tipo_gasto,
            'tipos_pago' => $this->getTiposPago()
        ];

        return view('gastos.editar', compact('data'));
    }

    public function goGasto(Request $request)
    {

        $id = $request->input('idGasto');

        $gasto = DB::table('gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->join('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
            ->select('gasto.*', 'usuario.usuario as nombreUsuario', 'sis_estado.nombre as estadoUsuario', 'sis_estado.cod_general as codEstadoUsuario')
            ->where('gasto.id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        if($gasto->sucursal != session("usuario")['sucursal']){
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        $gasto->fecha = $this->fechaFormat($gasto->fecha);

        $data = [
            
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_pago' => $this->getTiposPago(),
            'tipos_gasto' => $this->getTiposGasto()
        ];

        return view('gastos.gasto', compact('data'));
    }

    public function goGastoById($id)
    {

        $gasto = DB::table('gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->join('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
            ->select('gasto.*', 'usuario.usuario as nombreUsuario', 'sis_estado.nombre as estadoUsuario', 'sis_estado.cod_general as codEstadoUsuario')
            ->where('gasto.id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        $gasto->fecha = $this->fechaFormat($gasto->fecha);

        $data = [
            
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_pago' => $this->getTiposPago(),
            'tipos_gasto' => $this->getTiposGasto()
        ];

        return view('gastos.gasto', compact('data'));
    }

    public function goGastosAdmin()
    {
        $filtros = [];
        if (session("filtrosGastos") == null) {
            $filtros = [
                'proveedor' => 0,
                'sucursal' => 'T',
                'aprobado' => 'T',
                'select_estado' => 'T',
                'hasta' => "",
                'tipo_gasto' => "",
                'desde' => "",
            ];
            session(['filtrosGastos' =>  $filtros]);
          
        } else {
            return $this->goGastosAdminFiltro(new Request());
        }
        $data = [
            
            'totalGastos', 0,
            'gastos' => [],
            'filtros' => $filtros
        ];

        return view('gastos.gastosAdmin', compact('data'));
    }

    public function goGastosAdminFiltro(Request $request)
    {
       
        if ($request->getRequestUri() == "") {
           
            $filtros = session("filtrosGastos");
          
            $filtroProveedor = $filtros['proveedor'];
            $filtroSucursal = $filtros['sucursal'];
            $select_estado = isset($filtros['select_estado']) ? $filtros['select_estado'] : 'T';
            $hasta = $filtros['hasta'];
            $gasto = $filtros['tipo_gasto'];
            $desde = $filtros['desde'];
        } else {
            $filtroProveedor = $request->input('proveedor');
            $filtroSucursal = $request->input('sucursal');
            $select_estado = $request->input('select_estado');
            $gasto = $request->input('tipo_gasto');
            $hasta = $request->input('hasta');
            $desde = $request->input('desde');
        }
        $gastos =  DB::table('gasto')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->join('sis_tipo', 'sis_tipo.id', '=', 'gasto.tipo_gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->join('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
            ->join('sucursal', 'sucursal.id', '=', 'gasto.sucursal')
            ->select('gasto.*', 'sis_tipo.nombre as nombre_tipo_gasto', 'proveedor.nombre', 'usuario.usuario as nombreUsuario'
            ,'sucursal.descripcion as dscSucursal','sis_estado.nombre as dscEstado');

        if ($filtroProveedor >= 1  && !$this->isNull($filtroProveedor)) {
            $gastos = $gastos->where('gasto.proveedor', '=', $filtroProveedor);
        }

        if ($gasto >= 1  && !$this->isNull($gasto)) {
            $gastos = $gastos->where('gasto.tipo_gasto', '=', $gasto);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $gastos = $gastos->where('gasto.sucursal', '=', session('usuario')['sucursal']);
        }

        if (!$this->isNull($select_estado) && $select_estado != 'T') {
            $gastos = $gastos->where('gasto.estado', '=', $select_estado);
        }

        if (!$this->isNull($desde)) {
            $gastos = $gastos->where('gasto.fecha', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $gastos = $gastos->where('gasto.fecha', '<', $mod_date);
        }

        $gastos = $gastos->get();
        $totalGastos = 0;
        foreach ($gastos as $i) {
            $totalGastos = $totalGastos + $i->monto;
        }
       
        $filtros1 = [
            'proveedor' => $filtroProveedor,
            'sucursal' => $filtroSucursal,
            'select_estado' => $select_estado,
            'tipo_gasto' => $gasto,
            'hasta' => $hasta,
            'desde' => $desde,
        ];
        session(["filtrosGastos" => $filtros1]);
        
        $data = [
            
            'totalGastos' => $totalGastos,
            'gastos' => $gastos,
            'filtros' => $filtros1
        ];

        return view('gastos.gastosAdmin', compact('data'));
    }

    public function guardarGasto(Request $request)
    {
        $id = $request->input('id');
        if ($id < 1 || $this->isNull($id)) { // Nuevo usuario
            $actualizar = false;
        } else { // Editar usuario
            $actualizar = true;
        }

        if ($this->validarGasto($request)) {

            $tipo_documento = "F";
            $tipo_pago = $request->input('tipo_pago');

            $proveedor = $request->input('proveedor');
            $observacion = $request->input('observacion');
            $descripcion = $request->input('descripcion');
            $total = $request->input('total');
            $num_comprobante = $request->input('num_comprobante');
            $fecha_actual = date("Y-m-d H:i:s");
            $fecha = $request->input('fecha');
            $usuarioId = $this->getUsuarioAuth()['id'];
            $sucursal =  DB::table('sucursal')
                ->join('usuario', 'usuario.sucursal', '=', 'sucursal.id')
                ->select('sucursal.descripcion', 'sucursal.estado', 'sucursal.id')
                ->where('usuario.id', '=', $usuarioId)->get()->first();
            if ($sucursal == null || $sucursal->estado != 'A') {
                $this->setError('Guardar gasto', "La sucursal no existe o esta inactiva.");
                return $this->returnNuevoGastoWithData($request->all());
            }


            if ($fecha != null && $fecha != '') {
                $fecha_actual = $fecha;
            }
            $tipo_gasto = $request->input('tipo_gasto') ?? 1;

            $ingreso = -1;

            try {
                DB::beginTransaction();


                if ($actualizar) { // Editar gasto
                   
                    DB::table('gasto')
                        ->where('id', '=', $id)
                        ->update([
                            'monto' => $total, 'descripcion' => $descripcion, 'num_factura' => $num_comprobante,
                            'proveedor' => $proveedor,
                            'tipo_pago' => $tipo_pago, 'tipo_documento' => $tipo_documento, 'tipo_gasto' => $tipo_gasto,
                            'observacion' => $observacion, 'sucursal' => $sucursal->id
                        ]);
                } else { // Nuevo gasto
                    $id = DB::table('gasto')->insertGetId([
                        'id' => null, 'monto' => $total, 'descripcion' => $descripcion, 'num_factura' => $num_comprobante,
                        'usuario' => $usuarioId, 'proveedor' => $proveedor, 'fecha' => $fecha_actual,
                        'tipo_pago' => $tipo_pago, 'tipo_documento' => $tipo_documento, 'tipo_gasto' => $tipo_gasto,
                        'aprobado' =>'', 'observacion' => $observacion,  'sucursal' => $sucursal->id, 'ingreso' => $ingreso,
                        'url_factura' => null,'estado' => SisEstadoController::getIdEstadoByCodGeneral("EST_GASTO_APB")
                    ]);

                    $image = $request->file('foto_comprobante');

                    if ($image != null) {

                        $extension = $image->getClientOriginalExtension();
                        $nombreArchivo = 'gasto-' . $id . '.' . $extension;
                        $path = Storage::putFileAs('public/gastos', $image, $nombreArchivo, ['exists' => 'overwrite']);

                        $url_factura = asset('storage/gastos/' . $nombreArchivo);
                        DB::table('gasto')
                            ->where('id', '=', $id)->update(['url_factura' => $url_factura]);
                    }

                    $this->bitacoraMovimientos('gasto', 'nuevo', $id, $total, $fecha_actual);
                }

                DB::commit();


                if ($actualizar) { // Editar usuario
                    $this->setSuccess('Guardar gasto', 'Se actualizo el gasto correctamente.');
                    return $this->goGastoById($id);
                } else { // Nuevo usuario

                    $this->setSuccess('Guardar gasto', 'Gasto creado correctamente.');
                    return $this->goGastoById($id);
                }
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Gasto', 'Algo salío mal, reintentalo!');
             
                if ($actualizar) { // Editar usuario
                    return $this->goEditarGastoById($id);
                } else { // Nuevo usuario
                    return $this->returnNuevoGastoWithData($request->all());
                }
            }
        } else {
            if ($actualizar) { // Editar usuario
                return $this->goEditarGastoById($id);
            } else { // Nuevo usuario
                return $this->returnNuevoGastoWithData($request->all());
            }
        }
    }

    public function getFotoBase64(Request $request)
    {
        $id = $request->input('gasto');

        if ($id == null || $id < 1) {
            echo '-1';
            exit;
        }
        try {
            $gasto =  DB::table('gasto')->where('id', '=', $id)->get()->first();
            echo $gasto->url_factura;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo "-1"; // error en el proceso de bd
            exit;
        }
    }

    public function returnNuevoGastoWithData($datos)
    {

        $data = [
            
            'datos' => $datos,
            'tipos_gasto' =>  $this->getTiposGasto(),
            'tipos_pago' => $this->getTiposPago(),
            'proveedores' => $this->getProveedores()
        ];

        return view('gastos.registrar', compact('data'));
    }

    public function eliminarGasto(Request $request)
    {
        if (!$this->validarSesion("gastTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idGastoEliminar');
        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError('Eliminar gasto', "El gasto no existe.");
            return redirect('/');
        }


        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('id', '=', $id)->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("EST_GASTO_ELIMINADO")]);
            $this->bitacoraMovimientos('gasto', 'eliminar', $id, $gasto->monto);

            DB::commit();
            $this->setSuccess('Eliminar gasto', "El gasto se elimino correctamente.");
            return redirect('gastos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar gasto', "Algo salío mal, reintentalo.");
            return redirect('/');
        }
    }

    public function validarGasto(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

     

        if ($this->isNull($r->input('tipo_pago')) || $this->isEmpty($r->input('tipo_pago'))) {
            $requeridos .= " Tipo pago ";
            $valido = false;
            $esPrimero = false;
        }

        if ($this->isNull($r->input('total')) || $this->isEmpty($r->input('total'))) {
            $requeridos .= " Total ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('descripcion')) || $this->isEmpty($r->input('descripcion'))) {
            $requeridos .= " Descripción del gasto ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }


        if ($this->isNull($r->input('tipo_pago'))) {
            $this->setError('Error de integridad', "Tipo de pago invalido.");
            return false;
        }

        if (!$this->isLengthMinor($r->input('num_comprobante'), 50)) {
            $this->setError('Tamaño exedido', "El número de comprobante debe ser de máximo 50 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('descripcion'), 150)) {
            $this->setError('Tamaño exedido', "La descripción del gasto debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('observacion'), 150)) {
            $this->setError('Tamaño exedido', "La observación debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isNumber($r->input('total')) || $r->input('total') < 10) {
            $this->setError('Número incorrecto', "El total debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    }

}
