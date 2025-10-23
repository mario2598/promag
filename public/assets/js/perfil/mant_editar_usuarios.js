var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var usuarioGestion = {
    "id": 0,
    "nombre": "",
    "ape1": "",
    "ape2": "",
    "cedula": "",
    "telefono": "",
    "fecha_nacimiento": "",
    "usuario": "",
    "correo": "",
    "nombre_beneficiario": "",
    "numero_cuenta": "",
    "nombre_banco": ""
};

var usuarioGuarda = {
    "id": 0,
    "nombre": "",
    "ape1": "",
    "ape2": "",
    "telefono": "",
    "fecha_nacimiento": "",
    "cedula": "",
    "correo": "",
    "usuario": "",
    "nombre_beneficiario": "",
    "numero_cuenta": "",
    "nombre_banco": ""
};

$(document).ready(function () {
    cargarUsuario();
});

function cargarUsuario() {
    if (idUsuario > 0) {

        $.ajax({
            url: `${base_path}/mant/usuarios/cargarUsuario`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                idUsuario: idUsuario
            }
        }).done(function (response) {
            if (!response['estado']) {
                cargarUsuarioNuevo();
                showError(response['mensaje']);
                return;
            }
            cargarUsuarioGestion(response['datos']);
            cargarHtmlUsuario();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            showError("Algo salió mal");
        });

    } else {
        cargarUsuarioNuevo();
    }

}

function cargarUsuarioGestion(usuarioAux) {
    idUsuario = usuarioAux.id;
    usuarioGestion = {
        "id": usuarioAux.id,
        "nombre": usuarioAux.nombre,
        "ape1": usuarioAux.ape1,
        "ape2": usuarioAux.ape2,
        "cedula": usuarioAux.cedula,
        "telefono": usuarioAux.telefono,
        "fecha_nacimiento": usuarioAux.fecha_nacimiento,
        "usuario": usuarioAux.usuario,
        "correo": usuarioAux.correo,
        "nombre_beneficiario": usuarioAux.nombre_beneficiario ?? "",
        "numero_cuenta": usuarioAux.numero_cuenta ?? "",
        "nombre_banco": usuarioAux.nombre_banco ?? ""
    };

}

function cargarHtmlUsuario() {

    $('#nombre').val(usuarioGestion.nombre != null ? usuarioGestion.nombre : "");
    $('#ape1').val(usuarioGestion.ape1 != null ? usuarioGestion.ape1 : "");
    $('#ape2').val(usuarioGestion.ape2 != null ? usuarioGestion.ape2 : "");
    $('#cedula').val(usuarioGestion.cedula != null ? usuarioGestion.cedula : "");
    $('#telefono').val(usuarioGestion.telefono != null ? usuarioGestion.telefono : "");
    $('#nacimiento').val(usuarioGestion.fecha_nacimiento != null ? usuarioGestion.fecha_nacimiento : "");
    $('#usuario').val(usuarioGestion.usuario != null ? usuarioGestion.usuario : "");
    $('#contra').val("***");
    $('#correo').val(usuarioGestion.correo != null ? usuarioGestion.correo : "");
    $('#sucursal').val(usuarioGestion.sucursal != null ? usuarioGestion.sucursal : "");
    $('#nombre_beneficiario').val(usuarioGestion.nombre_beneficiario != null ? usuarioGestion.nombre_beneficiario : "");
    $('#numero_cuenta').val(usuarioGestion.numero_cuenta != null ? usuarioGestion.numero_cuenta : "");
    $('#nombre_banco').val(usuarioGestion.nombre_banco != null ? usuarioGestion.nombre_banco : "");
    $('#contra').prop('readonly', usuarioGestion.id != null);
    if(usuarioGestion.id != 0){
      $('#lblCambiarPss').fadeIn();
    }else{
      $('#lblCambiarPss').fadeOut();
    }
}

function cargarUsuarioHtml() {
 
    usuarioGuarda.id = idUsuario;
    usuarioGuarda.nombre = $('#nombre').val();
    usuarioGuarda.ape1 = $('#ape1').val();
    usuarioGuarda.ape2 = $('#ape2').val();
    usuarioGuarda.telefono = $('#telefono').val();
    usuarioGuarda.fecha_nacimiento = $('#nacimiento').val();
    usuarioGuarda.correo = $('#correo').val();
    usuarioGuarda.cedula = $('#cedula').val();
    usuarioGuarda.usuario = $('#usuario').val();
    usuarioGuarda.nombre_beneficiario = $('#nombre_beneficiario').val();
    usuarioGuarda.numero_cuenta = $('#numero_cuenta').val();
    usuarioGuarda.nombre_banco = $('#nombre_banco').val();
}

function initialice() {

    var t = document.getElementById('mdl_generico_ipt_nombre');
    t.addEventListener('input', function () { // 
        if (this.value.length > 50)
            this.value = this.value.slice(0, 50);
    });

    t = document.getElementById('mdl_generico_ipt_correo');
    t.addEventListener('input', function () { // 
        if (this.value.length > 100)
            this.value = this.value.slice(0, 100);
    });

    t = document.getElementById('mdl_generico_ipt_ubicacion');
    t.addEventListener('input', function () { // 
        if (this.value.length > 300)
            this.value = this.value.slice(0, 300);
    });

    t = document.getElementById('mdl_generico_ipt_tel');
    t.addEventListener('input', function () { // 
        if (this.value.length > 12)
            this.value = this.value.slice(0, 12);
    });
}

function guardarUsuario() {
    cargarUsuarioHtml();

    $.ajax({
        url: `${base_path}/perfil/usuario/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            usuario: usuarioGuarda
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess(response['mensaje']);
        idUsuario = response['datos'];
        cargarUsuario();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function cambiarContra() {
    var contraNew = $("#nueva_contra").val();

    $.ajax({
        url: `${base_path}/perfil/usuario/seg`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idUsuarioEditar: idUsuario,
            nueva_contra: contraNew
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess(response['mensaje']);
        cerrarModalCambioPss();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function cerrarModalCambioPss(){
  $("#modal_cambio_contra").modal("hide");
}