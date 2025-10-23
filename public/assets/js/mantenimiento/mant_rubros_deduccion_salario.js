var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaRubros;

$(document).ready(function () {
    cargarRubros();

    // Actualizar preview del cálculo al cambiar el porcentaje
    $('#rubro_porcentaje').on('input', function() {
        actualizarPreview();
    });
});

function cargarRubros() {
    $.ajax({
        url: `${base_path}/mant/rubrosdeduccionsalario/cargar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (response['estado']) {
            renderizarTabla(response['datos']);
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al cargar los rubros");
    });
}

function renderizarTabla(rubros) {
    if (tablaRubros) {
        tablaRubros.destroy();
    }

    let tbody = $('#tabla_rubros tbody');
    tbody.empty();

    if (rubros.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                    <i>No hay rubros registrados</i>
                </td>
            </tr>
        `);
        return;
    }

    rubros.forEach(rubro => {
        let salarioBase = 100000;
        let porcentaje = parseFloat(rubro.porcentaje_deduccion);
        let deduccion = salarioBase * (porcentaje / 100);
        let salarioNeto = salarioBase - deduccion;
        
        let fila = `
            <tr class="text-center">
                <td>${rubro.id}</td>
                <td class="text-left"><strong>${rubro.nombre}</strong></td>
                <td class="text-left">${rubro.descripcion || '-'}</td>
                <td>
                    <span class="badge badge-danger badge-lg">
                        <i class="fas fa-percentage"></i> ${porcentaje.toFixed(2)}%
                    </span>
                </td>
                <td>
                    <span class="text-muted">₡100,000 - ${porcentaje.toFixed(2)}% =</span><br>
                    <span class="text-danger">-₡${deduccion.toLocaleString('es-CR', {minimumFractionDigits: 2})}</span><br>
                    <span class="text-success">Neto: ₡${salarioNeto.toLocaleString('es-CR', {minimumFractionDigits: 2})}</span>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editarRubro(${rubro.id}, '${escapeHtml(rubro.nombre)}', '${escapeHtml(rubro.descripcion || '')}', ${rubro.porcentaje_deduccion})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarRubro(${rubro.id}, '${escapeHtml(rubro.nombre)}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });

    tablaRubros = $('#tabla_rubros').DataTable({
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
        "order": [[0, "asc"]]
    });
}

function nuevoRubro() {
    $('#rubro_id').val('-1');
    $('#rubro_nombre').val('');
    $('#rubro_descripcion').val('');
    $('#rubro_porcentaje').val('0.00');
    $('#titulo_modal_rubro').html('<i class="fas fa-minus-circle"></i> Nuevo Rubro Deducción Salarial');
    actualizarPreview();
    $('#modal_rubro').modal('show');
}

function editarRubro(id, nombre, descripcion, porcentaje) {
    $('#rubro_id').val(id);
    $('#rubro_nombre').val(nombre);
    $('#rubro_descripcion').val(descripcion);
    $('#rubro_porcentaje').val(porcentaje);
    $('#titulo_modal_rubro').html('<i class="fas fa-edit"></i> Editar Rubro Deducción Salarial');
    actualizarPreview();
    $('#modal_rubro').modal('show');
}

function guardarRubro() {
    let id = $('#rubro_id').val();
    let nombre = $('#rubro_nombre').val().trim();
    let descripcion = $('#rubro_descripcion').val().trim();
    let porcentaje = $('#rubro_porcentaje').val();

    // Validaciones
    if (!nombre) {
        showError("El nombre es requerido");
        $('#rubro_nombre').focus();
        return;
    }

    if (!porcentaje || parseFloat(porcentaje) < 0 || parseFloat(porcentaje) > 100) {
        showError("El porcentaje debe estar entre 0 y 100");
        $('#rubro_porcentaje').focus();
        return;
    }

    $.ajax({
        url: `${base_path}/mant/rubrosdeduccionsalario/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: id,
            nombre: nombre,
            descripcion: descripcion,
            porcentaje_deduccion: porcentaje
        }
    }).done(function (response) {
        if (response['estado']) {
            showSuccess(response['mensaje']);
            $('#modal_rubro').modal('hide');
            cargarRubros();
        } else {
            showError(response['mensaje']);
        }
    }).fail(function () {
        showError("Error al guardar el rubro");
    });
}

function eliminarRubro(id, nombre) {
    // Mostrar modal de confirmación formal
    mostrarModalConfirmacion(
        'Eliminar Rubro de Deducción',
        `¿Está seguro de que desea eliminar el rubro <strong>"${nombre}"</strong>?<br><br>
         <div class="alert alert-warning">
             <i class="fas fa-exclamation-triangle"></i> 
             <strong>Advertencia:</strong> Esta acción no se puede deshacer.
         </div>`,
        'Eliminar',
        'btn-danger',
        function() {
            // Función de confirmación
            $.ajax({
                url: `${base_path}/mant/rubrosdeduccionsalario/eliminar`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id: id
                }
            }).done(function (response) {
                if (response['estado']) {
                    showSuccess(response['mensaje']);
                    cargarRubros();
                } else {
                    showError(response['mensaje']);
                }
            }).fail(function () {
                showError("Error al eliminar el rubro");
            });
        }
    );
}

function mostrarModalConfirmacion(titulo, mensaje, textoBoton, claseBoton, callback) {
    let modalHtml = `
        <div class="modal fade" id="modal_confirmacion" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i> ${titulo}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${mensaje}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn ${claseBoton}" onclick="confirmarAccion()">
                            <i class="fas fa-check"></i> ${textoBoton}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior si existe
    $('#modal_confirmacion').remove();
    
    // Agregar modal al body
    $('body').append(modalHtml);
    
    // Mostrar modal
    $('#modal_confirmacion').modal('show');
    
    // Definir función global para confirmar
    window.confirmarAccion = function() {
        $('#modal_confirmacion').modal('hide');
        callback();
    };
    
    // Limpiar función global cuando se cierre el modal
    $('#modal_confirmacion').on('hidden.bs.modal', function() {
        $('#modal_confirmacion').remove();
        delete window.confirmarAccion;
    });
}

function actualizarPreview() {
    let porcentaje = parseFloat($('#rubro_porcentaje').val()) || 0.00;
    let salarioBase = 100000;
    let deduccion = salarioBase * (porcentaje / 100);
    let salarioNeto = salarioBase - deduccion;
    
    $('#preview_porcentaje').text(porcentaje.toFixed(2));
    $('#preview_deduccion').text(deduccion.toLocaleString('es-CR', {minimumFractionDigits: 2}));
    $('#preview_neto').text(salarioNeto.toLocaleString('es-CR', {minimumFractionDigits: 2}));
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
