var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaUsuariosPendientes;
var tablaHistorialBitacoras;
var bitacorasUsuarioActual = [];
var filtroActual = 'TODAS';

$(document).ready(function () {
    cargarUsuariosPendientes();
    cargarHistorialBitacoras('TODAS');
});

function cargarUsuariosPendientes() {
    $.ajax({
        url: `${base_path}/proyectos/cargarUsuariosBitacorasPendientes`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarTablaUsuarios(response['datos']);
            actualizarContadores(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar usuarios pendientes");
    });
}

function renderizarTablaUsuarios(usuarios) {
    if (tablaUsuariosPendientes) {
        tablaUsuariosPendientes.destroy();
    }

    let tbody = $('#tabla_usuarios_pendientes tbody');
    tbody.empty();

    if (usuarios.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                    <i>No hay bitácoras pendientes de autorización</i>
                </td>
            </tr>
        `);
        return;
    }

    usuarios.forEach(usuario => {
        let totalHoras = parseFloat(usuario.total_horas || 0);
        let montoEstimado = parseFloat(usuario.monto_estimado || 0);
        let totalProyectos = parseInt(usuario.total_proyectos || 0);

        let fila = `
            <tr class="text-center">
                <td class="text-left"><strong>${usuario.usuario_nombre}</strong></td>
                <td><span class="badge badge-info">${totalProyectos}</span></td>
                <td><span class="badge badge-warning">${usuario.total_bitacoras}</span></td>
                <td><strong>${totalHoras.toFixed(2)}</strong> hrs</td>
                <td class="text-success"><strong>₡${montoEstimado.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="abrirDesgloseBitacoras(${usuario.usuario}, '${usuario.usuario_nombre}')" title="Ver Bitácoras">
                        <i class="fas fa-clipboard-check"></i> Revisar
                    </button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });

    tablaUsuariosPendientes = $('#tabla_usuarios_pendientes').DataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "order": [[1, "asc"]]
    });
}

function actualizarContadores(usuarios) {
    let totalBitacoras = 0;
    usuarios.forEach(u => {
        totalBitacoras += parseInt(u.total_bitacoras || 0);
    });

    $('#total_pendientes').text(totalBitacoras);
    $('#total_aprobadas').text('0');
    $('#total_rechazadas').text('0');
    $('#total_general').text(totalBitacoras);

    // Cargar totales reales
    $.ajax({
        url: `${base_path}/proyectos/cargarBitacorasAutorizacion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro_estado: 'TODAS'
        }
    }).done(function (response) {
        if (response['estado']) {
            let todasBitacoras = response['datos'];
            let aprobadas = todasBitacoras.filter(b => b.estado_codigo === 'BIT_PROY_APROBADA').length;
            let rechazadas = todasBitacoras.filter(b => b.estado_codigo === 'BIT_PROY_RECHAZADA').length;

            $('#total_aprobadas').text(aprobadas);
            $('#total_rechazadas').text(rechazadas);
            $('#total_general').text(todasBitacoras.length);
        }
    });
}

function abrirDesgloseBitacoras(usuarioId, usuarioNombre) {
    $('#desglose_usuario_id').val(usuarioId);
    $('#desglose_usuario_nombre').text(usuarioNombre);

    // Cargar TODAS las bitácoras pendientes del usuario (de todos sus proyectos)
    $.ajax({
        url: `${base_path}/proyectos/cargarBitacorasAutorizacion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro_estado: 'PENDIENTE'
        }
    }).done(function (response) {
        if (response['estado']) {
            // Filtrar solo las de este usuario
            let bitacorasDelUsuario = response['datos'].filter(b => b.usuario == usuarioId);
            bitacorasUsuarioActual = bitacorasDelUsuario;
            renderizarDesgloseBitacorasPorProyecto(bitacorasDelUsuario);
            $('#modal_desglose_bitacoras').modal('show');
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar bitácoras");
    });
}

function renderizarDesgloseBitacorasPorProyecto(bitacoras) {
    let contenedor = $('#contenedor_bitacoras_proyectos');
    contenedor.empty();

    if (bitacoras.length === 0) {
        contenedor.append(`
            <div class="alert alert-warning text-center">
                <i class="fas fa-inbox fa-3x mb-3"></i><br>
                <i>No hay bitácoras pendientes para este usuario</i>
            </div>
        `);
        return;
    }

    // Agrupar bitácoras por proyecto
    let proyectosMap = {};
    bitacoras.forEach(bitacora => {
        let proyectoId = bitacora.proyecto;
        if (!proyectosMap[proyectoId]) {
            proyectosMap[proyectoId] = {
                id: proyectoId,
                nombre: bitacora.proyecto_nombre || 'Proyecto sin nombre',
                cliente: bitacora.cliente_nombre || '-',
                bitacoras: []
            };
        }
        proyectosMap[proyectoId].bitacoras.push(bitacora);
    });

    // Obtener precio hora
    let precioHora = 0;
    $.ajax({
        url: `${base_path}/proyectos/cargarUsuarios`,
        type: 'post',
        dataType: "json",
        data: { _token: CSRF_TOKEN },
        async: false
    }).done(function (response) {
        if (response['estado']) {
            let usuario = response['datos'].find(u => u.id == $('#desglose_usuario_id').val());
            if (usuario) {
                precioHora = parseFloat(usuario.precio_hora || 0);
            }
        }
    });

    let totalGeneralHoras = 0;
    let totalGeneralCosto = 0;

    // Renderizar cada proyecto
    Object.values(proyectosMap).forEach(proyecto => {
        let totalHorasProyecto = 0;
        let totalCostoProyecto = 0;

        let tablaBitacoras = `
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-folder"></i> <strong>${proyecto.nombre}</strong>
                        <span class="text-muted">| Cliente: ${proyecto.cliente}</span>
                        <span class="badge badge-warning float-right">${proyecto.bitacoras.length} bitácora(s)</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th>Fecha</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Horas</th>
                                    <th>Rubro</th>
                                    <th>Línea</th>
                                    <th>Descripción</th>
                                    <th>Costo (₡)</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        proyecto.bitacoras.forEach(bitacora => {
            let horas = calcularHoras(bitacora.hora_entrada, bitacora.hora_salida);
            let multiplicador = parseFloat(bitacora.rubro_multiplicador || 1.00);
            let costo = horas * precioHora * multiplicador;

            totalHorasProyecto += horas;
            totalCostoProyecto += costo;
            totalGeneralHoras += horas;
            totalGeneralCosto += costo;

            let rubroNombre = bitacora.rubro_nombre || 'Hora Normal';
            let rubroTexto = `${rubroNombre} <small>(×${multiplicador.toFixed(2)})</small>`;

            let lineaTexto = '-';
            if (bitacora.linea_numero) {
                lineaTexto = `<span class="badge badge-secondary" title="${bitacora.linea_descripcion || ''}">#${bitacora.linea_numero}</span>`;
            }

            tablaBitacoras += `
                <tr class="text-center">
                    <td>${formatearFecha(bitacora.fecha)}</td>
                    <td>${formatearHora(bitacora.hora_entrada)}</td>
                    <td>${formatearHora(bitacora.hora_salida)}</td>
                    <td><strong>${horas.toFixed(2)}</strong> hrs</td>
                    <td><small>${rubroTexto}</small></td>
                    <td>${lineaTexto}</td>
                    <td class="text-left"><small>${bitacora.descripcion}</small></td>
                    <td class="text-success"><strong>₡${costo.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="aprobarBitacora(${bitacora.id})" title="Aprobar">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rechazarBitacora(${bitacora.id})" title="Rechazar">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tablaBitacoras += `
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="font-weight-bold">
                                    <td colspan="3" class="text-right">Subtotal Proyecto:</td>
                                    <td class="text-center">${totalHorasProyecto.toFixed(2)} hrs</td>
                                    <td colspan="3"></td>
                                    <td class="text-center text-success">₡${totalCostoProyecto.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        `;

        contenedor.append(tablaBitacoras);
    });

    // Agregar totales generales al final
    contenedor.append(`
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-clock"></i> Total General de Horas: <strong>${totalGeneralHoras.toFixed(2)} hrs</strong></h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5><i class="fas fa-dollar-sign"></i> Monto Total: <strong>₡${totalGeneralCosto.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></h5>
                    </div>
                </div>
            </div>
        </div>
    `);
}

function aprobarBitacora(bitacoraId) {
    if (!confirm('¿Aprobar esta bitácora?')) {
        return;
    }

    $.ajax({
        url: `${base_path}/proyectos/autorizarBitacora`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            bitacora_id: bitacoraId,
            accion: 'aprobar'
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            // Recargar desglose del mismo usuario
            abrirDesgloseBitacoras(
                $('#desglose_usuario_id').val(),
                $('#desglose_usuario_nombre').text()
            );
            // Recargar listado principal
            cargarUsuariosPendientes();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al aprobar bitácora");
    });
}

function rechazarBitacora(bitacoraId) {
    // Mostrar panel de observación dentro del mismo modal
    $('#rechazo_bitacora_id').val(bitacoraId);
    $('#rechazo_es_multiple').val('0');
    $('#rechazo_observacion').val('');
    $('#panel_observacion_rechazo').slideDown(300);
    $('#botones_acciones_masivas').slideUp(300);
    $('#contenedor_bitacoras_proyectos').slideUp(300);
    setTimeout(function() {
        $('#rechazo_observacion').focus();
    }, 350);
}

function cerrarPanelRechazo() {
    $('#panel_observacion_rechazo').slideUp(300);
    $('#botones_acciones_masivas').slideDown(300);
    $('#contenedor_bitacoras_proyectos').slideDown(300);
}

function confirmarRechazo() {
    let bitacoraId = $('#rechazo_bitacora_id').val();
    let esMultiple = $('#rechazo_es_multiple').val() === '1';
    let observacion = $('#rechazo_observacion').val().trim();

    if (esMultiple) {
        // Rechazar múltiples
        let bitacoraIds = bitacorasUsuarioActual.map(b => b.id);

        $.ajax({
            url: `${base_path}/proyectos/autorizarMultiplesBitacoras`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                bitacora_ids: bitacoraIds,
                accion: 'rechazar',
                observacion_rechazo: observacion
            }
        }).done(function (response) {
            if (response['estado']) {
                showSuccess(response['mensaje']);
                cerrarPanelRechazo();
                $('#modal_desglose_bitacoras').modal('hide');
                cargarUsuariosPendientes();
            } else {
                showError(response['mensaje']);
            }
        }).fail(function () {
            showError("Error al rechazar bitácoras");
        });
    } else {
        // Rechazar una sola
        $.ajax({
            url: `${base_path}/proyectos/autorizarBitacora`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                bitacora_id: bitacoraId,
                accion: 'rechazar',
                observacion_rechazo: observacion
            }
        }).done(function (response) {
            if (response['estado']) {
                showSuccess(response['mensaje']);
                cerrarPanelRechazo();
                // Recargar desglose del mismo usuario
                abrirDesgloseBitacoras(
                    $('#desglose_usuario_id').val(),
                    $('#desglose_usuario_nombre').text()
                );
                // Recargar listado principal
                cargarUsuariosPendientes();
            } else {
                showError(response['mensaje']);
            }
        }).fail(function () {
            showError("Error al rechazar bitácora");
        });
    }
}

function aprobarTodasBitacoras() {
    if (bitacorasUsuarioActual.length === 0) {
        showError("No hay bitácoras para aprobar");
        return;
    }

    if (!confirm(`¿Aprobar las ${bitacorasUsuarioActual.length} bitácora(s) pendientes de este usuario?`)) {
        return;
    }

    let bitacoraIds = bitacorasUsuarioActual.map(b => b.id);

    $.ajax({
        url: `${base_path}/proyectos/autorizarMultiplesBitacoras`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            bitacora_ids: bitacoraIds,
            accion: 'aprobar'
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_desglose_bitacoras').modal('hide');
            cargarUsuariosPendientes();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al aprobar bitácoras");
    });
}

function rechazarTodasBitacoras() {
    if (bitacorasUsuarioActual.length === 0) {
        showError("No hay bitácoras para rechazar");
        return;
    }

    if (!confirm(`¿Rechazar las ${bitacorasUsuarioActual.length} bitácora(s) pendientes de este usuario?`)) {
        return;
    }

    // Mostrar panel de observación
    $('#rechazo_bitacora_id').val('');
    $('#rechazo_es_multiple').val('1');
    $('#rechazo_observacion').val('');
    $('#panel_observacion_rechazo').slideDown(300);
    $('#botones_acciones_masivas').slideUp(300);
    $('#contenedor_bitacoras_proyectos').slideUp(300);
    setTimeout(function() {
        $('#rechazo_observacion').focus();
    }, 350);
}

function calcularHoras(horaInicio, horaFin) {
    let [h1, m1] = horaInicio.split(':').map(Number);
    let [h2, m2] = horaFin.split(':').map(Number);
    
    let minutos1 = h1 * 60 + m1;
    let minutos2 = h2 * 60 + m2;
    
    let diferenciaMinutos = minutos2 - minutos1;
    return diferenciaMinutos / 60;
}

function formatearFecha(fecha) {
    let date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-CR', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function formatearHora(hora) {
    return hora.substring(0, 5); // HH:MM
}

function verObservacionRechazo(observacion) {
    let observacionDecodificada = observacion.replace(/&#39;/g, "'").replace(/&quot;/g, '"');
    $('#contenido_observacion_rechazo').text(observacionDecodificada);
    $('#modal_observacion_rechazo').modal('show');
}

function filtrarBitacoras(filtro) {
    filtroActual = filtro;
    
    // Actualizar botones activos
    $('.card-header-action .btn-group button').removeClass('active');
    event.target.closest('button').classList.add('active');
    
    cargarHistorialBitacoras(filtro);
}

function cargarHistorialBitacoras(filtro) {
    let filtroEstado = filtro;
    if (filtro === 'APROBADA') filtroEstado = 'APROBADA';
    else if (filtro === 'RECHAZADA') filtroEstado = 'RECHAZADA';
    else if (filtro === 'PENDIENTE') filtroEstado = 'PENDIENTE';
    else filtroEstado = 'TODAS';

    $.ajax({
        url: `${base_path}/proyectos/cargarBitacorasAutorizacion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro_estado: filtroEstado
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarHistorialBitacoras(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar el historial de bitácoras");
    });
}

function renderizarHistorialBitacoras(bitacoras) {
    if (tablaHistorialBitacoras) {
        tablaHistorialBitacoras.destroy();
    }

    let tbody = $('#tabla_historial_bitacoras tbody');
    tbody.empty();

    if (bitacoras.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="12" class="text-center text-muted">
                    <i>No hay bitácoras para mostrar</i>
                </td>
            </tr>
        `);
        return;
    }

    bitacoras.forEach(bitacora => {
        let horas = calcularHoras(bitacora.hora_entrada, bitacora.hora_salida);
        let multiplicador = parseFloat(bitacora.rubro_multiplicador || 1.00);
        let precioHora = parseFloat(bitacora.precio_hora || 0);
        let costo = horas * precioHora * multiplicador;

        // Badge de estado
        let badgeEstado = '';
        switch (bitacora.estado_codigo || 'BIT_PROY_PENDIENTE') {
            case 'BIT_PROY_PENDIENTE':
                badgeEstado = '<span class="badge badge-warning">Pendiente</span>';
                break;
            case 'BIT_PROY_APROBADA':
                badgeEstado = '<span class="badge badge-success">Aprobada</span>';
                break;
            case 'BIT_PROY_RECHAZADA':
                badgeEstado = '<span class="badge badge-danger">Rechazada</span>';
                if (bitacora.observacion_rechazo && bitacora.observacion_rechazo.trim() !== '') {
                    let observacionEscapada = bitacora.observacion_rechazo.replace(/'/g, '&#39;').replace(/"/g, '&quot;').replace(/\n/g, '&#10;');
                    badgeEstado += ` <i class="fas fa-comment-alt text-danger" style="cursor:pointer; font-size: 1.1em;" onclick="verObservacionRechazo('${observacionEscapada}')" title="Ver motivo de rechazo"></i>`;
                }
                break;
            default:
                badgeEstado = '<span class="badge badge-secondary">Sin estado</span>';
        }

        let rubroNombre = bitacora.rubro_nombre || 'Hora Normal';
        let rubroTexto = `${rubroNombre} <small class="text-muted">(×${multiplicador.toFixed(2)})</small>`;

        let lineaTexto = '-';
        if (bitacora.linea_numero) {
            lineaTexto = `<span class="badge badge-secondary">#${bitacora.linea_numero}</span>`;
        }

        let autorizadoPor = '-';
        if (bitacora.usuario_autoriza_nombre) {
            let fechaAutorizacion = bitacora.fecha_autorizacion ? formatearFecha(bitacora.fecha_autorizacion.split(' ')[0]) : '';
            autorizadoPor = `<small>${bitacora.usuario_autoriza_nombre}<br>${fechaAutorizacion}</small>`;
        }

        let fila = `
            <tr class="text-center">
                <td>${formatearFecha(bitacora.fecha)}</td>
                <td>${bitacora.usuario_nombre || '-'}</td>
                <td class="text-left"><small>${bitacora.proyecto_nombre || '-'}</small></td>
                <td class="text-left"><small>${bitacora.cliente_nombre || '-'}</small></td>
                <td>${formatearHora(bitacora.hora_entrada)}</td>
                <td>${formatearHora(bitacora.hora_salida)}</td>
                <td>${horas.toFixed(2)}</td>
                <td><small>${rubroTexto}</small></td>
                <td>${lineaTexto}</td>
                <td>${badgeEstado}</td>
                <td>₡${costo.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td>${autorizadoPor}</td>
            </tr>
        `;
        tbody.append(fila);
    });

    // Inicializar DataTable
    tablaHistorialBitacoras = $('#tabla_historial_bitacoras').DataTable({
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        order: [[0, 'desc']],
        pageLength: 25
    });
}
