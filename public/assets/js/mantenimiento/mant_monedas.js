$(document).ready(function() {
    // Configurar DataTable
    var tablaMonedas = $('#tabla_monedas').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": base_path + "/mant/monedas/cargar",
            "type": "POST",
            "data": function(d) {
                d._token = CSRF_TOKEN;
            }
        },
        "columns": [
            { "data": "codigo" },
            { "data": "descripcion" },
            { 
                "data": "tipo_cambio",
                "render": function(data, type, row) {
                    return parseFloat(data).toLocaleString('es-CR', {
                        minimumFractionDigits: 4,
                        maximumFractionDigits: 4
                    });
                }
            },
            { 
                "data": "estado_nombre",
                "render": function(data, type, row) {
                    return '<span class="badge badge-success">' + data + '</span>';
                }
            },
            { 
                "data": "fecha_creacion",
                "render": function(data, type, row) {
                    return formatearFecha(data);
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    let botones = '';
                    
                    // Botón editar
                    botones += '<button class="btn btn-sm btn-warning mr-1" onclick="editarMoneda(' + row.id + ')" title="Editar">';
                    botones += '<i class="fas fa-edit"></i>';
                    botones += '</button>';
                    
                    // Botón eliminar (solo si no es CRC)
                    if (row.codigo !== 'CRC') {
                        botones += '<button class="btn btn-sm btn-danger" onclick="eliminarMoneda(' + row.id + ')" title="Eliminar">';
                        botones += '<i class="fas fa-trash"></i>';
                        botones += '</button>';
                    }
                    
                    return botones;
                }
            }
        ],
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
        "order": [[0, "asc"]],
        "pageLength": 25,
        "responsive": true
    });
    
    // Hacer la tabla global para poder usarla en otras funciones
    window.tablaMonedas = tablaMonedas;
});

function cargarMonedas() {
    if (window.tablaMonedas) {
        window.tablaMonedas.ajax.reload();
    }
}

function nuevaMoneda() {
    limpiarFormulario();
    $('#titulo_modal').text('Nueva Moneda');
    $('#modal_moneda').modal('show');
}

function editarMoneda(id) {
    $.ajax({
        url: base_path + "/mant/monedas/obtener",
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            id: id
        },
        dataType: "json",
        success: function(response) {
            if (response.estado) {
                llenarFormulario(response.datos);
                $('#titulo_modal').text('Editar Moneda');
                $('#modal_moneda').modal('show');
            } else {
                showError(response.mensaje);
            }
        },
        error: function() {
            showError("Error al cargar los datos de la moneda");
        }
    });
}

function llenarFormulario(moneda) {
    $('#moneda_id').val(moneda.id);
    $('#codigo').val(moneda.codigo);
    $('#descripcion').val(moneda.descripcion);
    $('#tipo_cambio').val(moneda.tipo_cambio);
}

function limpiarFormulario() {
    $('#moneda_id').val('');
    $('#codigo').val('');
    $('#descripcion').val('');
    $('#tipo_cambio').val('1.0000');
}

function guardarMoneda() {
    let datos = {
        _token: CSRF_TOKEN,
        id: $('#moneda_id').val(),
        codigo: $('#codigo').val().toUpperCase().trim(),
        descripcion: $('#descripcion').val().trim(),
        tipo_cambio: parseFloat($('#tipo_cambio').val())
    };

    // Validaciones básicas
    if (!datos.codigo) {
        showError("El código es obligatorio");
        return;
    }

    if (!datos.descripcion) {
        showError("La descripción es obligatoria");
        return;
    }

    if (datos.tipo_cambio <= 0) {
        showError("El tipo de cambio debe ser mayor a 0");
        return;
    }

    // Validación especial para CRC
    if (datos.codigo === 'CRC' && datos.tipo_cambio != 1) {
        showError("La moneda CRC debe tener tipo de cambio 1.0000");
        return;
    }

    $.ajax({
        url: base_path + "/mant/monedas/guardar",
        type: 'POST',
        data: datos,
        dataType: "json",
        success: function(response) {
            if (response.estado) {
                showSuccess(response.mensaje);
                $('#modal_moneda').modal('hide');
                cargarMonedas();
            } else {
                showError(response.mensaje);
            }
        },
        error: function() {
            showError("Error al guardar la moneda");
        }
    });
}

function eliminarMoneda(id) {
    $('#modal_confirmar_eliminar').data('id', id);
    $('#modal_confirmar_eliminar').modal('show');
}

function confirmarEliminar() {
    let id = $('#modal_confirmar_eliminar').data('id');
    
    $.ajax({
        url: base_path + "/mant/monedas/eliminar",
        type: 'POST',
        data: {
            _token: CSRF_TOKEN,
            id: id
        },
        dataType: "json",
        success: function(response) {
            if (response.estado) {
                showSuccess(response.mensaje);
                $('#modal_confirmar_eliminar').modal('hide');
                cargarMonedas();
            } else {
                showError(response.mensaje);
            }
        },
        error: function() {
            showError("Error al eliminar la moneda");
        }
    });
}

// Función para formatear fechas
function formatearFecha(fecha) {
    if (!fecha) return '';
    
    let fechaObj = new Date(fecha);
    return fechaObj.toLocaleDateString('es-CR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para mostrar mensajes de éxito
function showSuccess(mensaje) {
    iziToast.success({
        title: 'Éxito',
        message: mensaje,
        position: 'topRight'
    });
}

// Función para mostrar mensajes de error
function showError(mensaje) {
    iziToast.error({
        title: 'Error',
        message: mensaje,
        position: 'topRight'
    });
}
