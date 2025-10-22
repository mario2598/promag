var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaProyectos;
var usuariosDisponibles = [];
var usuariosAsignados = [];
var lineasPresupuesto = [];
var proyectoGestion = {
    "id": 0,
    "cliente": "",
    "nombre": "",
    "descripcion": "",
    "usuario_encargado": "",
    "ubicacion": "",
    "usuarios_asignados": [],
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

    // Cargar usuarios
    $.ajax({
        url: `${base_path}/proyectos/cargarUsuarios`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            usuariosDisponibles = response['datos'];
            $('#proyecto_encargado').html('<option value="">Seleccione un usuario</option>');
            usuariosDisponibles.forEach(usuario => {
                $('#proyecto_encargado').append(`<option value="${usuario.id}">${usuario.nombre_completo}</option>`);
            });
        }
    });

    // Cargar estados de proyectos
    $.ajax({
        url: `${base_path}/proyectos/cargarEstados`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            let estados = response['datos'];
            $('#proyecto_estado').html('<option value="">Seleccione un estado</option>');
            estados.forEach(estado => {
                let colorClass = '';
                switch(estado.cod_general) {
                    case 'PROY_ACTIVO':
                        colorClass = 'text-success';
                        break;
                    case 'PROY_PAUSADO':
                        colorClass = 'text-warning';
                        break;
                    case 'PROY_FINALIZADO':
                        colorClass = 'text-info';
                        break;
                    case 'PROY_CANCELADO':
                        colorClass = 'text-danger';
                        break;
                }
                $('#proyecto_estado').append(`<option value="${estado.id}" class="${colorClass}">${estado.nombre}</option>`);
            });
        }
    });
}

function cargarProyectos() {
    $.ajax({
        url: `${base_path}/proyectos/cargar`,
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
        "usuario_encargado": "",
        "ubicacion": "",
        "usuarios_asignados": [],
        "estado": ""
    };

    usuariosAsignados = [];
    lineasPresupuesto = [];
    actualizarTablaUsuariosAsignados();

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
            $('#proyecto_encargado').val(proyecto.usuario_encargado).trigger('change');
            $('#proyecto_nombre').val(proyecto.nombre);
            $('#proyecto_descripcion').val(proyecto.descripcion);
            $('#proyecto_ubicacion').val(proyecto.ubicacion);
            $('#proyecto_estado').val(proyecto.estado);

            // Cargar usuarios asignados
            usuariosAsignados = proyecto.usuarios_asignados || [];
            actualizarTablaUsuariosAsignados();

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
    if (!$('#proyecto_encargado').val()) {
        showError("Debe seleccionar un usuario encargado");
        return;
    }
    if (!$('#proyecto_nombre').val().trim()) {
        showError("Debe ingresar el nombre del proyecto");
        return;
    }
    if (!$('#proyecto_estado').val()) {
        showError("Debe seleccionar el estado del proyecto");
        return;
    }

    // Capturar datos del formulario
    proyectoGestion.id = parseInt($('#proyecto_id').val()) || 0;
    proyectoGestion.cliente = $('#proyecto_cliente').val();
    proyectoGestion.nombre = $('#proyecto_nombre').val().trim();
    proyectoGestion.descripcion = $('#proyecto_descripcion').val().trim();
    proyectoGestion.usuario_encargado = $('#proyecto_encargado').val();
    proyectoGestion.ubicacion = $('#proyecto_ubicacion').val().trim();
    proyectoGestion.estado = $('#proyecto_estado').val();
    proyectoGestion.usuarios_asignados = usuariosAsignados.map(u => u.id);

    $.ajax({
        url: `${base_path}/proyectos/guardar`,
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

// Función para actualizar la tabla de usuarios asignados
function actualizarTablaUsuariosAsignados() {
    let tbody = $('#tabla_usuarios_asignados');
    tbody.empty();

    if (usuariosAsignados.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i>No hay usuarios asignados</i>
                </td>
            </tr>
        `);
        return;
    }

    usuariosAsignados.forEach(usuario => {
        let btnVerBitacoras = '';
        // Solo mostrar botón de bitácoras si el proyecto ya está guardado
        if (proyectoGestion.id && proyectoGestion.id != 0) {
            btnVerBitacoras = `
                <button class="btn btn-sm btn-info" onclick="verBitacorasUsuario(${usuario.id}, '${usuario.nombre_completo}', ${usuario.precio_hora})" title="Ver Bitácoras">
                    <i class="fas fa-clipboard-list"></i>
                </button>
            `;
        }

        let fila = `
            <tr class="text-center">
                <td>${usuario.nombre_completo}</td>
                <td>${usuario.rol_nombre}</td>
                <td>₡${parseFloat(usuario.precio_hora || 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td>
                    ${btnVerBitacoras}
                    <button class="btn btn-sm btn-danger" onclick="eliminarUsuarioAsignado(${usuario.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });
}

// Función para abrir modal de agregar usuarios
function abrirModalAgregarUsuario() {
    // Filtrar usuarios que no están asignados
    let usuariosNoAsignados = usuariosDisponibles.filter(u => 
        !usuariosAsignados.find(ua => ua.id === u.id)
    );

    let tbody = $('#tbody_usuarios_disponibles');
    tbody.empty();

    if (usuariosNoAsignados.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i>Todos los usuarios ya están asignados</i>
                </td>
            </tr>
        `);
        $('#check_todos').hide();
    } else {
        $('#check_todos').show().prop('checked', false);
        usuariosNoAsignados.forEach(usuario => {
            let fila = `
                <tr class="text-center">
                    <td>
                        <input type="checkbox" class="check_usuario" value="${usuario.id}" 
                            data-nombre="${usuario.nombre_completo}" 
                            data-rol="${usuario.rol_nombre}" 
                            data-precio="${usuario.precio_hora || 0}">
                    </td>
                    <td>${usuario.nombre_completo}</td>
                    <td>${usuario.rol_nombre}</td>
                    <td>₡${parseFloat(usuario.precio_hora || 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
            tbody.append(fila);
        });
    }

    $('#modal_agregar_usuario').modal('show');
}

// Función para seleccionar/deseleccionar todos los usuarios
function seleccionarTodos() {
    let checked = $('#check_todos').is(':checked');
    $('.check_usuario').prop('checked', checked);
}

// Función para agregar usuarios seleccionados
function agregarUsuariosSeleccionados() {
    $('.check_usuario:checked').each(function() {
        let usuario = {
            id: parseInt($(this).val()),
            nombre_completo: $(this).data('nombre'),
            rol_nombre: $(this).data('rol'),
            precio_hora: $(this).data('precio')
        };
        usuariosAsignados.push(usuario);
    });

    actualizarTablaUsuariosAsignados();
    $('#modal_agregar_usuario').modal('hide');
}

// Función para eliminar un usuario asignado
function eliminarUsuarioAsignado(usuarioId) {
    usuariosAsignados = usuariosAsignados.filter(u => u.id !== usuarioId);
    actualizarTablaUsuariosAsignados();
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

// ==================== FUNCIONES DE VER BITÁCORAS ====================

function verBitacorasUsuario(usuarioId, nombreUsuario, precioHora) {
    $('#nombre_usuario_bitacoras').text(nombreUsuario);
    
    $.ajax({
        url: `${base_path}/proyectos/cargarBitacoraUsuario`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            proyecto_id: proyectoGestion.id,
            usuario_id: usuarioId
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarBitacorasUsuario(response['datos'], precioHora);
            $('#modal_ver_bitacoras').modal('show');
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar las bitácoras");
    });
}

function renderizarBitacorasUsuario(bitacoras, precioHora) {
    let tbody = $('#tabla_ver_bitacoras');
    tbody.empty();

    if (bitacoras.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center text-muted">
                    <i>No hay registros de bitácora</i>
                </td>
            </tr>
        `);
        $('#ver_total_horas').text('0.00 hrs');
        $('#ver_total_costo').text('₡0.00');
        return;
    }

    let totalHoras = 0;
    let totalCosto = 0;

    bitacoras.forEach(bitacora => {
        let horas = calcularHoras(bitacora.hora_entrada, bitacora.hora_salida);
        let multiplicador = parseFloat(bitacora.rubro_multiplicador || 1.00);
        let costo = horas * precioHora * multiplicador;

        // Solo sumar aprobadas
        if (bitacora.estado_codigo === 'BIT_PROY_APROBADA') {
            totalHoras += horas;
            totalCosto += costo;
        }

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
            lineaTexto = `<span class="badge badge-secondary" title="${bitacora.linea_descripcion || ''}">#${bitacora.linea_numero}</span>`;
        }

        let fila = `
            <tr class="text-center">
                <td>${formatearFecha(bitacora.fecha)}</td>
                <td>${formatearHora(bitacora.hora_entrada)}</td>
                <td>${formatearHora(bitacora.hora_salida)}</td>
                <td>${horas.toFixed(2)} hrs</td>
                <td>${rubroTexto}</td>
                <td>${lineaTexto}</td>
                <td class="text-left"><small>${bitacora.descripcion}</small></td>
                <td>${badgeEstado}</td>
                <td>₡${costo.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
        tbody.append(fila);
    });

    $('#ver_total_horas').text(totalHoras.toFixed(2) + ' hrs');
    $('#ver_total_costo').text('₡' + totalCosto.toLocaleString('es-CR', {minimumFractionDigits: 2}));
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

