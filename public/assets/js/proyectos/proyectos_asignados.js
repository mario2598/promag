var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaProyectosAsignados;
var proyectoActual = null;
var usuarioBitacoraActual = null;
var bitacoraEditando = null;
var rubrosDisponibles = [];
var lineasPresupuestoDisponibles = [];

$(document).ready(function () {
    cargarProyectosAsignados();
    cargarRubros();
    
    // Calcular horas trabajadas al cambiar entrada o salida
    $('#bitacora_hora_entrada, #bitacora_hora_salida').on('change', function() {
        calcularHorasTrabajadas();
    });

    // Actualizar preview del costo al cambiar rubro
    $('#bitacora_rubro').on('change', function() {
        calcularHorasTrabajadas();
    });
});

function cargarRubros() {
    $.ajax({
        url: `${base_path}/proyectos/cargarRubros`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            rubrosDisponibles = response['datos'];
            // Cargar rubros en el select
            let select = $('#bitacora_rubro');
            select.empty();
            select.append('<option value="">Seleccione un rubro...</option>');
            rubrosDisponibles.forEach(rubro => {
                select.append(`<option value="${rubro.id}" data-multiplicador="${rubro.multiplicador}">${rubro.nombre} (×${parseFloat(rubro.multiplicador).toFixed(2)})</option>`);
            });
        }
    });
}

function cargarProyectosAsignados() {
    $.ajax({
        url: `${base_path}/proyectos/cargarProyectosAsignados`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            cargarTablaProyectosAsignados(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar los proyectos asignados");
    });
}

function cargarTablaProyectosAsignados(proyectos) {
    if (tablaProyectosAsignados) {
        tablaProyectosAsignados.destroy();
    }

    $('#tabla_proyectos_asignados tbody').empty();

    if (proyectos.length === 0) {
        $('#tabla_proyectos_asignados tbody').append(`
            <tr>
                <td colspan="8" class="text-center text-muted">
                    <i>No estás asignado a ningún proyecto</i>
                </td>
            </tr>
        `);
        return;
    }

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

        // Determinar el rol del usuario en el proyecto
        let miRol = '';
        if (proyecto.es_encargado) {
            miRol = '<span class="badge badge-primary"><i class="fas fa-user-tie"></i> Encargado</span>';
        } else if (proyecto.es_equipo) {
            miRol = '<span class="badge badge-info"><i class="fas fa-users"></i> Equipo</span>';
        }

        let fila = `
            <tr class="text-center">
                <td>${proyecto.id}</td>
                <td>${proyecto.cliente_nombre}</td>
                <td class="text-left">${proyecto.nombre}</td>
                <td>${proyecto.encargado_nombre}</td>
                <td>${miRol}</td>
                <td>
                    <span class="badge badge-secondary">
                        <i class="fas fa-users"></i> ${proyecto.total_usuarios || 0}
                    </span>
                </td>
                <td><span class="badge ${badgeClass}">${proyecto.estado_nombre}</span></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="verDetalleProyecto(${proyecto.id})" title="Ver Detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tabla_proyectos_asignados tbody').append(fila);
    });

    tablaProyectosAsignados = $('#tabla_proyectos_asignados').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        "order": [[0, "desc"]]
    });
}

function verDetalleProyecto(id) {
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
            
            // Cargar datos básicos
            $('#detalle_nombre').text(proyecto.nombre || '-');
            $('#detalle_ubicacion').text(proyecto.ubicacion || 'No especificada');
            $('#detalle_descripcion').text(proyecto.descripcion || 'Sin descripción');
            
            // Cargar información completa del proyecto
            $.ajax({
                url: `${base_path}/proyectos/cargarProyectosAsignados`,
                type: 'post',
                dataType: "json",
                data: { _token: CSRF_TOKEN }
            }).done(function (resp) {
                if (resp['estado']) {
                    let proyectoCompleto = resp['datos'].find(p => p.id == id);
                    if (proyectoCompleto) {
                        $('#detalle_cliente').text(proyectoCompleto.cliente_nombre);
                        $('#detalle_encargado').text(proyectoCompleto.encargado_nombre);
                        
                        let badgeClass = '';
                        switch (proyectoCompleto.estado_codigo) {
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
                        }
                        $('#detalle_estado').html(`<span class="badge ${badgeClass}">${proyectoCompleto.estado_nombre}</span>`);
                    }
                }
            });

            // Cargar equipo del proyecto
            let tbodyEquipo = $('#detalle_equipo');
            tbodyEquipo.empty();

            // Guardar proyecto actual para bitácora
            proyectoActual = proyecto;

            // Verificar si el usuario actual es encargado
            $.ajax({
                url: `${base_path}/proyectos/cargarProyectosAsignados`,
                type: 'post',
                dataType: "json",
                data: { _token: CSRF_TOKEN }
            }).done(function (resp) {
                if (resp['estado']) {
                    let proyectoCompleto = resp['datos'].find(p => p.id == id);
                    let esEncargado = proyectoCompleto ? proyectoCompleto.es_encargado : false;
                    
                    // Mostrar u ocultar columna de acciones según si es encargado
                    if (esEncargado) {
                        $('#th_acciones_equipo').show();
                    } else {
                        $('#th_acciones_equipo').hide();
                    }

                    if (proyecto.usuarios_asignados && proyecto.usuarios_asignados.length > 0) {
                        proyecto.usuarios_asignados.forEach(usuario => {
                            let btnBitacora = '';
                            if (esEncargado) {
                                btnBitacora = `
                                    <button class="btn btn-sm btn-primary" onclick="abrirBitacoraUsuario(${proyecto.id}, ${usuario.id}, '${usuario.nombre_completo}', ${usuario.precio_hora})" title="Gestionar Bitácora">
                                        <i class="fas fa-clipboard-list"></i>
                                    </button>
                                `;
                            }
                            
                            let fila = `
                                <tr>
                                    <td>${usuario.nombre_completo}</td>
                                    <td>${usuario.rol_nombre}</td>
                                    <td>₡${parseFloat(usuario.precio_hora || 0).toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                                    ${esEncargado ? `<td>${btnBitacora}</td>` : ''}
                                </tr>
                            `;
                            tbodyEquipo.append(fila);
                        });
                    } else {
                        let colspan = esEncargado ? 4 : 3;
                        tbodyEquipo.append(`
                            <tr>
                                <td colspan="${colspan}" class="text-center text-muted">
                                    <i>No hay usuarios asignados a este proyecto</i>
                                </td>
                            </tr>
                        `);
                    }
                }
            });

            $('#modal_detalle_proyecto').modal('show');
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar el detalle del proyecto");
    });
}

// ==================== FUNCIONES DE BITÁCORA ====================

function abrirBitacoraUsuario(proyectoId, usuarioId, nombreUsuario, precioHora) {
    $('#bitacora_proyecto_id').val(proyectoId);
    $('#bitacora_usuario_id').val(usuarioId);
    $('#nombre_usuario_bitacora').text(nombreUsuario);
    $('#precio_hora_usuario').text('₡' + parseFloat(precioHora || 0).toLocaleString('es-CR', {minimumFractionDigits: 2}));
    
    usuarioBitacoraActual = {
        id: usuarioId,
        nombre: nombreUsuario,
        precio_hora: precioHora
    };

    cancelarFormularioBitacora();
    cargarLineasPresupuesto(proyectoId);
    cargarBitacorasUsuario(proyectoId, usuarioId);
    $('#modal_bitacora_usuario').modal('show');
}

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
            lineasPresupuestoDisponibles = response['datos'];
            // Cargar líneas en el select
            let select = $('#bitacora_linea_presupuesto');
            select.empty();
            select.append('<option value="">Sin asignar</option>');
            lineasPresupuestoDisponibles.forEach(linea => {
                let disponible = parseFloat(linea.monto_autorizado) - parseFloat(linea.monto_consumido);
                select.append(`<option value="${linea.id}">#${linea.numero_linea} - ${linea.descripcion} (Disp: ₡${disponible.toLocaleString('es-CR', {minimumFractionDigits: 0})})</option>`);
            });
        }
    });
}

function cargarBitacorasUsuario(proyectoId, usuarioId) {
    $.ajax({
        url: `${base_path}/proyectos/cargarBitacoraUsuario`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            proyecto_id: proyectoId,
            usuario_id: usuarioId
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarBitacoras(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar las bitácoras");
    });
}

function renderizarBitacoras(bitacoras) {
    let tbody = $('#tabla_bitacoras_usuario');
    tbody.empty();

    let totalHoras = 0;
    let precioHora = usuarioBitacoraActual.precio_hora || 0;

    if (bitacoras.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="10" class="text-center text-muted">
                    <i>No hay registros de bitácora</i>
                </td>
            </tr>
        `);
        $('#total_horas').text('0.00 hrs');
        $('#costo_total_usuario').text('₡0.00');
        return;
    }

    let costoTotalGeneral = 0;

    bitacoras.forEach(bitacora => {
        let horas = calcularHorasEntreFechas(bitacora.hora_entrada, bitacora.hora_salida);
        let multiplicador = parseFloat(bitacora.rubro_multiplicador || 1.00);
        let costo = horas * precioHora * multiplicador;
        
        // Solo sumar si está aprobada
        if (bitacora.estado_codigo === 'BIT_PROY_APROBADA') {
            totalHoras += horas;
            costoTotalGeneral += costo;
        }

        // Badge de estado
        let badgeEstado = '';
        let botonesAccion = '';
        
        // Si no hay código de estado, asumimos que es pendiente
        let estadoCodigo = bitacora.estado_codigo || 'BIT_PROY_PENDIENTE';
        
        switch (estadoCodigo) {
            case 'BIT_PROY_PENDIENTE':
                badgeEstado = '<span class="badge badge-warning">Pendiente</span>';
                botonesAccion = `
                    <button class="btn btn-sm btn-warning" onclick="editarBitacora(${bitacora.id}, '${bitacora.fecha}', '${bitacora.hora_entrada}', '${bitacora.hora_salida}', ${bitacora.rubro_extra_salario || 1}, ${bitacora.linea_presupuesto || 'null'}, \`${bitacora.descripcion.replace(/`/g, '\\`')}\`)" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarBitacora(${bitacora.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                break;
            case 'BIT_PROY_APROBADA':
                badgeEstado = '<span class="badge badge-success">Aprobada</span>';
                botonesAccion = '<span class="text-muted">-</span>';
                break;
            case 'BIT_PROY_RECHAZADA':
                badgeEstado = '<span class="badge badge-danger">Rechazada</span>';
                botonesAccion = '<span class="text-muted">-</span>';
                break;
            default:
                badgeEstado = '<span class="badge badge-secondary">Sin estado</span>';
                botonesAccion = `
                    <button class="btn btn-sm btn-warning" onclick="editarBitacora(${bitacora.id}, '${bitacora.fecha}', '${bitacora.hora_entrada}', '${bitacora.hora_salida}', ${bitacora.rubro_extra_salario || 1}, ${bitacora.linea_presupuesto || 'null'}, \`${bitacora.descripcion.replace(/`/g, '\\`')}\`)" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                `;
        }

        let rubroNombre = bitacora.rubro_nombre || 'Hora Normal';
        let rubroTexto = `${rubroNombre} <small class="text-muted">(×${multiplicador.toFixed(2)})</small>`;

        let lineaTexto = '-';
        if (bitacora.linea_numero) {
            lineaTexto = `<span class="badge badge-secondary">#${bitacora.linea_numero}</span>`;
        }

        let fila = `
            <tr class="text-center">
                <td>${formatearFecha(bitacora.fecha)}</td>
                <td>${formatearHora(bitacora.hora_entrada)}</td>
                <td>${formatearHora(bitacora.hora_salida)}</td>
                <td>${horas.toFixed(2)} hrs</td>
                <td>${rubroTexto}</td>
                <td>${lineaTexto}</td>
                <td class="text-left">${bitacora.descripcion}</td>
                <td>${badgeEstado}</td>
                <td>₡${costo.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                <td>${botonesAccion}</td>
            </tr>
        `;
        tbody.append(fila);
    });

    $('#total_horas').text(totalHoras.toFixed(2) + ' hrs');
    $('#costo_total_usuario').text('₡' + costoTotalGeneral.toLocaleString('es-CR', {minimumFractionDigits: 2}));
}

function abrirFormularioBitacora() {
    bitacoraEditando = null;
    $('#bitacora_fecha').val('');
    $('#bitacora_hora_entrada').val('');
    $('#bitacora_hora_salida').val('');
    $('#bitacora_rubro').val('1'); // Default: Hora Normal
    $('#bitacora_linea_presupuesto').val(''); // Default: Sin asignar
    $('#bitacora_descripcion').val('');
    $('#bitacora_horas_trabajadas').val('');
    $('#bitacora_costo_preview').val('');
    $('#bitacora_multiplicador_preview').text('×1.00');
    $('#formulario_bitacora').slideDown();
}

function cancelarFormularioBitacora() {
    bitacoraEditando = null;
    $('#formulario_bitacora').slideUp();
}

function editarBitacora(id, fecha, horaEntrada, horaSalida, rubroId, lineaPresupuestoId, descripcion) {
    bitacoraEditando = id;
    $('#bitacora_fecha').val(fecha);
    $('#bitacora_hora_entrada').val(horaEntrada);
    $('#bitacora_hora_salida').val(horaSalida);
    $('#bitacora_rubro').val(rubroId);
    $('#bitacora_linea_presupuesto').val(lineaPresupuestoId || '');
    $('#bitacora_descripcion').val(descripcion);
    calcularHorasTrabajadas();
    $('#formulario_bitacora').slideDown();
}

function guardarBitacora() {
    let fecha = $('#bitacora_fecha').val();
    let horaEntrada = $('#bitacora_hora_entrada').val();
    let horaSalida = $('#bitacora_hora_salida').val();
    let rubroId = $('#bitacora_rubro').val();
    let lineaPresupuestoId = $('#bitacora_linea_presupuesto').val();
    let descripcion = $('#bitacora_descripcion').val().trim();

    if (!fecha || !horaEntrada || !horaSalida || !rubroId || !descripcion) {
        showError("Todos los campos son requeridos");
        return;
    }

    // Validar que hora salida sea mayor que hora entrada
    if (horaEntrada >= horaSalida) {
        showError("La hora de salida debe ser mayor que la hora de entrada");
        return;
    }

    let dataToSend = {
        _token: CSRF_TOKEN,
        proyecto_id: $('#bitacora_proyecto_id').val(),
        usuario_id: $('#bitacora_usuario_id').val(),
        fecha: fecha,
        hora_entrada: horaEntrada,
        hora_salida: horaSalida,
        rubro_id: rubroId,
        linea_presupuesto_id: lineaPresupuestoId || null,
        descripcion: descripcion
    };

    // Si estamos editando, agregar el ID
    if (bitacoraEditando) {
        dataToSend.bitacora_id = bitacoraEditando;
    }

    $.ajax({
        url: `${base_path}/proyectos/guardarBitacora`,
        type: 'post',
        dataType: "json",
        data: dataToSend
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            cancelarFormularioBitacora();
            cargarBitacorasUsuario($('#bitacora_proyecto_id').val(), $('#bitacora_usuario_id').val());
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al guardar la bitácora");
    });
}

function eliminarBitacora(bitacoraId) {
    if (!confirm('¿Está seguro de eliminar este registro?')) {
        return;
    }

    $.ajax({
        url: `${base_path}/proyectos/eliminarBitacora`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            bitacora_id: bitacoraId
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            cargarBitacorasUsuario($('#bitacora_proyecto_id').val(), $('#bitacora_usuario_id').val());
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al eliminar el registro");
    });
}

function calcularHorasTrabajadas() {
    let horaEntrada = $('#bitacora_hora_entrada').val();
    let horaSalida = $('#bitacora_hora_salida').val();
    let rubroId = $('#bitacora_rubro').val();

    if (horaEntrada && horaSalida) {
        let horas = calcularHorasEntreFechas(horaEntrada, horaSalida);
        $('#bitacora_horas_trabajadas').val(horas.toFixed(2) + ' hrs');

        // Calcular costo con multiplicador
        if (rubroId && usuarioBitacoraActual) {
            let precioHora = usuarioBitacoraActual.precio_hora || 0;
            let multiplicador = 1.00;
            
            // Obtener multiplicador del rubro seleccionado
            let rubroSeleccionado = rubrosDisponibles.find(r => r.id == rubroId);
            if (rubroSeleccionado) {
                multiplicador = parseFloat(rubroSeleccionado.multiplicador);
            }
            
            let costoCalculado = horas * precioHora * multiplicador;
            $('#bitacora_costo_preview').val(costoCalculado.toLocaleString('es-CR', {minimumFractionDigits: 2}));
            $('#bitacora_multiplicador_preview').text('×' + multiplicador.toFixed(2));
        }
    } else {
        $('#bitacora_horas_trabajadas').val('');
        $('#bitacora_costo_preview').val('');
    }
}

function calcularHorasEntreFechas(horaInicio, horaFin) {
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

