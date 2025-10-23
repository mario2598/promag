var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var usuariosConCxP = [];
var cxpSeleccionadas = [];
var usuarioActual = null;

$(document).ready(function () {
    cargarUsuariosConCxP();
});

function cargarUsuariosConCxP() {
    $.ajax({
        url: `${base_path}/cxp/cargar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            procesarUsuariosConCxP(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar las CxP");
    });
}

function procesarUsuariosConCxP(cxp) {
    // Agrupar CxP por usuario
    let usuarios = {};
    let totalCxP = 0;
    let montoTotal = 0;

    cxp.forEach(c => {
        // Solo contar CxP pendientes
        if (c.estado_codigo !== 'CXP_PENDIENTE') {
            return;
        }

        let usuarioId = c.usuario_creacion || 'sin_usuario';
        let usuarioNombre = c.usuario_creacion_nombre || 'Sin usuario asignado';
        
        if (!usuarios[usuarioId]) {
            usuarios[usuarioId] = {
                id: usuarioId,
                nombre: usuarioNombre,
                beneficiario: c.beneficiario || 'No especificado',
                numero_cuenta: c.numero_cuenta || 'No especificado',
                cxps: [],
                cantidadCxP: 0,
                montoTotal: 0
            };
        }
        
        let saldoPendiente = parseFloat(c.monto_total || 0) - parseFloat(c.monto_pagado || 0);
        
        usuarios[usuarioId].cxps.push(c);
        usuarios[usuarioId].cantidadCxP++;
        usuarios[usuarioId].montoTotal += saldoPendiente;
        
        totalCxP++;
        montoTotal += saldoPendiente;
    });

    usuariosConCxP = Object.values(usuarios);

    // Actualizar tarjetas de resumen
    $('#total_usuarios').text(usuariosConCxP.length);
    $('#total_cxp').text(totalCxP);
    $('#monto_total').text('₡' + montoTotal.toLocaleString('es-CR', {minimumFractionDigits: 2}));

    // Renderizar tabla de usuarios
    renderizarTablaUsuarios();
}

function renderizarTablaUsuarios() {
    let tbody = $('#tbody_usuarios');
    tbody.empty();

    if (usuariosConCxP.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                    <h5>No hay usuarios con CxP pendientes</h5>
                </td>
            </tr>
        `);
        return;
    }

    usuariosConCxP.forEach(usuario => {
        tbody.append(`
            <tr>
                <td>
                    <strong>${usuario.nombre}</strong><br>
                    <small class="text-muted">${usuario.cantidadCxP} CxP(s)</small>
                </td>
                <td class="text-center">${usuario.beneficiario}</td>
                <td class="text-center">${usuario.numero_cuenta}</td>
                <td class="text-center">
                    <span class="badge badge-warning">${usuario.cantidadCxP}</span>
                </td>
                <td class="text-center text-success">
                    <strong>₡${usuario.montoTotal.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary" onclick="abrirModalGestionarCxP('${usuario.id}')" title="Gestionar CxP">
                        <i class="fas fa-tasks"></i> Gestionar
                    </button>
                </td>
            </tr>
        `);
    });
}

function abrirModalGestionarCxP(usuarioId) {
    usuarioActual = usuariosConCxP.find(u => u.id == usuarioId);
    
    if (!usuarioActual) {
        showError("Usuario no encontrado");
        return;
    }

    // Limpiar selección
    cxpSeleccionadas = [];

    // Llenar información del modal
    $('#nombre_usuario_modal').text(usuarioActual.nombre);
    $('#beneficiario_modal').text(usuarioActual.beneficiario);
    $('#numero_cuenta_modal').text(usuarioActual.numero_cuenta);
    $('#monto_total_modal').text('₡' + usuarioActual.montoTotal.toLocaleString('es-CR', {minimumFractionDigits: 2}));

    // Renderizar tabla de CxP
    renderizarTablaCxPModal();

    // Actualizar contador
    actualizarContadorSeleccionadas();

    // Mostrar modal
    $('#modal_gestionar_cxp').modal('show');
}

function renderizarTablaCxPModal() {
    let html = `
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="50" class="text-center">
                            <input type="checkbox" id="select_all_cxp" onchange="seleccionarTodasCxP()" 
                                   style="width: 20px; height: 20px; cursor: pointer;">
                        </th>
                        <th>Número CxP</th>
                        <th>Tipo</th>
                        <th>Fecha Creación</th>
                        <th>Monto Total</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
    `;

    let totalSaldo = 0;

    usuarioActual.cxps.forEach(cxp => {
        let montoTotal = parseFloat(cxp.monto_total || 0);
        let montoPagado = parseFloat(cxp.monto_pagado || 0);
        let saldoPendiente = montoTotal - montoPagado;
        let estadoBadge = obtenerBadgeEstado(cxp.estado_codigo, cxp.estado_nombre);

        totalSaldo += saldoPendiente;

        html += `
            <tr data-cxp-id="${cxp.id}">
                <td class="text-center">
                    <input type="checkbox" class="checkbox-cxp" value="${cxp.id}" 
                           data-monto="${saldoPendiente}"
                           onchange="actualizarSeleccion()"
                           style="width: 20px; height: 20px; cursor: pointer;">
                </td>
                <td><strong>${cxp.numero_cxp}</strong></td>
                <td><small>${cxp.tipo_nombre || '-'}</small></td>
                <td><small>${formatearFecha(cxp.fecha_creacion)}</small></td>
                <td class="text-success">₡${montoTotal.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td class="text-warning"><strong>₡${saldoPendiente.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                <td>${estadoBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="verDetalleCxP(${cxp.id})" title="Ver Detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
                <tfoot class="bg-light">
                    <tr class="font-weight-bold">
                        <td colspan="5" class="text-right">Total Saldo Pendiente:</td>
                        <td class="text-warning">₡${totalSaldo.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    $('#contenedor_cxp_modal').html(html);
}

function seleccionarTodasCxP() {
    let selectAll = $('#select_all_cxp').is(':checked');
    $('.checkbox-cxp').prop('checked', selectAll);
    actualizarSeleccion();
}

function actualizarSeleccion() {
    cxpSeleccionadas = [];
    
    $('.checkbox-cxp:checked').each(function() {
        cxpSeleccionadas.push({
            id: $(this).val(),
            monto: parseFloat($(this).data('monto'))
        });
    });

    actualizarContadorSeleccionadas();
}

function actualizarContadorSeleccionadas() {
    let cantidad = cxpSeleccionadas.length;
    $('#cant_seleccionadas').text(cantidad);
    $('#cant_seleccionadas_rechazar').text(cantidad);

    // Habilitar/deshabilitar botones
    if (cantidad > 0) {
        $('#btn_aprobar_todas').prop('disabled', false);
        $('#btn_rechazar_todas').prop('disabled', false);
    } else {
        $('#btn_aprobar_todas').prop('disabled', true);
        $('#btn_rechazar_todas').prop('disabled', true);
    }
}

function aprobarYPagarTodasCxP() {
    if (cxpSeleccionadas.length === 0) {
        showError("Debe seleccionar al menos una CxP");
        return;
    }

    let montoTotal = cxpSeleccionadas.reduce((sum, c) => sum + c.monto, 0);

    // Actualizar información en el modal de aprobación
    $('#cant_cxp_aprobar').text(cxpSeleccionadas.length);
    $('#monto_total_aprobar').text('₡' + montoTotal.toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#tipo_pago_aprobacion').val('');
    $('#num_comprobante_aprobacion').val('');
    $('#observaciones_aprobacion').val('');
    
    // Mostrar el modal de aprobación
    $('#modal_aprobar_cxp').modal('show');
}

function confirmarAprobacion() {
    let tipoPago = $('#tipo_pago_aprobacion').val();
    let numComprobante = $('#num_comprobante_aprobacion').val().trim();
    let observaciones = $('#observaciones_aprobacion').val().trim();
    
    if (!tipoPago) {
        showError("Debe seleccionar un tipo de pago");
        return;
    }

    let cxpIds = cxpSeleccionadas.map(c => c.id);
    let observacionesFinal = observaciones || `Pago aprobado para ${usuarioActual.nombre}`;

    $.ajax({
        url: `${base_path}/cxp/aprobar-pagar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            cxp_ids: cxpIds,
            tipo_pago: tipoPago,
            num_comprobante: numComprobante,
            observaciones: observacionesFinal
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_aprobar_cxp').modal('hide');
            $('#modal_gestionar_cxp').modal('hide');
            cargarUsuariosConCxP();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al aprobar y pagar las CxP");
    });
}

function rechazarTodasCxP() {
    if (cxpSeleccionadas.length === 0) {
        showError("Debe seleccionar al menos una CxP");
        return;
    }

    $('#motivo_rechazo').val('');
    $('#modal_rechazar_cxp').data('cxp-ids', cxpSeleccionadas.map(c => c.id));
    $('#modal_rechazar_cxp').modal('show');
}

function confirmarRechazo() {
    let cxpIds = $('#modal_rechazar_cxp').data('cxp-ids');
    let motivoRechazo = $('#motivo_rechazo').val().trim();
    
    if (!motivoRechazo) {
        showError("Debe ingresar un motivo de rechazo");
        return;
    }
    
    if (!cxpIds || cxpIds.length === 0) {
        showError("No hay CxP seleccionadas");
        return;
    }
    
    $.ajax({
        url: `${base_path}/cxp/rechazar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            cxp_ids: cxpIds,
            motivo_rechazo: motivoRechazo
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_rechazar_cxp').modal('hide');
            $('#modal_gestionar_cxp').modal('hide');
            cargarUsuariosConCxP();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al rechazar las CxP");
    });
}

function verDetalleCxP(cxpId) {
    // Ocultar el modal de gestión temporalmente
    $('#modal_gestionar_cxp').modal('hide');
    
    $.ajax({
        url: `${base_path}/cxp/detalle`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: cxpId
        }
    }).done(function (response) {
        if (response['estado']) {
            mostrarDetalleCxPEnAlerta(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar el detalle de la CxP");
    });
}

function mostrarDetalleCxPEnAlerta(cxp) {
    let detallesHTML = '';
    
    if (cxp.detalles && cxp.detalles.length > 0) {
        detallesHTML = '<table class="table table-sm table-bordered mt-2"><thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Monto</th></tr></thead><tbody>';
        cxp.detalles.forEach(d => {
            detallesHTML += `<tr>
                <td>${d.descripcion}</td>
                <td>${d.cantidad || 1}</td>
                <td>₡${parseFloat(d.precio_unitario || 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td>₡${parseFloat(d.monto).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
            </tr>`;
        });
        detallesHTML += '</tbody></table>';
    }

    // Sección de deducciones
    let deduccionesHTML = '';
    if (cxp.deducciones && cxp.deducciones.length > 0) {
        let totalDeducciones = 0;
        let deduccionesFilas = '';
        
        cxp.deducciones.forEach(d => {
            totalDeducciones += parseFloat(d.monto_deduccion);
            deduccionesFilas += `
                <tr>
                    <td>
                        <i class="fas fa-arrow-right text-danger"></i> ${d.rubro_nombre}
                        <span class="badge badge-warning">${parseFloat(d.porcentaje).toFixed(2)}%</span>
                    </td>
                    <td class="text-right">₡${parseFloat(d.monto_base).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                    <td class="text-right text-danger">- ₡${parseFloat(d.monto_deduccion).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });

        // Calcular monto antes de deducciones
        let montoAntesDeducciones = parseFloat(cxp.monto_total) + totalDeducciones;

        deduccionesHTML = `
            <div class="mt-3">
                <strong><i class="fas fa-calculator"></i> Deducciones Aplicadas:</strong>
                <table class="table table-sm table-bordered mt-2">
                    <thead class="thead-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-right" style="width: 120px;">Base</th>
                            <th class="text-right" style="width: 120px;">Deducción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-light">
                            <td><strong><i class="fas fa-clock"></i> Monto Base (Antes de Deducciones)</strong></td>
                            <td colspan="2" class="text-right"><strong>₡${montoAntesDeducciones.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                        </tr>
                        ${deduccionesFilas}
                        <tr class="table-info">
                            <td colspan="2"><strong><i class="fas fa-minus-circle"></i> Total Deducciones</strong></td>
                            <td class="text-right text-danger"><strong>₡${totalDeducciones.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td colspan="2"><strong><i class="fas fa-check-circle"></i> MONTO FINAL (CxP)</strong></td>
                            <td class="text-right text-success"><strong>₡${parseFloat(cxp.monto_total).toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }

    // Información de aprobación/rechazo
    let infoAprobacion = '';
    if (cxp.usuario_aprobacion_nombre && cxp.fecha_aprobacion) {
        let estadoCodigo = cxp.estado_codigo || '';
        let textoAccion = estadoCodigo === 'CXP_CANCELADA' ? 'Rechazada' : 'Aprobada';
        infoAprobacion = `
            <hr>
            <p><strong>${textoAccion} por:</strong> ${cxp.usuario_aprobacion_nombre}</p>
            <p><strong>Fecha de ${textoAccion.toLowerCase()}:</strong> ${formatearFecha(cxp.fecha_aprobacion)}</p>
        `;
    }

    let mensaje = `
        <div class="text-left">
            <p><strong>Número:</strong> ${cxp.numero_cxp}</p>
            <p><strong>Tipo:</strong> ${cxp.tipo_nombre}</p>
            <p><strong>Estado:</strong> ${obtenerBadgeEstado(cxp.estado_codigo, cxp.estado_nombre)}</p>
            <p><strong>Beneficiario:</strong> ${cxp.beneficiario}</p>
            <p><strong>Cuenta:</strong> ${cxp.numero_cuenta || 'No especificado'}</p>
            <p><strong>Monto Total CxP:</strong> ₡${parseFloat(cxp.monto_total).toLocaleString('es-CR', {minimumFractionDigits: 2})}</p>
            <p><strong>Saldo:</strong> ₡${(parseFloat(cxp.monto_total) - parseFloat(cxp.monto_pagado || 0)).toLocaleString('es-CR', {minimumFractionDigits: 2})}</p>
            ${infoAprobacion}
            ${deduccionesHTML}
            ${cxp.observaciones ? `<p><strong>Observaciones:</strong><br>${cxp.observaciones.replace(/\n/g, '<br>')}</p>` : ''}
            ${detallesHTML ? `<div><strong>Detalles:</strong>${detallesHTML}</div>` : ''}
        </div>
    `;

    swal({
        title: "Detalle de CxP",
        content: {
            element: "div",
            attributes: {
                innerHTML: mensaje
            }
        },
        buttons: {
            confirm: {
                text: "Cerrar",
                value: true,
                visible: true,
                className: "btn btn-primary"
            }
        }
    }).then(() => {
        // Volver a mostrar el modal de gestión
        $('#modal_gestionar_cxp').modal('show');
    });
}

function obtenerBadgeEstado(codigo, nombre) {
    switch (codigo) {
        case 'CXP_PENDIENTE':
            return `<span class="badge badge-warning">${nombre}</span>`;
        case 'CXP_APROBADA':
            return `<span class="badge badge-info">${nombre}</span>`;
        case 'CXP_PAGADA':
            return `<span class="badge badge-success">${nombre}</span>`;
        case 'CXP_CANCELADA':
            return `<span class="badge badge-danger">${nombre}</span>`;
        default:
            return `<span class="badge badge-secondary">${nombre}</span>`;
    }
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-CR');
}
