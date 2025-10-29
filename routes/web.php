<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Rutas INICIO
|--------------------------------------------------------------------------
*/

Route::group([], function () {
    Route::post('login', 'LogInController@logIn');
    Route::get('login', 'LogInController@index');
    Route::get('/', 'LogInController@index');
    Route::get('logOut', 'LogInController@logOut');
});


// Temporalmente sin middleware para debug
Route::group([], function () {
    Route::get('/perfil/usuario', 'PerfilUsuarioController@goPerfilUsuario');
    Route::post('/perfil/usuario/guardar', 'MantenimientoUsuariosController@guardarUsuarioPerfilAjax');
    Route::post('/perfil/usuario/seg', 'PerfilUsuarioController@cambiarContraPerfil');
    Route::post('mant/usuarios/cargarUsuario', 'MantenimientoUsuariosController@cargarUsuarioAjax');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de usuarios general
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mantUsu'], function () {
    Route::get('mant/usuarios', 'MantenimientoUsuariosController@index');
    Route::get( 'mant/usuarios/cargarUsuarios', 'MantenimientoUsuariosController@cargarUsuariosAjax');
    Route::post('mant/usuarios/usuario', 'MantenimientoUsuariosController@goEditarUsuario');
    Route::post('/mant/usuarios/usuario/guardar', 'MantenimientoUsuariosController@guardarUsuarioAjax');
    Route::post('/mant/usuarios/usuario/seg', 'MantenimientoUsuariosController@cambiarContra');
    Route::post('/mant/usuarios/usuario/inactivar', 'MantenimientoUsuariosController@inactivarUsuario');
    Route::post('/mant/usuarios/usuario/activar', 'MantenimientoUsuariosController@activarUsuario');
});

Route::group(['middleware' => 'autorizated:mantSuc'], function () {
    Route::get('mant/sucursales', 'MantenimientoSucursalController@index');
    Route::post('guardarsucursal', 'MantenimientoSucursalController@guardarSucursal');
    Route::post('eliminarsucursal', 'MantenimientoSucursalController@eliminarSucursal');
    Route::post('mant/sucursales/cargar', 'MantenimientoSucursalController@cargarSucursalAjax');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Parametros Generales
|--------------------------------------------------------------------------
| DESHABILITADO: Tabla parametros_generales no existe en esta versión
*/

// Route::group(['middleware' => 'autorizated:mantParGen'], function () {
//     Route::get('mant/parametrosgenerales', 'ParametrosGeneralesController@index');
//     Route::post('mant/guardarparametrosgenerales', 'ParametrosGeneralesController@guardar');
// });


/*
|--------------------------------------------------------------------------
| Mantenimiento de Gastos
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:gastTodos'], function () {
    Route::get('gastos/administracion', 'GastosController@goGastosAdmin');
    Route::post('gastos/administracion/filtro', 'GastosController@goGastosAdminFiltro');
    Route::post('gastos/gasto', 'GastosController@goGasto');
    Route::post('gasto/fotoBase64', 'GastosController@getFotoBase64');
});

Route::group(['middleware' => 'autorizated:gastNue'], function () {
    Route::get('gastos/nuevo', 'GastosController@goNuevoGasto');
    Route::post('gastos/guardar', 'GastosController@guardarGasto');
    Route::post('gastos/editar', 'GastosController@goEditarGasto');
    Route::post('gastos/eliminar', 'GastosController@eliminarGasto');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Ingresos CREACION
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:ingNue'], function () {
    Route::get('ingresos/nuevo', 'IngresosController@index');
    Route::post('ingresos/guardar', 'IngresosController@guardarIngreso');
    Route::get('ingresos/administracion', 'IngresosController@goIngresosAdmin');
    Route::post('ingresos/administracion/filtro', 'IngresosController@goIngresosAdminFiltro');
    Route::post('ingresos/ingreso', 'IngresosController@goIngreso');

    Route::post('ingresos/eliminar', 'IngresosController@eliminarIngreso');
    Route::post('ingresos/aprobar', 'IngresosController@aprobarIngreso');
    Route::post('ingresos/rechazar', 'IngresosController@rechazarIngreso');
    Route::post('ingresos/gastos/rechazar', 'IngresosController@rechazarIngresoGasto');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Ingresos ADMIN
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:ingTodos'], function () {
    Route::get('ingresos/administracion', 'IngresosController@goIngresosAdmin');
    Route::post('ingresos/administracion/filtro', 'IngresosController@goIngresosAdminFiltro');
    Route::post('ingresos/ingreso', 'IngresosController@goIngreso');
    Route::post('ingresos/eliminar', 'IngresosController@eliminarIngreso');
    Route::get('ingresos/pendientes', 'IngresosController@goIngresosPendientes');
});

/*
|--------------------------------------------------------------------------
|MANTENIMIENTO DE CLIENTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mantClientes'], function () {

    Route::get('mant/clientes', 'MantenimientoClientesController@index');
    Route::post('mant/clientes/guardar', 'MantenimientoClientesController@guardarCliente');
    Route::post('mant/clientes/guardar/eliminarcliente', 'MantenimientoClientesController@eliminarCliente');
    Route::post('mant/clientes/obtener-clientes-ajax', 'MantenimientoClientesController@obtenerClientesAjax');
    Route::post('mant/clientes/obtener-cliente', 'MantenimientoClientesController@obtenerCliente');
    Route::post('mant/clientes/obtener-info-fe-cliente', 'MantenimientoClientesController@obtenerInfoFECliente');
    Route::post('mant/clientes/guardar-info-fe-cliente', 'MantenimientoClientesController@guardarInfoFECliente');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Proyectos
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'autorizated:proyAsig,proyGen'], function () {
    Route::post('proyectos/cargarProyecto', 'ProyectosController@cargarProyectoAjax');
    Route::post('proyectos/cargarLineasPresupuesto', 'ProyectosController@cargarLineasPresupuestoAjax');
});

 Route::group(['middleware' => 'autorizated:proyGen'], function () {
    Route::get('proyectos/proyectos', 'ProyectosController@index');
    Route::post('proyectos/cargar', 'ProyectosController@cargarProyectosAjax');
   
    Route::post('proyectos/guardar', 'ProyectosController@guardarProyectoAjax');
    Route::post('proyectos/cargarClientes', 'ProyectosController@cargarClientesAjax');
    Route::post('proyectos/cargarUsuarios', 'ProyectosController@cargarUsuariosActivosAjax');
    Route::post('proyectos/cargarEstados', 'ProyectosController@cargarEstadosProyectoAjax');
    
    Route::post('proyectos/guardarLineaPresupuesto', 'ProyectosController@guardarLineaPresupuestoAjax');
    Route::post('proyectos/eliminarLineaPresupuesto', 'ProyectosController@eliminarLineaPresupuestoAjax');
    Route::post('proyectos/cargarBitacoraUsuario', 'ProyectosController@cargarBitacoraUsuarioAjax');
});
Route::group(['middleware' => 'autorizated:cotProy'], function () {
// Rutas para Cotización de Proyectos (temporalmente sin middleware para pruebas)
Route::get('proyectos/cotizacion_proyectos', 'ProyectosController@cotizacionProyectos');
Route::post('proyectos/cotizacion/cargar', 'ProyectosController@cargarCotizacionProyectosAjax');
Route::post('proyectos/cotizacion/cargarProyecto', 'ProyectosController@cargarCotizacionProyectoAjax');
Route::post('proyectos/cotizacion/guardar', 'ProyectosController@guardarCotizacionProyectoAjax');
Route::post('proyectos/cotizacion/cargarClientes', 'ProyectosController@cargarClientesAjax');
Route::post('proyectos/cotizacion/cargarEstados', 'ProyectosController@cargarEstadosCotizacionAjax');
Route::post('proyectos/cotizacion/cargarLineasPresupuesto', 'ProyectosController@cargarLineasPresupuestoAjax');
Route::post('proyectos/cotizacion/guardarLineaPresupuesto', 'ProyectosController@guardarLineaPresupuestoAjax');
Route::post('proyectos/cotizacion/eliminarLineaPresupuesto', 'ProyectosController@eliminarLineaPresupuestoAjax');
});

Route::group(['middleware' => 'autorizated:proyAsig'], function () {
    Route::get('proyectos/proyectos_asignados', 'ProyectosController@proyectosAsignados');
    Route::post('proyectos/cargarProyectosAsignados', 'ProyectosController@cargarProyectosAsignadosAjax');
    Route::post('proyectos/cargarRubros', 'ProyectosController@cargarRubrosAjax');
    Route::post('proyectos/cargarBitacoraUsuario', 'ProyectosController@cargarBitacoraUsuarioAjax');
    Route::post('proyectos/guardarBitacora', 'ProyectosController@guardarBitacoraAjax');
    Route::post('proyectos/eliminarBitacora', 'ProyectosController@eliminarBitacoraAjax');
    Route::post('proyectos/cambiarEstadoBitacora', 'ProyectosController@cambiarEstadoBitacoraAjax');
});

// Rutas para Autorización de Horas (proyAut)
Route::group(['middleware' => 'autorizated:proyAut'], function () {
    Route::get('proyectos/autorizar_horas', 'ProyectosController@autorizarHoras');
    Route::post('proyectos/cargarUsuariosBitacorasPendientes', 'ProyectosController@cargarUsuariosBitacorasPendientesAjax');
    Route::post('proyectos/cargarBitacorasAutorizacion', 'ProyectosController@cargarBitacorasAutorizacionAjax');
    Route::post('proyectos/autorizarBitacora', 'ProyectosController@cambiarEstadoBitacoraAjax');
    Route::post('proyectos/autorizarMultiplesBitacoras', 'ProyectosController@autorizarMultiplesBitacorasAjax');
});


/*** impuestos */
Route::get('mant/impuestos', 'MantenimientoImpuestosController@index');
Route::post('guardarimpuesto', 'MantenimientoImpuestosController@guardarImpuesto');
Route::post('eliminarimpuesto', 'MantenimientoImpuestosController@eliminarImpuesto');


Route::group(['middleware' => 'autorizated:mantRubExtSal'], function () {
    /*** Rubros Extra Salariales */
Route::get('mant/rubrosextrasalario', 'MantenimientoRubrosExtraSalarioController@index');
Route::post('mant/rubrosextrasalario/cargar', 'MantenimientoRubrosExtraSalarioController@cargarRubrosAjax');
Route::post('mant/rubrosextrasalario/guardar', 'MantenimientoRubrosExtraSalarioController@guardarRubroAjax');
Route::post('mant/rubrosextrasalario/eliminar', 'MantenimientoRubrosExtraSalarioController@eliminarRubroAjax');

});

Route::group(['middleware' => 'autorizated:mantRubDedSal'], function () {
/*** Rubros Deducción Salarial */
Route::get('mant/rubrosdeduccionsalario', 'MantenimientoRubrosDeduccionSalarioController@index');
Route::post('mant/rubrosdeduccionsalario/cargar', 'MantenimientoRubrosDeduccionSalarioController@cargarRubrosAjax');
Route::post('mant/rubrosdeduccionsalario/guardar', 'MantenimientoRubrosDeduccionSalarioController@guardarRubroAjax');
Route::post('mant/rubrosdeduccionsalario/eliminar', 'MantenimientoRubrosDeduccionSalarioController@eliminarRubroAjax');
});

/*** Cuentas por Pagar */
Route::group(['middleware' => 'autorizated:cxpIndex'], function () {
Route::get('cxp/index', 'CuentaPorPagarController@index');
Route::post('cxp/cargar', 'CuentaPorPagarController@cargarCxPAjax');
Route::post('cxp/crear', 'CuentaPorPagarController@crearCxPAjax');
Route::post('cxp/actualizar', 'CuentaPorPagarController@actualizarCxPAjax');
Route::post('cxp/detalle', 'CuentaPorPagarController@obtenerDetalleCxPAjax');
Route::post('cxp/aprobar-pagar', 'CuentaPorPagarController@aprobarYPagarCxPAjax');
Route::post('cxp/rechazar', 'CuentaPorPagarController@rechazarCxPAjax');
});

/*** Cuentas por Pagar */
Route::group(['middleware' => 'autorizated:cxpHistPagpProy'], function () {
    Route::get('cxp/historial_pagos_proyectos', 'CuentaPorPagarController@historialPagosProyectos');
    Route::post('cxp/historial-pagos-proyectos-ajax', 'CuentaPorPagarController@historialPagosProyectosAjax');
    Route::post('cxp/pagos-proyecto-ajax', 'CuentaPorPagarController@pagosProyectoAjax');
    Route::post('cxp/consumo-lineas-presupuesto-ajax', 'CuentaPorPagarController@consumoLineasPresupuestoAjax');
    Route::post('cxp/detalle-pago-proyecto-ajax', 'CuentaPorPagarController@detallePagoProyectoAjax');
    });

/*** Mantenimiento de Monedas */
Route::group(['middleware' => 'autorizated:mantMonedas'], function () {
Route::get('mant/monedas', 'MantenimientoMonedasController@index');
Route::post('mant/monedas/cargar', 'MantenimientoMonedasController@cargarMonedasAjax');
Route::post('mant/monedas/guardar', 'MantenimientoMonedasController@guardarMonedaAjax');
Route::post('mant/monedas/eliminar', 'MantenimientoMonedasController@eliminarMonedaAjax');
Route::post('mant/monedas/obtener', 'MantenimientoMonedasController@obtenerMonedaAjax');
});
/*** Proveedores */
Route::get('mant/proveedores', 'MantenimientoProveedorController@index');
Route::post('guardarproveedor', 'MantenimientoProveedorController@guardarProveedor');
Route::post('eliminarproveedor', 'MantenimientoProveedorController@eliminarProveedor');


/*** Bancos */
Route::get('mant/roles', 'MantenimientoRolesController@index');
Route::post('guardarrol', 'MantenimientoRolesController@guardarRol');
Route::post('eliminarrol', 'MantenimientoRolesController@eliminarRol');
Route::post('cargarPermisosRoles', 'MantenimientoRolesController@cargarPermisosRoles');


/*** Usuarios */
Route::get('restaurar_pc', 'MantenimientoUsuariosController@restaurarPc');
Route::get('tema_claro', 'MantenimientoUsuariosController@temaClaro');
Route::get('tema_oscuro', 'MantenimientoUsuariosController@temaOscuro');
Route::post('side_teme', 'MantenimientoUsuariosController@sideTeme');
Route::post('color_teme', 'MantenimientoUsuariosController@colorTeme');
Route::post('sticky', 'MantenimientoUsuariosController@sticky');



Route::get('inicio', function () {
    return view('inicio');
});

Route::get('usuario', function () {
    return view('mant.usuario');
});



/******************Informes ********************** */
Route::post('informes/resumencontable/filtro', 'InformesController@goResumenContableFiltro');
Route::get('informes/resumencontable', 'InformesController@goResumenContable');

