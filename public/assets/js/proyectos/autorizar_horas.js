var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaBitacoras;
var filtroActual = 'PENDIENTE';

$(document).ready(function () {
    cargarBitacoras('PENDIENTE');
});

function filtrarBitacoras(filtro) {
    filtroActual = filtro;
    cargarBitacoras(filtro);
}

function cargarBitacoras(filtro) {
    $.ajax({
        url: `${base_path}/proyectos/cargarBitacorasAutorizacion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro_estado: filtro
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarTabla(response['datos']);
            actualizarContadores(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar las bitácoras");
    });
}

function renderizarTabla(bitacoras) {
    if (tablaBitacoras) {
        tablaBitacoras.destroy();
    }

    let tbody = $('#tabla_bitacoras tbody');
    tbody.empty();

    if (bitacoras.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="12" class="text-center text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                    <i>No hay bitácoras ${filtroActual.toLowerCase()}</i>
                </td>
            </tr>
        `);
        return;
    }

    bitacoras.forEach(bitacora => {
        let horas = calcularHoras(bitacora.hora_entrada, bitacora.hora_salida);
        
        // Badge de estado
        let badgeEstado = '';
        let estadoCodigo = bitacora.estado_codigo || 'BIT_PROY_PENDIENTE';
        
        switch (estadoCodigo) {
            case 'BIT_PROY_PENDIENTE':
                badgeEstado = '<span class="badge badge-warning badge-xl"><i class="fas fa-clock"></i> Pendiente</span>';
                break;
            case 'BIT_PROY_APROBADA':
                badgeEstado = '<span class="badge badge-success badge-xl"><i class="fas fa-check-circle"></i> Aprobada</span>';
                break;
            case 'BIT_PROY_RECHAZADA':
                badgeEstado = '<span class="badge badge-danger badge-xl"><i class="fas fa-times-circle"></i> Rechazada</span>';
                break;
            default:
                badgeEstado = '<span class="badge badge-secondary">Sin estado</span>';
        }

        // Autorizado por
        let autorizadoPor = '-';
        if (bitacora.autorizado_por) {
            autorizadoPor = `${bitacora.autorizado_por}<br><small class="text-muted">${formatearFechaHora(bitacora.fecha_autorizacion)}</small>`;
        }

        let rubroInfo = bitacora.rubro_nombre || 'Hora Normal';
        let lineaInfo = bitacora.linea_numero ? `#${bitacora.linea_numero} - ${bitacora.linea_descripcion}` : 'Sin asignar';

        // Botones de acción
        let botones = '';
        if (estadoCodigo === 'BIT_PROY_PENDIENTE') {
            botones = `
                <button class="btn btn-sm btn-info" onclick="verDescripcion('${bitacora.proyecto_nombre}', '${bitacora.usuario_nombre}', '${bitacora.fecha}', '${bitacora.hora_entrada}', '${bitacora.hora_salida}', '${rubroInfo}', '${escapeHtml(lineaInfo)}', \`${escapeHtml(bitacora.descripcion)}\`)" title="Ver Descripción">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-success" onclick="aprobarBitacora(${bitacora.id})" title="Aprobar">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="rechazarBitacora(${bitacora.id})" title="Rechazar">
                    <i class="fas fa-times"></i>
                </button>
            `;
        } else {
            botones = `
                <button class="btn btn-sm btn-info" onclick="verDescripcion('${bitacora.proyecto_nombre}', '${bitacora.usuario_nombre}', '${bitacora.fecha}', '${bitacora.hora_entrada}', '${bitacora.hora_salida}', '${rubroInfo}', '${escapeHtml(lineaInfo)}', \`${escapeHtml(bitacora.descripcion)}\`)" title="Ver Descripción">
                    <i class="fas fa-eye"></i>
                </button>
            `;
        }

        let rubroNombre = bitacora.rubro_nombre || 'Hora Normal';
        let multiplicador = parseFloat(bitacora.rubro_multiplicador || 1.00);
        let rubroTexto = `${rubroNombre} <small class="text-muted">(×${multiplicador.toFixed(2)})</small>`;

        let lineaTexto = '-';
        if (bitacora.linea_numero) {
            lineaTexto = `<span class="badge badge-secondary" title="${bitacora.linea_descripcion || ''}">#${bitacora.linea_numero}</span>`;
        }

        let fila = `
            <tr class="text-center">
                <td>${formatearFecha(bitacora.fecha)}</td>
                <td class="text-left">${bitacora.proyecto_nombre || '-'}</td>
                <td class="text-left">${bitacora.cliente_nombre || '-'}</td>
                <td>${bitacora.usuario_nombre || '-'}</td>
                <td>${formatearHora(bitacora.hora_entrada)}</td>
                <td>${formatearHora(bitacora.hora_salida)}</td>
                <td><strong>${horas.toFixed(2)}</strong> hrs</td>
                <td>${rubroTexto}</td>
                <td>${lineaTexto}</td>
                <td>${badgeEstado}</td>
                <td>${autorizadoPor}</td>
                <td>${botones}</td>
            </tr>
        `;
        tbody.append(fila);
    });

    tablaBitacoras = $('#tabla_bitacoras').DataTable({
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
        "order": [[0, "desc"]],
        "pageLength": 25
    });
}

function actualizarContadores(bitacoras) {
    let pendientes = 0;
    let aprobadas = 0;
    let rechazadas = 0;

    bitacoras.forEach(bitacora => {
        switch (bitacora.estado_codigo) {
            case 'BIT_PROY_PENDIENTE':
                pendientes++;
                break;
            case 'BIT_PROY_APROBADA':
                aprobadas++;
                break;
            case 'BIT_PROY_RECHAZADA':
                rechazadas++;
                break;
        }
    });

    // Cargar todas las bitácoras para obtener el total real
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
            let totalPendientes = 0;
            let totalAprobadas = 0;
            let totalRechazadas = 0;

            todasBitacoras.forEach(b => {
                switch (b.estado_codigo) {
                    case 'BIT_PROY_PENDIENTE':
                        totalPendientes++;
                        break;
                    case 'BIT_PROY_APROBADA':
                        totalAprobadas++;
                        break;
                    case 'BIT_PROY_RECHAZADA':
                        totalRechazadas++;
                        break;
                }
            });

            $('#total_pendientes').text(totalPendientes);
            $('#total_aprobadas').text(totalAprobadas);
            $('#total_rechazadas').text(totalRechazadas);
            $('#total_general').text(todasBitacoras.length);
        }
    });
}

function verDescripcion(proyecto, usuario, fecha, horaEntrada, horaSalida, rubro, linea, descripcion) {
    $('#desc_proyecto').text(proyecto);
    $('#desc_usuario').text(usuario);
    $('#desc_fecha').text(formatearFecha(fecha));
    $('#desc_horario').text(`${formatearHora(horaEntrada)} - ${formatearHora(horaSalida)}`);
    $('#desc_rubro').text(rubro);
    $('#desc_linea').text(linea);
    $('#desc_contenido').text(descripcion);
    $('#modal_descripcion').modal('show');
}

function aprobarBitacora(bitacoraId) {
    if (!confirm('¿Está seguro de APROBAR esta bitácora?')) {
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
            cargarBitacoras(filtroActual);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al aprobar la bitácora");
    });
}

function rechazarBitacora(bitacoraId) {
    if (!confirm('¿Está seguro de RECHAZAR esta bitácora?')) {
        return;
    }

    $.ajax({
        url: `${base_path}/proyectos/autorizarBitacora`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            bitacora_id: bitacoraId,
            accion: 'rechazar'
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            cargarBitacoras(filtroActual);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al rechazar la bitácora");
    });
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

function formatearFechaHora(fechaHora) {
    if (!fechaHora) return '-';
    let date = new Date(fechaHora);
    return date.toLocaleString('es-CR', { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/\\/g, '\\\\')
        .replace(/`/g, '\\`')
        .replace(/\$/g, '\\$');
}

