var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaProyectos;
var lineasPresupuesto = [];
var proyectoGestion = {
    "id": 0,
    "cliente": "",
    "nombre": "",
    "descripcion": "",
    "ubicacion": "",
    "estado": ""
};

$(document).ready(function () {
    inicializarSelect2();
    cargarDatosIniciales();
    cargarProyectos();
});

function inicializarSelect2() {
    $('.select2').select2({
        width: '100%'
    });
}

function cargarDatosIniciales() {
    // Cargar clientes
    $.ajax({
        url: `${base_path}/proyectos/cargarClientes`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            let clientes = response['datos'];
            $('#proyecto_cliente').html('<option value="">Seleccione un cliente</option>');
            clientes.forEach(cliente => {
                $('#proyecto_cliente').append(`<option value="${cliente.id}">${cliente.nombre}</option>`);
            });
        }
    });
}

function cargarProyectos() {
    $.ajax({
        url: `${base_path}/proyectos/cotizaciones/cargar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            cargarTablaProyectos(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar los proyectos");
    });
}

function cargarTablaProyectos(proyectos) {
    if (tablaProyectos) {
        tablaProyectos.destroy();
    }

    $('#tabla_proyectos tbody').empty();

    proyectos.forEach(proyecto => {
        let badgeClass = '';
        switch (proyecto.estado_codigo) {
            case 'PROY_ACTIVO':
                badgeClass = 'badge-success';
                break;
            case 'PROY_PAUSADO':
                badgeClass = 'badge-warning';
                break;
            case 'PROY_FINALIZADO':
                badgeClass = 'badge-info';
                break;
            case 'PROY_CANCELADO':
                badgeClass = 'badge-danger';
                break;
            default:
                badgeClass = 'badge-secondary';
        }

        let fila = `
            <tr>
                <td>${proyecto.id}</td>
                <td>${proyecto.cliente_nombre}</td>
                <td>${proyecto.nombre}</td>
                <td>${proyecto.encargado_nombre}</td>
                <td>${proyecto.ubicacion || '-'}</td>
                <td><span class="badge ${badgeClass}">${proyecto.estado_nombre}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editarProyecto(${proyecto.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tabla_proyectos tbody').append(fila);
    });

    tablaProyectos = $('#tabla_proyectos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        "order": [[0, "desc"]]
    });
}

function nuevoProyecto() {
    proyectoGestion = {
        "id": 0,
        "cliente": "",
        "nombre": "",
        "descripcion": "",
        "ubicacion": "",
        "estado": ""
    };
    lineasPresupuesto = [];

    // Ocultar sección de líneas para nuevo proyecto
    $('#seccion_lineas_presupuesto').hide();

    $('#titulo_modal').html('Nuevo Proyecto');
    $('#proyecto_id').val(0);
    $('#proyecto_cliente').val('').trigger('change');
    $('#proyecto_encargado').val('').trigger('change');
    $('#proyecto_nombre').val('');
    $('#proyecto_descripcion').val('');
    $('#proyecto_ubicacion').val('');
    $('#proyecto_estado').val('');

    $('#modal_proyecto').modal('show');
}

function editarProyecto(id) {
    $.ajax({
        url: `${base_path}/proyectos/cargarProyecto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idProyecto: id
        }
    }).done(function (response) {
        if (response['estado']) {
            let proyecto = response['datos'];
            proyectoGestion = proyecto;

            // Verificar estado del proyecto para cargar info adicional
            $.ajax({
                url: `${base_path}/proyectos/cargar`,
                type: 'post',
                dataType: "json",
                data: { _token: CSRF_TOKEN }
            }).done(function (resp) {
                if (resp['estado']) {
                    let proyectoCompleto = resp['datos'].find(p => p.id == id);
                    if (proyectoCompleto) {
                        proyectoGestion.estado_codigo = proyectoCompleto.estado_codigo;
                    }
                }
            });

            $('#titulo_modal').html('Editar Proyecto');
            $('#proyecto_id').val(proyecto.id);
            $('#proyecto_cliente').val(proyecto.cliente).trigger('change');
            $('#proyecto_nombre').val(proyecto.nombre);
            $('#proyecto_descripcion').val(proyecto.descripcion);
            $('#proyecto_ubicacion').val(proyecto.ubicacion);

            // Mostrar y cargar líneas de presupuesto
            $('#seccion_lineas_presupuesto').show();
            cargarLineasPresupuesto(proyecto.id);

            $('#modal_proyecto').modal('show');
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar el proyecto");
    });
}

function guardarProyecto() {
    // Validar campos requeridos
    if (!$('#proyecto_cliente').val()) {
        showError("Debe seleccionar un cliente");
        return;
    }
    if (!$('#proyecto_nombre').val().trim()) {
        showError("Debe ingresar el nombre del proyecto");
        return;
    }

    // Capturar datos del formulario
    proyectoGestion.id = parseInt($('#proyecto_id').val()) || 0;
    proyectoGestion.cliente = $('#proyecto_cliente').val();
    proyectoGestion.nombre = $('#proyecto_nombre').val().trim();
    proyectoGestion.descripcion = $('#proyecto_descripcion').val().trim();
    proyectoGestion.ubicacion = $('#proyecto_ubicacion').val().trim();

    $.ajax({
        url: `${base_path}/proyectos/cotizaciones/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            proyecto: proyectoGestion
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_proyecto').modal('hide');
            cargarProyectos();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al guardar el proyecto");
    });
}



// ==================== FUNCIONES DE LÍNEAS DE PRESUPUESTO ====================

function cargarLineasPresupuesto(proyectoId) {
    $.ajax({
        url: `${base_path}/proyectos/cargarLineasPresupuesto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            proyecto_id: proyectoId
        }
    }).done(function (response) {
        if (response['estado']) {
            lineasPresupuesto = response['datos'];
            renderizarLineasPresupuesto();
        }
    });
}

function renderizarLineasPresupuesto() {
    let tbody = $('#tabla_lineas_presupuesto');
    tbody.empty();

    if (lineasPresupuesto.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i>No hay líneas de presupuesto</i>
                </td>
            </tr>
        `);
        actualizarTotalesPresupuesto();
        return;
    }

    lineasPresupuesto.forEach(linea => {
        let montoAutorizado = parseFloat(linea.monto_autorizado || 0);
        let montoConsumido = parseFloat(linea.monto_consumido || 0);
        let disponible = montoAutorizado - montoConsumido;
        
        let colorDisponible = disponible >= 0 ? 'text-success' : 'text-danger';

        let fila = `
            <tr class="text-center">
                <td><strong>${linea.numero_linea}</strong></td>
                <td class="text-left">${linea.descripcion}</td>
                <td>₡${montoAutorizado.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td>₡${montoConsumido.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td class="${colorDisponible}"><strong>₡${disponible.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editarLineaPresupuesto(${linea.id}, ${linea.numero_linea}, '${escapeHtml(linea.descripcion)}', ${montoAutorizado}, ${montoConsumido})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarLineaPresupuesto(${linea.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });

    actualizarTotalesPresupuesto();
}

function actualizarTotalesPresupuesto() {
    let totalAutorizado = 0;
    let totalConsumido = 0;

    lineasPresupuesto.forEach(linea => {
        totalAutorizado += parseFloat(linea.monto_autorizado || 0);
        totalConsumido += parseFloat(linea.monto_consumido || 0);
    });

    let totalDisponible = totalAutorizado - totalConsumido;
    let colorDisponible = totalDisponible >= 0 ? 'text-success' : 'text-danger';

    $('#total_autorizado').text('₡' + totalAutorizado.toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#total_consumido').text('₡' + totalConsumido.toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#total_disponible').html(`<span class="${colorDisponible}">₡${totalDisponible.toLocaleString('es-CR', {minimumFractionDigits: 2})}</span>`);
}

function agregarLineaPresupuesto() {
    // Calcular el siguiente número de línea
    let siguienteNumero = 1;
    if (lineasPresupuesto.length > 0) {
        let maxNumero = Math.max(...lineasPresupuesto.map(l => parseInt(l.numero_linea)));
        siguienteNumero = maxNumero + 1;
    }

    $('#linea_id').val('0');
    $('#linea_numero').val(siguienteNumero);
    $('#linea_descripcion').val('');
    $('#linea_monto_autorizado').val('0');
    $('#linea_monto_consumido').val('0');
    $('#info_monto_consumido').hide();
    $('#titulo_modal_linea').html('<i class="fas fa-dollar-sign"></i> Nueva Línea de Presupuesto #' + siguienteNumero);
    $('#modal_linea_presupuesto').modal('show');
}

function editarLineaPresupuesto(id, numeroLinea, descripcion, montoAutorizado, montoConsumido) {
    $('#linea_id').val(id);
    $('#linea_numero').val(numeroLinea);
    $('#linea_descripcion').val(descripcion);
    $('#linea_monto_autorizado').val(montoAutorizado);
    $('#linea_monto_consumido').val(montoConsumido);
    $('#display_monto_consumido').text(parseFloat(montoConsumido).toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#info_monto_consumido').show();
    $('#titulo_modal_linea').html('<i class="fas fa-edit"></i> Editar Línea de Presupuesto');
    $('#modal_linea_presupuesto').modal('show');
}

function guardarLineaPresupuesto() {
    let id = $('#linea_id').val();
    let numeroLinea = $('#linea_numero').val();
    let descripcion = $('#linea_descripcion').val().trim();
    let montoAutorizado = $('#linea_monto_autorizado').val();

    if (!descripcion || !montoAutorizado) {
        showError("La descripción y el monto autorizado son requeridos");
        return;
    }

    if (proyectoGestion.id == 0) {
        showError("Primero debe guardar el proyecto");
        return;
    }

    // Si no hay número de línea (no debería pasar), calcularlo
    if (!numeroLinea || numeroLinea == '') {
        let siguienteNumero = 1;
        if (lineasPresupuesto.length > 0) {
            let maxNumero = Math.max(...lineasPresupuesto.map(l => parseInt(l.numero_linea)));
            siguienteNumero = maxNumero + 1;
        }
        numeroLinea = siguienteNumero;
    }

    $.ajax({
        url: `${base_path}/proyectos/guardarLineaPresupuesto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: id,
            proyecto_id: proyectoGestion.id,
            numero_linea: numeroLinea,
            descripcion: descripcion,
            monto_autorizado: montoAutorizado
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_linea_presupuesto').modal('hide');
            cargarLineasPresupuesto(proyectoGestion.id);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al guardar la línea de presupuesto");
    });
}

function eliminarLineaPresupuesto(id) {
    if (!confirm('¿Está seguro de eliminar esta línea de presupuesto?')) {
        return;
    }

    $.ajax({
        url: `${base_path}/proyectos/eliminarLineaPresupuesto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: id
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            cargarLineasPresupuesto(proyectoGestion.id);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al eliminar la línea de presupuesto");
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

