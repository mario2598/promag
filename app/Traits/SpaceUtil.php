<?php

namespace App\Traits;

use App\Http\Controllers\ComandasController;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait SpaceUtil
{

  /**
   * trae la imagen en base 64 del servidor
   * @params Ruta en servidor 
   * @returns archivo en base 64
   */
  public function getContentsOrDefault($url)
  {
    $probarV2 = false;
    $cont = null;
    try {
      $cont = file_get_contents($url);
    } catch (\Throwable $th) {
      $probarV2 = true;
    }

    if ($probarV2) {
      try {
        $url = str_replace('el_amanecer', 'el_amanecer-v2', $url);
        $cont = file_get_contents($url);
      } catch (\Throwable $th) {
        $cont = file_get_contents(public_path() . '/offer_default.png');
      }
    }
    return base64_encode($cont);
  }


  /**
   * Registra un info en la variable de session
   * @return 
   */
  public function setInfo($titulo, $error)
  {
    session(['info' => [
      'mostrar' => true,
      'titulo' =>  $titulo,
      'descripcion' => $error
    ]]);
  }
  /**
   * Valida null
   * @return boolean
   */
  public function isNull($objeto)
  {
    return ($objeto == null) ? true : false;
  }

  /**
   * Valida si el string no sobre pasa el limite
   * @param $objeto string a evaluar
   * @param $tam , tamaño maximo del string
   * @return boolean
   */
  public function isLengthMinor($objeto, $tam)
  {
    return (strlen($objeto) > $tam) ? false : true;
  }

  /**
   * Valida si el string existe en el array enviado
   * @param $objeto string a evaluar
   * @param $array , array de valores permitidis
   * @return boolean
   */
  public function isIn($objeto, $array)
  {
    $encontrado = false;
    foreach ($array as $i) {
      if ($objeto === $i) {
        $encontrado = true;
      }
    }
    return $encontrado;
  }

  /**
   * Trae los tipos de gasto
   * Los activos "A"
   * @return datos tipos de gasto
   */
  public function getTiposGasto()
  {
    return DB::table('sis_tipo')
      ->where('cod_general', '=', 'GEN_GASTOS')
      ->get();
  }

  /**
   * Trae los tipos de gasto
   * Los activos "A"
   * @return datos tipos de gasto
   */
  public function getTiposMovimiento()
  {
    return DB::table('tipo_movimiento')
      ->get();
  }

  /**
   * Trae las categorias
   * Los activos "A"
   * @return datos categorias
   */
  public function getCategorias()
  {
    $categorias = DB::table('categoria')
      ->where('estado', '=', 'A')->orderBy('posicion_menu', 'asc')
      ->get();

    foreach ($categorias as $c) {
      $c->url_imagen = asset('storage/' . $c->url_imagen);
    }
    return $categorias;
  }



  /**
   * Trae los impuestos
   * Los activos "A"
   * @return datos impuestos
   */
  public function getImpuestos()
  {
    return DB::table('impuesto')
      ->where('estado', '=', 'A')
      ->get();
  }


  /**
   * Trae los tipos de ingreso
   * Los activos "A"
   * @return datos tipos de ingreso
   */
  public function getTiposIngreso()
  {
    return DB::table('sis_tipo')
      ->where('clase', '=', 1)
      ->get();
  }

  /**
   * Trae losn parámetros generales de la entidad
   *
   * @return datos
   */
  public function getParametrosGenerales()
  {
    // Tabla parametros_generales no existe en esta versión
    // return DB::table('parametros_generales')
    //   ->get()->first();
    
    // Retornar valores por defecto
    return (object)[
      'porcentaje_banco' => 2.5, // 2.5% por defecto
    ];
  }

  /**
   * Trae los clientes
   * Los activos "A"
   * @return datos clientes
   */
  public function getClientes()
  {
    return DB::table('cliente')
      ->where('estado', '=', 'A')
      ->get();
  }


  /**
   * Trae los tipos de pago
   * Los activos "A"
   * @return datos tipos de pago
   */
  public function getTiposPago()
  {
    return DB::table('sis_tipo')
      ->where('cod_general', '=', 'GEN_TIPOS_PAGOS')
      ->get();
  }

  /**
   * Trae los tipos de pago
   * Los activos "A"
   * @return datos tipos de pago
   */
  public function getTiposGastoRolUsuario()
  {
    return  DB::table('usuario')
      ->join('rol', 'rol.id', '=', 'usuario.rol')
      ->select('rol.tipo_gasto')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first()->tipo_gasto ?? 1;
  }

  /**
   * Trae la sucursal del usuario
   
   * @return datos tipos de ingreso
   */
  public function getSucursalUsuario()
  {
    return  DB::table('usuario')
      ->select('usuario.sucursal')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first()->sucursal ?? 1;
  }



  public function getTipoIngresoRolUsuario()
  {
    return  DB::table('usuario')
      ->join('rol', 'rol.id', '=', 'usuario.rol')
      ->select('rol.tipo_ingreso')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first()->tipo_ingreso ?? 1;
  }


  /**
   * Obtiene las sucursales activas sin bodega

   * @return sucursales
   */
  public static function getSucursales()
  {
    return DB::table('sucursal')->where('estado', 'like', 'A')->get();
  }



  /**
   * Obtiene las sucursales activas sin bodega

   * @return sucursales
   */
  public function getSucursalesAndBodegas()
  {
    return DB::table('sucursal')->where('estado', 'like', 'A')->get();
  }

  public static function getSucursalesAll()
  {
    return DB::table('sucursal')->get();
  }

  public static function getSucursalesActivas()
  {
    return DB::table('sucursal')->where('estado', 'like', 'A')->get();
  }


  /**
   * Obtiene las sucursales activas que son bodega

   * @return sucursales
   */
  public function getBodegas()
  {
    return DB::table('sucursal')->where('estado', 'like', 'A')->where('bodega', '=', 'S')->get();
  }

  /**
   * Obtiene las sucursales activas que son bodega

   * @return sucursales
   */
  public function getRestaurantesSucursal($sucursal)
  {
    return DB::table('restaurante')->where('sucursal', '=', $sucursal)->where('estado', '=', 'A')->get();
  }

  /**
   * Obtiene las sucursales activas que son bodega

   * @return sucursales
   */
  public function getImpresoraCaja()
  {
    $suc = DB::table('sucursal')->select('nombre_impresora')->where('id', '=', $this->getSucursalUsuario())->get()->first();
    if ($suc == null) {
      return '';
    } else {
      return $suc->nombre_impresora;
    }
  }


  /**
   * Obtiene las vistas disponibles en el sistema
   * @return vistas
   */
  public function cargarVistas()
  {
    $headers = DB::table('vista')
      ->where('vista.tipo', '=', 'G')
      ->where('vista.inactivo', '=', '0')
      ->select('vista.*')
      ->orderBy('vista.peso_general', 'ASC')
      ->orderBy('vista.orden', 'ASC')
      ->get();

    $menus = DB::table('vista')
      ->where('vista.tipo', '=', 'M')
      ->where('vista.inactivo', '=', '0')
      ->select('vista.*')
      ->orderBy('vista.peso_general', 'ASC')
      ->orderBy('vista.orden', 'ASC')
      ->get();



    foreach ($headers as $h) {
      $submenus = [];
      foreach ($menus as $m) {
        if ($m->codigo_grupo == $h->codigo_grupo) {
          array_push($submenus, $m);
        }
        $h->submenus = $submenus;
      }
    }
    return $headers;
  }

  /**
   * Valida si el codigo de barra ya esta registrado
   * @param $codigo barra
   * @return boolean si esta registrado true si no false
   */
  public function codigoBarraRegistrado($codigo)
  {
    $producto_menu = DB::table('producto_menu')->select('producto_menu.id')->where('producto_menu.codigo', '=', $codigo)->get()->first();

    if ($producto_menu != null) {
      return true;
    }

    $producto_externo = DB::table('producto_externo')->select('producto_externo.id')->where('producto_externo.codigo_barra', '=', $codigo)->get()->first();

    if ($producto_externo != null) {
      return true;
    }
    return false;
  }

  /**
   * Obtiene los menus por usuario
   * @return menus
   */
  public static function cargarMenus()
  {
    $usuarioSession = session('usuario');

    if ($usuarioSession == null) {
      session(['usuario' => null]);
      return false;
    }

    $usuario = DB::table('usuario')
      ->where('usuario.id', '=', $usuarioSession['id'])
      ->get()->first();



    if ($usuario == null) {
      session(['usuario' => null]);
      return false;
    }

    $headers = DB::table('menu')
      ->leftJoin('vista', 'vista.id', '=', 'menu.vista')
      ->where('menu.rol', '=', $usuario->rol)
      ->where('vista.tipo', '=', 'G')
      ->where('vista.inactivo', '=', 0)
      ->select('vista.*')
      ->orderBy('vista.peso_general', 'ASC')
      ->orderBy('vista.orden', 'ASC')
      ->get();
    //dd($usuario->rol);
    $menus = DB::table('menu')
      ->leftJoin('vista', 'vista.id', '=', 'menu.vista')
      ->where('menu.rol', '=', $usuario->rol)
      ->where('vista.tipo', '=', 'M')
      ->where('vista.inactivo', '=', 0)
      ->select('vista.*')
      ->orderBy('vista.peso_general', 'ASC')
      ->orderBy('vista.orden', 'ASC')
      ->get();

    //dd($headers);

    foreach ($headers as $h) {
      $submenus = [];
      foreach ($menus as $m) {
        if ($m->codigo_grupo == $h->codigo_grupo) {
          array_push($submenus, $m);
        }
        $h->submenus = $submenus;
      }
     
    }

    return $headers;
  }

  /**
   * Obtiene los productos activos con su impuesto y categoria

   * @return sucursales
   */
  public function getProductos()
  {
    return DB::table('producto')
      ->leftJoin('categoria', 'categoria.id', '=', 'producto.categoria')
      ->leftJoin('impuesto', 'impuesto.id', '=', 'producto.impuesto')
      ->select('producto.*', 'impuesto.impuesto as porcentaje_impuesto', 'categoria.categoria as nombre_categoria')
      ->where('producto.estado', '=', 'A')->get();
  }

  public function getSucursalUsuarioAuth()
  {
    return  DB::table('usuario')->where('id', '=', $this->getUsuarioAuth()['id'])->get()->first()->sucursal;
  }

  /**
   * Obtiene las proveedores activas

   * @return proveedores
   */
  public static function getProveedores()
  {
    return DB::table('proveedor')->where('estado', 'like', 'A')->get();
  }

  /**
   * Valida si el string cumple con el minimo de caracteress
   * @param $objeto string a evaluar
   * @param $tam , tamaño minimo del string
   * @return boolean
   */
  public function isLengthMayor($objeto, $tam)
  {
    return (strlen($objeto) < $tam) ? false : true;
  }

  public function getRoles()
  {
    return DB::table('rol')->where('estado', 'like', 'A')->get();
  }


  /**
   * Valida empty
   * @return boolean
   */
  public function isEmpty($objeto)
  {
    return (empty($objeto)) ? true : false;
  }

  /**
   * Valida si es numero o no
   * @return boolean
   */
  public function isNumber($objeto)
  {
    return (is_nan($objeto)) ? false : true;
  }

  /**
   * Limpiar los errores
   * @return 
   */
  public function cleanError()
  {
    session(['error' => null]);
  }

  /**
   * Registra un error en la variable de session
   * @return 
   */
  public function setError($titulo, $error)
  {
    session(['error' => [
      'mostrar' => true,
      'titulo' => $titulo,
      'descripcion' => $error
    ]]);
  }

  /**
   * Registra un success en la variable de session
   * @return 
   */
  public function setSuccess($titulo, $error)
  {
    session(['success' => [
      'mostrar' => true,
      'titulo' =>  $titulo,
      'descripcion' => $error
    ]]);
  }

  /**
   * Limpia los success
   * @return 
   */
  public function cleanSuccess()
  {
    session(['success' => null]);
  }

  public function setMsjSeguridad()
  {
    $this->setError("Seguridad", "No tienes permisos para ingresar..");
  }

  private function setAjaxResponse($codigo = 500, $mensaje = "", $datos = "", $estado = false)
  {
    return [
      "codigo" => $codigo,
      "mensaje" => $mensaje,
      "datos" => $datos,
      "estado" => $estado
    ];
  }

  public function responseAjaxServerError($mensaje = "", $datos = "")
  {
    return $this->setAjaxResponse(500, $mensaje, $datos, false);
  }

  public function responseAjaxSuccess($mensaje = "", $datos = "")
  {
    return $this->setAjaxResponse(200, $mensaje, $datos, true);
  }



  public function usuarioAdministrador()
  {
    return true;
    /*if (!session()->has('usuario')) {
      session(['usuario' => null]);
      $this->setError("Seguridad", "No tienes permisos para ingresar..");
      return redirect('/');
    }

    $usuario = DB::table('usuario')
      ->join('rol', 'rol.id', '=', 'usuario.rol')
      ->select('rol.administrador')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();

    if ($usuario == null) {
      session(['usuario' => null]);
      $this->setError("Seguridad", "No tienes permisos para ingresar..");
      return false;
    }

    session(['rol' => ($usuario->administrador == 'S') ? "admin" : "normal"]);

    return ($usuario->administrador == 'S') ? true : false;*/
  }

  public function getRestauranteUsuario()
  {
    $usuario = DB::table('usuario')
      ->leftjoin('restaurante', 'restaurante.sucursal', '=', 'usuario.sucursal')
      ->select('restaurante.id as restaurante')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();

    return $usuario->restaurante;
  }

  public function usuarioCierraCaja()
  {

    $usuario = DB::table('usuario')
      ->join('rol', 'rol.id', '=', 'usuario.rol')
      ->select('rol.cierra_caja')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();

    if ($usuario == null) {
      session(['usuario' => null]);
      $this->setError("Seguridad", "No tienes permisos para ingresar..");
      return false;
    }

    return ($usuario->cierra_caja == 'S') ? true : false;
  }

  public function getUsuarioAuth()
  {
    return session('usuario');
  }

  public function getUsuarioSucursal()
  {
    $usuario = DB::table('usuario')
      ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
      ->select('sucursal.id')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();
    return $usuario->id;
  }

  public function getUsuarioRestaurante()
  {
    $usuario = DB::table('usuario')
      ->join('restaurante', 'restaurante.sucursal', '=', 'usuario.sucursal')
      ->select('restaurante.id')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();


    return $usuario == null ? null : $usuario->id;
  }

  public function fechaFormat($fecha)
  {
    // Crea una instancia de Carbon a partir de la fecha
    $carbonDate = Carbon::parse($fecha);

    // Establece la localización en español
    $carbonDate->setLocale('es');

    // Formatea la fecha en español
    $fechaAux = $carbonDate->isoFormat('ddd D [de] MMMM');
    $fechaAux = ucfirst($fechaAux);
    $phpdate = strtotime($fecha);
    return str_replace('.', '', $fechaAux) . " " . date("g:i a", $phpdate);
  }

  public static function soloFechaFormat($fecha)
  {
    // Crea una instancia de Carbon a partir de la fecha
    $carbonDate = Carbon::parse($fecha);

    // Establece la localización en español
    $carbonDate->setLocale('es');

    // Formatea la fecha en español
    $fechaAux = $carbonDate->isoFormat('ddd D [de] MMMM');
    $fechaAux = ucfirst($fechaAux);
    $phpdate = strtotime($fecha);
    return str_replace('.', '', $fechaAux);
  }

  /**
   * Limpia los success
   * @return 
   */
  public static function getPanelConfiguraciones()
  {
    $usuario = session('usuario');
    return DB::table('panel_configuraciones')->where('usuario', '=', $usuario['id'])->get()->first();
  }

  /**
   * Genera en la bitacora los incios de session
   * @return 
   */
  public function bitacoraInicioSesion($usuario, $estado)
  {
    $fecha_actual = date("Y-m-d H:i:s");
    DB::table('bitacora_inicio_sesion')->insert(['id' => null, 'usuario' => $usuario, 'fecha' => $fecha_actual, 'estado' => $estado]);
    DB::commit();
  }

  /**
   * Genera en la bitacora los movimientos
   * @return 
   */
  public function bitacoraMovimientos($tabla, $tipo, $idEntidad, $total, $fecha = null)
  {
    if ($fecha != null && $fecha != '') {
      $fecha_actual = $fecha;
    } else {
      $fecha_actual = date("Y-m-d H:i:s");
    }

    $usuario = DB::table('usuario')
      ->select('usuario.usuario')
      ->where('usuario.id', '=', session('usuario')['id'])
      ->get()->first();


    DB::table('bitacora_modificacion')->insert([
      'id' => null,
      'usuario' => $usuario->usuario,
      'total' => $total,
      'id_entidad' => $idEntidad,
      'fecha' => $fecha_actual,
      'tabla' => $tabla,
      'tipo' => $tipo
    ]);
  }

  /**
   * Devuelve el total de ingresos del mes, cantidad de ingresos y el promedio
   */
  public function totalIngresosMes($fecha = null, $tipo_ingreso = null)
  {

    if ($fecha == null) {
      $desde =  date("Y-m-01 00:00:00");
      $hasta =  date("Y-m-t 23:59:59");
    } else {
      $desde =  date("Y-m-01 00:00:00", strtotime($fecha));
      $hasta =  date("Y-m-t 23:59:59", strtotime($fecha));
    }


    $ingresos = DB::table('ingreso')
      ->select('ingreso.*')
      ->where('ingreso.fecha', '>=', $desde)
      ->where('ingreso.fecha', '<=', $hasta)
      ->where('ingreso.aprobado', '=', 'S');

    if ($tipo_ingreso != null) {

      $ingresos =  $ingresos->where('ingreso.tipo', '=', $tipo_ingreso);
    }
    $ingresos = $ingresos->get();
    // dd($ingresos);

    $totalGeneral = 0;

    foreach ($ingresos as $i) {
      $sinpe = $i->monto_sinpe ?? 0;
      $efectivo = $i->monto_efectivo ?? 0;
      $tarjeta = $i->monto_tarjeta ?? 0;
      $totalAux = $sinpe + $efectivo + $tarjeta;
      $totalGeneral = $totalAux + $totalGeneral;
    }

    if (count($ingresos) > 0) {
      $avg = $totalGeneral / count($ingresos);
    } else {
      $avg = 0;
    }

    $data = [
      'promedio' => $avg,
      'total' => $totalGeneral,
      'cantidad' => count($ingresos)
    ];

    return $data;
  }

  public function resumenContableMesActual()
  {
    $desde =  date("Y-m-01");
    $hasta =  date("Y-m-t");
    return $this->resumenContable($desde, $hasta);
  }


  /**
   * Devuelve el total de ingresos del mes, cantidad de ingresos y el promedio
   * Con desglose por moneda
   */
  public function resumenContable($desde = null, $hasta = null, $sucursal = null)
  {
    $ingresos = DB::table('ingreso')
      ->join('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
      ->where('sis_estado.cod_general', '=', 'ING_EST_APROBADO');

    $gastos = DB::table('gasto')
      ->join('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
      ->where('sis_estado.cod_general', '!=', "EST_GASTO_ELIMINADO"); // Cambiado != en vez de =

    if ($sucursal != null && $sucursal != '' && $sucursal != 'T') {
      $ingresos = $ingresos->where('ingreso.sucursal', '=', $sucursal);
      $nombreSucursal =  DB::table('sucursal')->where('id', '=', $sucursal)->get()->first()->descripcion;
      $gastos = $gastos->where('gasto.sucursal', 'like', '%' . $nombreSucursal . '%');
    }

    if ($desde != null && $desde != '') {
      $desde = date('Y-m-d 00:00:00', strtotime($desde));
      $ingresos = $ingresos->where('ingreso.fecha', '>=', $desde);
      $gastos = $gastos->where('gasto.fecha', '>=', $desde);
    }
    if ($hasta != null && $hasta != '') {
      $hasta = date('Y-m-d 23:59:59', strtotime($hasta));
      $ingresos = $ingresos->where('ingreso.fecha', '<=', $hasta);
      $gastos = $gastos->where('gasto.fecha', '<=', $hasta);
    }


    // Tabla parametros_generales no existe - usar valor por defecto
    // $parametros = DB::table('parametros_generales')->get()->first();
    // $porcentaje_banco = $parametros->porcentaje_banco / 100;
    $porcentaje_banco = 2.5 / 100; // 2.5% por defecto
    $totalPagoTarjeta = 0;


    $ingresos = $ingresos->get();
    
    // Obtener gastos con su moneda
    $gastosQuery = DB::table('gasto')
      ->join('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
      ->where('sis_estado.cod_general', '!=', "EST_GASTO_ELIMINADO");

    if ($sucursal != null && $sucursal != '' && $sucursal != 'T') {
      $nombreSucursal =  DB::table('sucursal')->where('id', '=', $sucursal)->get()->first()->descripcion;
      $gastosQuery = $gastosQuery->where('gasto.sucursal', 'like', '%' . $nombreSucursal . '%');
    }

    if ($desde != null && $desde != '') {
      $desde_calc = date('Y-m-d 00:00:00', strtotime($desde));
      $gastosQuery = $gastosQuery->where('gasto.fecha', '>=', $desde_calc);
    }
    if ($hasta != null && $hasta != '') {
      $hasta_calc = date('Y-m-d 23:59:59', strtotime($hasta));
      $gastosQuery = $gastosQuery->where('gasto.fecha', '<=', $hasta_calc);
    }

    $gastosDetalle = $gastosQuery->select('gasto.monto', 'gasto.codigo_moneda', 'gasto.tipo_cambio')->get();

    // Totales en CRC
    $totalIngresos = 0;
    $totalIngresosEfectivo = 0;
    $totalIngresosTarjeta = 0;
    $totalIngresosSinpe = 0;

    // Desglose por moneda - Ingresos
    $resumenMonedas = [];

    foreach ($ingresos as $i) {
      $codigoMoneda = $i->codigo_moneda ?? 'CRC';
      $tipoCambio = floatval($i->tipo_cambio ?? 1.0);

      // Calcular montos en moneda original
      $sinpe = floatval($i->monto_sinpe ?? 0);
      $efectivo = floatval($i->monto_efectivo ?? 0);
      $tarjeta = floatval($i->monto_tarjeta ?? 0);
      
      $total = $sinpe + $efectivo + $tarjeta;

      // Convertir a CRC para totales generales
      $sinpeCRC = $sinpe * $tipoCambio;
      $efectivoCRC = $efectivo * $tipoCambio;
      $tarjetaCRC = $tarjeta * $tipoCambio;
      $totalCRC = $total * $tipoCambio;

      $porcentaje_cobro_tarjeta_aux = $tarjetaCRC * $porcentaje_banco;

      // Acumular en totales en CRC
      $totalIngresosEfectivo += $efectivoCRC;
      $totalIngresosTarjeta += $tarjetaCRC;
      $totalIngresosSinpe += $sinpeCRC;
      $totalPagoTarjeta += $porcentaje_cobro_tarjeta_aux;
      $totalIngresos += $totalCRC;

      // Acumular por moneda
      if (!isset($resumenMonedas[$codigoMoneda])) {
        $resumenMonedas[$codigoMoneda] = [
          'codigo_moneda' => $codigoMoneda,
          'total_efectivo' => 0,
          'total_tarjeta' => 0,
          'total_sinpe' => 0,
          'total' => 0,
          'total_efectivo_crc' => 0,
          'total_tarjeta_crc' => 0,
          'total_sinpe_crc' => 0,
          'total_crc' => 0,
        ];
      }

      $resumenMonedas[$codigoMoneda]['total_efectivo'] += $efectivo;
      $resumenMonedas[$codigoMoneda]['total_tarjeta'] += $tarjeta;
      $resumenMonedas[$codigoMoneda]['total_sinpe'] += $sinpe;
      $resumenMonedas[$codigoMoneda]['total'] += $total;
      $resumenMonedas[$codigoMoneda]['total_efectivo_crc'] += $efectivoCRC;
      $resumenMonedas[$codigoMoneda]['total_tarjeta_crc'] += $tarjetaCRC;
      $resumenMonedas[$codigoMoneda]['total_sinpe_crc'] += $sinpeCRC;
      $resumenMonedas[$codigoMoneda]['total_crc'] += $totalCRC;
    }

    // Desglose por moneda - Gastos
    $gastosGeneral = 0;
    $resumenGastosPorMoneda = [];

    foreach ($gastosDetalle as $gasto) {
      $codigoMoneda = $gasto->codigo_moneda ?? 'CRC';
      $tipoCambio = floatval($gasto->tipo_cambio ?? 1.0);
      $monto = floatval($gasto->monto);
      $montoCRC = $monto * $tipoCambio;

      $gastosGeneral += $montoCRC;

      if (!isset($resumenGastosPorMoneda[$codigoMoneda])) {
        $resumenGastosPorMoneda[$codigoMoneda] = [
          'codigo_moneda' => $codigoMoneda,
          'total' => 0,
          'total_crc' => 0,
        ];
      }

      $resumenGastosPorMoneda[$codigoMoneda]['total'] += $monto;
      $resumenGastosPorMoneda[$codigoMoneda]['total_crc'] += $montoCRC;
    }

    $mesActual = date("M");

    $subTotalFondos = $totalIngresos;
    $totalFondos = $subTotalFondos - $gastosGeneral;
    $totalFondos = $totalFondos - $totalPagoTarjeta;

    //Totales en conjunto
    $totalIngresosEfectivoGeneral = $totalIngresosEfectivo;
    $totalIngresosTarjetaGeneral = $totalIngresosTarjeta;
    $totalIngresosSinpeGeneral = $totalIngresosSinpe;
    $totalPagoTarjetaGeneral = $totalPagoTarjeta;

    $subTotalFondosGeneral = $subTotalFondos;
    $subTotalFondosGeneral = $subTotalFondosGeneral - $totalPagoTarjeta;
    $totalFondosGeneral = $totalFondos;

    $resumen = [
      'totalIngressosTarjeta' => $totalIngresosTarjeta,
      'mesActual' => $mesActual,
      'totalIngresosSinpe' => $totalIngresosSinpe,
      'totalPagoTarjeta' => $totalPagoTarjeta,
      'totalIngressosTarjeta' => $totalIngresosTarjeta,
      'ingresosTarjetaImpuestoAplicado' => $totalIngresosTarjeta - $totalPagoTarjeta,
      'totalIngresosEfectivo' => $totalIngresosEfectivo,
      'ingresosGeneralTarjetaImpuestoAplicado' => $totalIngresosTarjetaGeneral - $totalPagoTarjetaGeneral,
      'totalIngresosSinpeGeneral' => $totalIngresosSinpeGeneral,
      'totalIngresosEfectivoGeneral' => $totalIngresosEfectivoGeneral,
      'totalIngresosTarjetaGeneral' => $totalIngresosTarjetaGeneral,
      'totalPagoTarjetaGeneral' => $totalPagoTarjetaGeneral,
      'gastos' => $gastosGeneral,
      'gastosGeneral' => $gastosGeneral,
      'subTotalFondos' => $subTotalFondos,
      'totalFondosGeneral' => $totalFondosGeneral,
      'subTotalFondosGeneral' => $subTotalFondosGeneral,
      'totalFondos' => $totalFondos,
      'ingresos' => $totalIngresos,
      // Nuevos datos por moneda
      'resumenMonedas' => array_values($resumenMonedas),
      'resumenGastosPorMoneda' => array_values($resumenGastosPorMoneda),
    ];
    
    return $resumen;
  }
}
