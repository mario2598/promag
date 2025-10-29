var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
let proyectosData = [];

$(document).ready(function() {
    cargarProyectos();
});

/**
 * Cargar proyectos con su historial de pagos
 */
function cargarProyectos() {
    $.ajax({
        url: `${base_path}/cxp/historial-pagos-proyectos-ajax`,
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN
        },
        success: function(response) {
            if (response.estado) {
                proyectosData = response.datos.data;
                renderizarProyectos(response.datos.data);
                actualizarResumen(response.datos.resumen);
            } else {
                iziToast.error({
                    title: 'Error',
                    message: response.mensaje || 'Error al cargar proyectos',
                    position: 'topRight'
                });
            }
        },
        error: function(xhr) {
            console.error('Error al cargar proyectos:', xhr);
            iziToast.error({
                title: 'Error',
                message: 'Error al cargar el historial de pagos',
                position: 'topRight'
            });
        }
    });
}

/**
 * Renderizar tabla de proyectos agrupados
 */
function renderizarProyectos(proyectos) {
    let tbody = $('#tbody_proyectos');
    tbody.empty();

    if (!proyectos || proyectos.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="4" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay proyectos con pagos registrados</p>
                </td>
            </tr>
        `);
        return;
    }

    proyectos.forEach((proyecto) => {
        let numPagos = parseInt(proyecto.num_pagos || 0);
        let totalPagado = parseFloat(proyecto.total_pagado || 0);

        tbody.append(`
            <tr>
                <td>
                    <strong><i class="fas fa-project-diagram text-primary"></i> ${proyecto.proyecto_nombre}</strong>
                </td>
                <td class="text-center">
                    <span class="badge badge-info badge-pago">${numPagos} pago(s)</span>
                </td>
                <td class="text-right">
                    <strong class="text-success">₡${totalPagado.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary" onclick="verPagosProyecto(${proyecto.proyecto_id}, '${proyecto.proyecto_nombre.replace(/'/g, "\\'")}')" title="Ver pagos">
                        <i class="fas fa-eye"></i> Ver Pagos
                    </button>
                </td>
            </tr>
        `);
    });

    // Inicializar DataTables si no existe
    if (!$.fn.DataTable.isDataTable('#tabla_proyectos')) {
        $('#tabla_proyectos').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ proyectos",
                "infoEmpty": "Mostrando 0 a 0 de 0 proyectos",
                "infoFiltered": "(filtrado de _MAX_ proyectos totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ proyectos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar proyecto:",
                "zeroRecords": "No se encontraron proyectos coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            order: [[2, 'desc']], // Ordenar por total pagado
            pageLength: 10
        });
    }
}

/**
 * Actualizar tarjetas de resumen
 */
function actualizarResumen(resumen) {
    if (!resumen) return;

    $('#total_pagos').text(resumen.total_pagos || 0);
    $('#monto_total').text('₡' + parseFloat(resumen.monto_total || 0).toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#proyectos_con_pagos').text(resumen.proyectos_con_pagos || 0);
}

/**
 * Ver pagos de un proyecto específico
 */
function verPagosProyecto(proyectoId, proyectoNombre) {
    // Mostrar el modal primero
    $('#modal_pagos_proyecto').modal('show');
    
    // Cargar pagos del proyecto
    $.ajax({
        url: `${base_path}/cxp/pagos-proyecto-ajax`,
        type: 'post',
        data: { 
            proyecto_id: proyectoId,
            _token: CSRF_TOKEN
        },
        dataType: 'json',
        success: function(response) {
            if (response.estado) {
                mostrarPagosProyecto(proyectoNombre, response.datos.data, response.datos.resumen);
                // Cargar consumo por líneas
                cargarConsumoLineasPresupuesto(proyectoId);
            } else {
                iziToast.error({
                    title: 'Error',
                    message: response.mensaje || 'Error al cargar pagos del proyecto',
                    position: 'topRight'
                });
            }
        },
        error: function(xhr) {
            console.error('Error al cargar pagos:', xhr);
            iziToast.error({
                title: 'Error',
                message: 'Error al cargar los pagos del proyecto',
                position: 'topRight'
            });
        }
    });
}

/**
 * Cargar consumo por líneas de presupuesto
 */
function cargarConsumoLineasPresupuesto(proyectoId) {
    $.ajax({
        url: `${base_path}/cxp/consumo-lineas-presupuesto-ajax`,
        type: 'post',
        data: {
            proyecto_id: proyectoId,
            _token: CSRF_TOKEN
        },
        dataType: 'json',
        success: function(response) {
            if (response.estado) {
                renderizarLineasPresupuesto(response.datos.data);
            } else {
                $('#tbody_lineas_presupuesto').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${response.mensaje || 'Error al cargar líneas'}
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar consumo por líneas:', xhr);
            $('#tbody_lineas_presupuesto').html(`
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar el consumo por líneas
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Renderizar tabla de líneas de presupuesto con consumo
 */
function renderizarLineasPresupuesto(lineas) {
    let tbody = $('#tbody_lineas_presupuesto');
    tbody.empty();

    if (!lineas || lineas.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="fas fa-inbox"></i> No hay líneas de presupuesto para este proyecto
                </td>
            </tr>
        `);
        return;
    }

    let totalAutorizado = 0;
    let totalConsumido = 0;
    let totalPendiente = 0;

    lineas.forEach((linea) => {
        let montoAutorizado = parseFloat(linea.monto_autorizado || 0);
        let montoConsumido = parseFloat(linea.monto_consumido || 0);
        let montoPendiente = parseFloat(linea.monto_pendiente || 0);
        let montoDisponible = parseFloat(linea.monto_disponible || 0);
        let porcentajeConsumido = parseFloat(linea.porcentaje_consumido || 0);

        totalAutorizado += montoAutorizado;
        totalConsumido += montoConsumido;
        totalPendiente += montoPendiente;

        // Determinar clase de fila según el porcentaje consumido
        let claseFila = '';
        if (porcentajeConsumido >= 100) {
            claseFila = 'bg-danger text-white';
        } else if (porcentajeConsumido >= 80) {
            claseFila = 'bg-warning';
        } else if (porcentajeConsumido >= 50) {
            claseFila = 'bg-light';
        }

        // Badge de porcentaje
        let badgePorcentaje = '';
        if (porcentajeConsumido >= 100) {
            badgePorcentaje = `<span class="badge badge-danger">${porcentajeConsumido.toFixed(2)}%</span>`;
        } else if (porcentajeConsumido >= 80) {
            badgePorcentaje = `<span class="badge badge-warning">${porcentajeConsumido.toFixed(2)}%</span>`;
        } else {
            badgePorcentaje = `<span class="badge badge-info">${porcentajeConsumido.toFixed(2)}%</span>`;
        }

        // Indicador de disponibilidad
        let claseDisponible = montoDisponible >= 0 ? 'text-success' : 'text-danger';
        let iconoDisponible = montoDisponible >= 0 ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>';

        tbody.append(`
            <tr class="${claseFila}">
                <td class="text-center"><strong>#${linea.numero_linea}</strong></td>
                <td>${linea.descripcion || '-'}</td>
                <td class="text-right">
                    <strong>₡${montoAutorizado.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                </td>
                <td class="text-right bg-success text-white">
                    <strong>₡${montoConsumido.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                    ${linea.num_bitacoras_aprobadas > 0 ? `<br><small>(${linea.num_bitacoras_aprobadas} bitácoras)</small>` : ''}
                </td>
                <td class="text-right bg-warning">
                    <strong>₡${montoPendiente.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                    ${linea.num_bitacoras_pendientes > 0 ? `<br><small>(${linea.num_bitacoras_pendientes} bitácoras)</small>` : ''}
                </td>
                <td class="text-right ${claseDisponible}">
                    <strong>${iconoDisponible} ₡${montoDisponible.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                </td>
                <td class="text-center">${badgePorcentaje}</td>
            </tr>
        `);
    });

    // Agregar fila de totales
    let totalDisponible = totalAutorizado - totalConsumido;
    let porcentajeTotal = totalAutorizado > 0 ? (totalConsumido / totalAutorizado) * 100 : 0;

    tbody.append(`
        <tr class="bg-light font-weight-bold">
            <td colspan="2" class="text-right"><strong>TOTALES:</strong></td>
            <td class="text-right"><strong>₡${totalAutorizado.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
            <td class="text-right bg-success text-white"><strong>₡${totalConsumido.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
            <td class="text-right bg-warning"><strong>₡${totalPendiente.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
            <td class="text-right ${totalDisponible >= 0 ? 'text-success' : 'text-danger'}">
                <strong>₡${totalDisponible.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
            </td>
            <td class="text-center">
                <strong>${porcentajeTotal.toFixed(2)}%</strong>
            </td>
        </tr>
    `);
}

/**
 * Mostrar modal con pagos del proyecto
 */
function mostrarPagosProyecto(proyectoNombre, pagos, resumen) {
    $('#nombre_proyecto_modal').text(proyectoNombre);
    
    // Actualizar resumen del modal
    $('#modal_total_pagos').text(resumen.total_pagos || 0);
    $('#modal_monto_total').text('₡' + parseFloat(resumen.monto_total || 0).toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#modal_beneficiarios').text(resumen.beneficiarios || 0);

    // Destruir DataTable si existe
    if ($.fn.DataTable.isDataTable('#tabla_pagos_proyecto')) {
        $('#tabla_pagos_proyecto').DataTable().clear().destroy();
    }

    // Renderizar tabla de pagos
    let tbody = $('#tbody_pagos_proyecto');
    tbody.empty();

    if (!pagos || pagos.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center">
                    <i class="fas fa-inbox"></i> No hay pagos registrados para este proyecto
                </td>
            </tr>
        `);
        $('#modal_footer_total').text('₡0.00');
    } else {
        let totalGeneral = 0;

        pagos.forEach((pago, index) => {
            let monto = parseFloat(pago.monto || 0);
            let montoCRC = monto * parseFloat(pago.tipo_cambio || 1);
            totalGeneral += montoCRC;

            let fechaPago = formatearFecha(pago.fecha);
            let numComprobante = pago.num_factura || 'N/A';
            let beneficiario = pago.beneficiarios || 'N/A';
            let numCxps = pago.num_cxps || 0;

            tbody.append(`
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${fechaPago}</td>
                    <td>${beneficiario}</td>
                    <td>${numComprobante}</td>
                    <td class="text-center">
                        <span class="badge badge-info badge-pago">${numCxps} CxP(s)</span>
                    </td>
                    <td class="text-right">₡${montoCRC.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" onclick="verDetallePago(${pago.id})" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#modal_footer_total').text('₡' + totalGeneral.toLocaleString('es-CR', {minimumFractionDigits: 2}));
    }

    // Inicializar DataTables
    $('#tabla_pagos_proyecto').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ pagos",
            "infoEmpty": "Mostrando 0 a 0 de 0 pagos",
            "infoFiltered": "(filtrado de _MAX_ pagos totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ pagos",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron pagos coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        order: [[1, 'desc']], // Ordenar por fecha descendente
        pageLength: 10,
        paging: true,
        searching: true,
        info: true
    });

    // El modal ya está abierto, no lo abrimos de nuevo
}

/**
 * Ver detalle de un pago individual
 */
function verDetallePago(gastoId) {
    // Ocultar el modal de pagos del proyecto temporalmente
    $('#modal_pagos_proyecto').modal('hide');

    $.ajax({
        url: `${base_path}/cxp/detalle-pago-proyecto-ajax`,
        type: 'post',
        data: { 
            gasto_id: gastoId,
            _token: CSRF_TOKEN
        },
        dataType: 'json',
        success: function(response) {
            if (response.estado) {
                mostrarDetallePago(response.datos);
            } else {
                iziToast.error({
                    title: 'Error',
                    message: response.mensaje || 'Error al cargar detalle',
                    position: 'topRight'
                });
                // Volver a mostrar el modal de pagos
                $('#modal_pagos_proyecto').modal('show');
            }
        },
        error: function(xhr) {
            console.error('Error al cargar detalle:', xhr);
            iziToast.error({
                title: 'Error',
                message: 'Error al cargar el detalle del pago',
                position: 'topRight'
            });
            // Volver a mostrar el modal de pagos
            $('#modal_pagos_proyecto').modal('show');
        }
    });
}

/**
 * Mostrar modal con detalle del pago
 */
function mostrarDetallePago(data) {
    let gasto = data.gasto;
    let cxps = data.cxps || [];

    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-file-invoice"></i> Información del Gasto</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th style="width: 40%;">Fecha:</th>
                        <td>${formatearFecha(gasto.fecha)}</td>
                    </tr>
                    <tr>
                        <th>N° Comprobante:</th>
                        <td>${gasto.num_factura || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Tipo de Pago:</th>
                        <td>${gasto.tipo_pago_nombre || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Monto:</th>
                        <td class="text-success"><strong>₡${parseFloat(gasto.monto * gasto.tipo_cambio).toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-info-circle"></i> Descripción</h6>
                <div class="alert alert-info">
                    ${gasto.descripcion || 'Sin descripción'}
                </div>
                ${gasto.observacion ? `
                <h6><i class="fas fa-comment"></i> Observaciones</h6>
                <div class="alert alert-secondary">
                    ${gasto.observacion}
                </div>
                ` : ''}
            </div>
        </div>

        <hr>

        <h6><i class="fas fa-list"></i> Cuentas por Pagar Relacionadas (${cxps.length})</h6>
    `;

    if (cxps.length > 0) {
        cxps.forEach((cxp, index) => {
            let deducciones = cxp.deducciones || [];
            let totalDeducciones = 0;
            let montoBase = 0;

            if (deducciones.length > 0) {
                montoBase = parseFloat(deducciones[0].monto_base || 0);
                deducciones.forEach(d => {
                    totalDeducciones += parseFloat(d.monto_deduccion || 0);
                });
            }

            html += `
                <div class="card card-proyecto mb-3">
                    <div class="card-header bg-soft-info">
                        <strong><i class="fas fa-file-invoice-dollar"></i> CxP #${cxp.numero_cxp}</strong>
                        <span class="float-right badge badge-${cxp.estado_codigo === 'CXP_PAGADA' ? 'success' : 'warning'}">
                            ${cxp.estado_nombre}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-user"></i> Beneficiario:</strong> ${cxp.beneficiario}</p>
                                <p><strong><i class="fas fa-credit-card"></i> Cuenta:</strong> ${cxp.numero_cuenta || 'N/A'}</p>
                                <p><strong><i class="fas fa-tag"></i> Tipo:</strong> ${cxp.tipo_nombre}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar"></i> Fecha Creación:</strong> ${formatearFecha(cxp.fecha_creacion)}</p>
                                ${cxp.fecha_aprobacion ? `
                                <p><strong><i class="fas fa-check"></i> Fecha Pago:</strong> ${formatearFecha(cxp.fecha_aprobacion)}</p>
                                ` : ''}
                                <p><strong><i class="fas fa-dollar-sign"></i> Monto:</strong> 
                                    <span class="text-success"><strong>₡${parseFloat(cxp.monto_total).toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></span>
                                </p>
                            </div>
                        </div>

                        ${cxp.observaciones ? `
                        <div class="mt-2">
                            <strong><i class="fas fa-comment"></i> Observaciones:</strong>
                            <p class="mb-0">${cxp.observaciones.replace(/\n/g, '<br>')}</p>
                        </div>
                        ` : ''}

                        ${deducciones.length > 0 ? `
                        <div class="mt-3">
                            <strong><i class="fas fa-calculator"></i> Deducciones Aplicadas:</strong>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Concepto</th>
                                            <th class="text-right" style="width: 120px;">Base</th>
                                            <th class="text-center" style="width: 80px;">%</th>
                                            <th class="text-right" style="width: 120px;">Deducción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${deducciones.map(d => `
                                        <tr>
                                            <td>
                                                <i class="fas fa-arrow-right text-danger"></i> ${d.rubro_nombre}
                                            </td>
                                            <td class="text-right">₡${parseFloat(d.monto_base).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                                            <td class="text-center">
                                                <span class="badge badge-warning">${parseFloat(d.porcentaje).toFixed(2)}%</span>
                                            </td>
                                            <td class="text-right text-danger">
                                                - ₡${parseFloat(d.monto_deduccion).toLocaleString('es-CR', {minimumFractionDigits: 2})}
                                            </td>
                                        </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr class="bg-light">
                                            <td colspan="3" class="text-right"><strong>Monto Base (Antes de Deducciones):</strong></td>
                                            <td class="text-right"><strong>₡${montoBase.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                                        </tr>
                                        <tr class="bg-soft-info">
                                            <td colspan="3" class="text-right"><strong>Total Deducciones:</strong></td>
                                            <td class="text-right text-danger"><strong>₡${totalDeducciones.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                                        </tr>
                                        <tr class="bg-soft-success">
                                            <td colspan="3" class="text-right"><strong>MONTO FINAL (CxP):</strong></td>
                                            <td class="text-right text-success"><strong>₡${parseFloat(cxp.monto_total).toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        ` : ''}

                        ${cxp.bitacoras && cxp.bitacoras.length > 0 ? `
                        <div class="mt-3">
                            <strong><i class="fas fa-clipboard-list"></i> Bitácoras Relacionadas (${cxp.bitacoras.length}):</strong>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th style="width: 80px;">Fecha</th>
                                            <th>Usuario</th>
                                            <th>Proyecto</th>
                                            <th style="width: 80px;">Horas</th>
                                            <th>Rubro</th>
                                            <th style="width: 100px;">Línea Presupuesto</th>
                                            <th class="text-right">Costo (₡)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${cxp.bitacoras.map(b => `
                                        <tr>
                                            <td class="text-center"><small>${formatearFechaSimple(b.fecha)}</small></td>
                                            <td><small>${b.usuario_nombre || 'N/A'}</small></td>
                                            <td><small>${b.proyecto_nombre || 'N/A'}</small></td>
                                            <td class="text-center">${parseFloat(b.horas_calculadas || 0).toFixed(2)}</td>
                                            <td><small>${b.rubro_nombre || 'Normal'} (×${parseFloat(b.multiplicador || 1).toFixed(2)})</small></td>
                                            <td class="text-center">
                                                ${b.linea_numero ? `<span class="badge badge-info">#${b.linea_numero}</span><br><small class="text-muted">${(b.linea_descripcion || '').substring(0, 30)}${(b.linea_descripcion || '').length > 30 ? '...' : ''}</small>` : '<span class="text-muted">-</span>'}
                                            </td>
                                            <td class="text-right">₡${parseFloat(b.costo_calculado || 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                                        </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr class="font-weight-bold">
                                            <td colspan="6" class="text-right">Total:</td>
                                            <td class="text-right">₡${cxp.bitacoras.reduce((sum, b) => sum + parseFloat(b.costo_calculado || 0), 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
    } else {
        html += `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No hay CxP relacionadas con este gasto.
            </div>
        `;
    }

    $('#detalle_pago_content').html(html);
    $('#modal_detalle_pago').modal('show');

    // Cuando se cierre el modal de detalle, volver a mostrar el de pagos del proyecto
    $('#modal_detalle_pago').on('hidden.bs.modal', function (e) {
        $('#modal_pagos_proyecto').modal('show');
        // Remover el event listener para evitar duplicados
        $(this).off('hidden.bs.modal');
    });
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    let d = new Date(fecha);
    let dia = String(d.getDate()).padStart(2, '0');
    let mes = String(d.getMonth() + 1).padStart(2, '0');
    let anio = d.getFullYear();
    let hora = String(d.getHours()).padStart(2, '0');
    let minuto = String(d.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${minuto}`;
}

/**
 * Formatear fecha simple (sin hora)
 */
function formatearFechaSimple(fecha) {
    if (!fecha) return '-';
    let d = new Date(fecha + 'T00:00:00');
    let dia = String(d.getDate()).padStart(2, '0');
    let mes = String(d.getMonth() + 1).padStart(2, '0');
    let anio = d.getFullYear();
    return `${dia}/${mes}/${anio}`;
}
