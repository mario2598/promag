<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class InformesController extends Controller
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

    public function goResumenContable()
    {
        if (!$this->validarSesion("informes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
        ];


        $data = [
            
            'filtros' => $filtros,
            'sucursales' => $this->getSucursales()
        ];

        return view('informes.resumenContable', compact('data'));
    }

    public function goResumenContableFiltro(Request $request)
    {
        if (!$this->validarSesion("informes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }


        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
        ];

        $data = [
            
            'resumen' => $this->resumenContable($desde, $hasta, $filtroSucursal),
            'sucursales' => $this->getSucursales(),
            'filtros' => $filtros
        ];
        return view('informes.resumenContable', compact('data'));
    }

    public function goVentaGenProductos()
    {
        if (!$this->validarSesion("ventaGenProductos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'horaDesdeFiltro' => "",
            'filtroTipoProd' => "",
            'horaHastaFiltro' => ""
        ];


        $data = [
            
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.ventasGenProductos', compact('data'));
    }

    public function goMovInvProductoExterno()
    {
        if (!$this->validarSesion("movInvProductoExterno")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'descUsuario' => ""
        ];


        $data = [
            
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.movInvProductoExterno', compact('data'));
    }

    public function goMovInvProductoExternoFiltro(Request $request)
    {
        if (!$this->validarSesion("movInvProductoExterno")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $filtroDescUsuario = $request->input('descUsuario');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT usu.nombre as nombreUsuario,suc.descripcion as nombreSucursal, " .
            "usu.usuario,inv.fecha,pe.nombre as nombreProducto,inv.detalle,inv.cantidad_anterior,inv.cantidad_ajustada,inv.cantidad_nueva " .
            "FROM bit_inv_producto_externo inv join  usuario usu on usu.id = inv.usuario " .
            "join producto_externo pe on pe.id = inv.producto " .
            "join sucursal suc on suc.id = inv.sucursal ";
        $where = " where 1 = 1 ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtroDescUsuario != ''  && !$this->isNull($filtroDescUsuario)) {
            $where .= " and  ( UPPER(usu.usuario) like UPPER('%" . $filtroDescUsuario . "%') or UPPER(usu.nombre) like UPPER('%" . $filtroDescUsuario . "%'))";
        }

        $query .= $where . " order by inv.fecha DESC";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'descUsuario' => $filtroDescUsuario
        ];
        $datos = DB::select($query);
        foreach ($datos as $d) {
            $d->fecha = $this->fechaFormat($d->fecha);
        }
        $data = [
            
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.movInvProductoExterno', compact('data'));
    }

    public function goMovConMateriaPrima()
    {
        if (!$this->validarSesion("movConMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'descUsuario' => ""
        ];


        $data = [
            
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.movConMateriaPrima', compact('data'));
    }

    public function goMovConMateriaPrimaFiltro(Request $request)
    {
        if (!$this->validarSesion("movConMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $filtroDescUsuario = $request->input('descUsuario');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT usu.nombre as nombreUsuario,suc.descripcion as nombreSucursal, " .
            "usu.usuario,inv.fecha,pe.nombre as nombreProducto,inv.detalle,inv.cantidad_anterior,inv.cantidad_ajuste,inv.cantidad_nueva,pe.unidad_medida " .
            "FROM bit_materia_prima inv join  usuario usu on usu.id = inv.usuario " .
            "join materia_prima pe on pe.id = inv.materia_prima join sucursal suc on suc.id = inv.sucursal ";
        $where = " where 1 = 1 ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtroDescUsuario != ''  && !$this->isNull($filtroDescUsuario)) {
            $where .= " and  ( UPPER(usu.usuario) like UPPER('%" . $filtroDescUsuario . "%') or UPPER(usu.nombre) like UPPER('%" . $filtroDescUsuario . "%'))";
        }

        $query .= $where . " order by inv.fecha DESC";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'descUsuario' => $filtroDescUsuario
        ];
        $datos = DB::select($query);
        foreach ($datos as $d) {
            $d->fecha = $this->fechaFormat($d->fecha);
        }
        $data = [
            
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.movConMateriaPrima', compact('data'));
    }

    public function goConMateriaPrima()
    {
        if (!$this->validarSesion("conMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
        ];


        $data = [
            
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.conMateriaPrima', compact('data'));
    }

    public function goConMateriaPrimaFiltro(Request $request)
    {
        if (!$this->validarSesion("conMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT suc.descripcion as nombreSucursal,pe.nombre as nombreProducto,pe.unidad_medida,sum(inv.cantidad_ajuste) as suma,pe.precio as precio_unidad, (sum(inv.cantidad_ajuste) * pe.precio) as costo " .
            "FROM bit_materia_prima inv join  usuario usu on usu.id = inv.usuario " .
            "join materia_prima pe on pe.id = inv.materia_prima join sucursal suc on suc.id = inv.sucursal ";
        $where = " where inv.cantidad_anterior > inv.cantidad_nueva ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }


        $query .= $where . " group by suc.descripcion,pe.nombre,pe.unidad_medida,pe.precio";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd
        ];
        $datos = DB::select($query);

        $data = [
            
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.conMateriaPrima', compact('data'));
    }


    public function goVentaGenProductosFiltro(Request $request)
    {
        if (!$this->validarSesion("ventaGenProductos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroTipoProd = $request->input('filtroTipoProd');
        $filtroDescProd = $request->input('descProd');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $horaHasta = $request->input('horaHastaFiltro');
        $horaDesde = $request->input('horaDesdeFiltro');

        $query = "SELECT do.nombre_producto PRODUCTO" .
            ",suc.descripcion as SUCURSAL, " .
            "sum(do.cantidad) " .
            "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta," .
            " case do.tipo_producto when 'E' then 'Externo' else  'Cafetería'  end as tipo_producto FROM detalle_orden " .
            " do join orden o on do.orden = o.id " .
            " join usuario usu on usu.id = o.cajero " .
            " left join sucursal suc on suc.id = o.sucursal " .
            " left join cliente cli on cli.id = o.cliente ";
        $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='" . $filtroTipoProd . "' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%" . $filtroDescProd . "%')";
        }


        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= " . $horaHasta;
        }


        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= " . $horaDesde;
        }

        $query .= $where . " group by do.nombre_producto,suc.descripcion,do.precio_unidad,do.tipo_producto order by 3 DESC";

        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'horaDesdeFiltro' => $horaDesde,
            'filtroTipoProd' => $filtroTipoProd,
            'horaHastaFiltro' => $horaHasta
        ];

        $data = [
            
            'datosReporte' => DB::select($query),
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.ventasGenProductos', compact('data'));
    }

    public function goVentaXhora()
    {
        if (!$this->validarSesion("ventaXhora")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'cliente' => 0,
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'nombreUsu' => "",
            'horaDesdeFiltro' => "",
            'filtroTipoProd' => "",
            'horaHastaFiltro' => ""
        ];


        $data = [
            
            'clientes' => $this->getClientes(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.ventasXhora', compact('data'));
    }

    public function goVentaXhoraFiltro(Request $request)
    {
        if (!$this->validarSesion("ventaXhora")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroCliente = $request->input('cliente');
        $filtroSucursal = $request->input('sucursal');
        $filtroTipoProd = $request->input('filtroTipoProd');
        $filtroDescProd = $request->input('descProd');
        $filtronombreUsu = $request->input('nombreUsu');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $horaHasta = $request->input('horaHastaFiltro');
        $horaDesde = $request->input('horaDesdeFiltro');

        $query = "SELECT DATE_FORMAT(o.fecha_inicio, '%Y-%m-%d') FECHA, DATE_FORMAT(o.fecha_inicio, '%h %p') HORA ,do.nombre_producto PRODUCTO, usu.usuario AS " .
            "USUARIO,NVL(cli.nombre,'') as CLIENTE,suc.descripcion as SUCURSAL,HOUR(o.fecha_inicio) as HORAFILTRO, " .
            "sum(do.cantidad) " .
            "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta," .
            " case do.tipo_producto when 'E' then 'Externo' else  'Propio'  end as tipo_producto FROM detalle_orden " .
            " do join orden o on do.orden = o.id " .
            " join usuario usu on usu.id = o.cajero " .
            " left join sucursal suc on suc.id = o.sucursal " .
            " left join cliente cli on cli.id = o.cliente ";
        $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $where .= " and cli.id =" . $filtroCliente;
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='" . $filtroTipoProd . "' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtronombreUsu != ''  && !$this->isNull($filtronombreUsu)) {
            $where .= " and  UPPER(usu.usuario) like UPPER('%" . $filtronombreUsu . "%')";
        }

        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= " . $horaHasta;
        }


        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= " . $horaDesde;
        }

        $query .= $where . " group by do.nombre_producto,DATE_FORMAT(o.fecha_inicio, '%Y-%m-%d'),DATE_FORMAT(o.fecha_inicio, '%h %p'),usu.usuario,NVL(cli.nombre,''),suc.descripcion,HOUR(o.fecha_inicio),do.precio_unidad,do.tipo_producto order by 1 DESC,2 ASC,7 ASC";

        $filtros = [
            'cliente' => $filtroCliente,
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'nombreUsu' => $filtronombreUsu,
            'horaDesdeFiltro' => $horaDesde,
            'filtroTipoProd' => $filtroTipoProd,
            'horaHastaFiltro' => $horaHasta
        ];

        $data = [
            
            'clientes' => $this->getClientes(),
            'datosReporte' => DB::select($query),
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas()
        ];

        return view('informes.ventasXhora', compact('data'));
    }
}
