var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tablaRubros;

$(document).ready(function () {
    cargarRubros();

    // Actualizar preview del cálculo al cambiar el multiplicador
    $('#rubro_multiplicador').on('input', function() {
        actualizarPreview();
    });
});

function cargarRubros() {
    $.ajax({
        url: `${base_path}/mant/rubrosextrasalario/cargar`,
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
        let precioBase = 10000;
        let precioConMultiplicador = precioBase * parseFloat(rubro.multiplicador);
        
        let fila = `
            <tr class="text-center">
                <td>${rubro.id}</td>
                <td class="text-left"><strong>${rubro.nombre}</strong></td>
                <td class="text-left">${rubro.descripcion || '-'}</td>
                <td>
                    <span class="badge badge-primary badge-lg">
                        <i class="fas fa-times"></i> ${parseFloat(rubro.multiplicador).toFixed(2)}
                    </span>
                </td>
                <td>
                    <span class="text-muted">₡10,000 × ${parseFloat(rubro.multiplicador).toFixed(2)} =</span>
                    <strong class="text-success">₡${precioConMultiplicador.toLocaleString('es-CR', {minimumFractionDigits: 2})}</strong>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editarRubro(${rubro.id}, '${escapeHtml(rubro.nombre)}', '${escapeHtml(rubro.descripcion || '')}', ${rubro.multiplicador})" title="Editar">
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
    $('#rubro_multiplicador').val('1.00');
    $('#titulo_modal_rubro').html('<i class="fas fa-money-bill-wave"></i> Nuevo Rubro Extra Salarial');
    actualizarPreview();
    $('#modal_rubro').modal('show');
}

function editarRubro(id, nombre, descripcion, multiplicador) {
    $('#rubro_id').val(id);
    $('#rubro_nombre').val(nombre);
    $('#rubro_descripcion').val(descripcion);
    $('#rubro_multiplicador').val(multiplicador);
    $('#titulo_modal_rubro').html('<i class="fas fa-edit"></i> Editar Rubro Extra Salarial');
    actualizarPreview();
    $('#modal_rubro').modal('show');
}

function guardarRubro() {
    let id = $('#rubro_id').val();
    let nombre = $('#rubro_nombre').val().trim();
    let descripcion = $('#rubro_descripcion').val().trim();
    let multiplicador = $('#rubro_multiplicador').val();

    // Validaciones
    if (!nombre) {
        showError("El nombre es requerido");
        $('#rubro_nombre').focus();
        return;
    }

    if (!multiplicador || parseFloat(multiplicador) <= 0) {
        showError("El multiplicador debe ser mayor a 0");
        $('#rubro_multiplicador').focus();
        return;
    }

    $.ajax({
        url: `${base_path}/mant/rubrosextrasalario/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: id,
            nombre: nombre,
            descripcion: descripcion,
            multiplicador: multiplicador
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
    if (!confirm(`¿Está seguro de eliminar el rubro "${nombre}"?`)) {
        return;
    }

    $.ajax({
        url: `${base_path}/mant/rubrosextrasalario/eliminar`,
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

function actualizarPreview() {
    let multiplicador = parseFloat($('#rubro_multiplicador').val()) || 1.00;
    let precioBase = 10000;
    let total = precioBase * multiplicador;
    
    $('#preview_multiplicador').text(multiplicador.toFixed(2));
    $('#preview_total').text(total.toLocaleString('es-CR', {minimumFractionDigits: 2}));
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

