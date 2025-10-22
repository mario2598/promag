@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Proyectos Asignados</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Proyectos en los que estoy involucrado</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Mis Proyectos Asignados</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="tabla_proyectos_asignados">
                                        <thead>
                                            <tr>
                                                <th class="text-center">ID</th>
                                                <th class="text-center">Cliente</th>
                                                <th class="text-center">Nombre Proyecto</th>
                                                <th class="text-center">Encargado</th>
                                                <th class="text-center">Mi Rol</th>
                                                <th class="text-center">Equipo</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('layout.configbar')
@endsection

@section('popup')
    <!-- Modal Ver Detalles del Proyecto -->
    <div class="modal fade" id="modal_detalle_proyecto" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_detalle_modal">Detalles del Proyecto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <span id="detalle_cliente"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Encargado:</strong> <span id="detalle_encargado"></span></p>
                        </div>
                        <div class="col-md-12">
                            <p><strong>Nombre:</strong> <span id="detalle_nombre"></span></p>
                        </div>
                        <div class="col-md-12">
                            <p><strong>Ubicación:</strong> <span id="detalle_ubicacion"></span></p>
                        </div>
                        <div class="col-md-12">
                            <p><strong>Descripción:</strong></p>
                            <p id="detalle_descripcion" class="text-muted"></p>
                        </div>
                        <div class="col-md-12">
                            <p><strong>Estado:</strong> <span id="detalle_estado"></span></p>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <h6><strong>Equipo del Proyecto:</strong></h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Precio/Hora</th>
                                            <th id="th_acciones_equipo">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalle_equipo" class="text-center">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestionar Bitácora de Usuario -->
    <div class="modal fade" id="modal_bitacora_usuario" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_bitacora_modal">
                        <i class="fas fa-clipboard-list"></i> Bitácora de <span id="nombre_usuario_bitacora"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bitacora_proyecto_id">
                    <input type="hidden" id="bitacora_usuario_id">
                    
                    <!-- Botón para agregar nueva entrada -->
                    <button type="button" class="btn btn-primary mb-3" onclick="abrirFormularioBitacora()">
                        <i class="fas fa-plus"></i> Agregar Registro
                    </button>

                    <!-- Formulario para agregar/editar bitácora -->
                    <div id="formulario_bitacora" style="display: none;" class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>* Fecha</label>
                                        <input type="date" class="form-control" id="bitacora_fecha" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>* Hora Entrada</label>
                                        <input type="time" class="form-control" id="bitacora_hora_entrada" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>* Hora Salida</label>
                                        <input type="time" class="form-control" id="bitacora_hora_salida" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Horas Trabajadas</label>
                                        <input type="text" class="form-control" id="bitacora_horas_trabajadas" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>* Tipo de Rubro</label>
                                        <select class="form-control" id="bitacora_rubro" required>
                                            <option value="">Seleccione un rubro...</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            El multiplicador afectará el cálculo del costo
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Línea de Presupuesto</label>
                                        <select class="form-control" id="bitacora_linea_presupuesto">
                                            <option value="">Sin asignar</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            Opcional: Asignar a una línea de presupuesto
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Costo Calculado</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₡</span>
                                            </div>
                                            <input type="text" class="form-control" id="bitacora_costo_preview" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="bitacora_multiplicador_preview">×1.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>* Descripción de Actividades</label>
                                        <textarea class="form-control" id="bitacora_descripcion" rows="4" required placeholder="Describa las actividades realizadas durante este día..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success" onclick="guardarBitacora()">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelarFormularioBitacora()">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de bitácoras -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                             <thead>
                                <tr class="text-center">
                                    <th>Fecha</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Horas</th>
                                    <th>Rubro</th>
                                    <th>Línea</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Costo (₡)</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_bitacoras_usuario">
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen total -->
                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Horas Trabajadas:</strong>
                                    <h4 id="total_horas" class="text-primary">0.00 hrs</h4>
                                </div>
                                <div class="col-md-4">
                                    <strong>Precio por Hora:</strong>
                                    <h4 id="precio_hora_usuario" class="text-info">₡0.00</h4>
                                </div>
                                <div class="col-md-4">
                                    <strong>Costo Total:</strong>
                                    <h4 id="costo_total_usuario" class="text-success">₡0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Observación de Rechazo -->
    <div class="modal fade" id="modal_observacion_rechazo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Motivo del Rechazo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <div id="contenido_observacion_rechazo" style="white-space: pre-wrap; font-size: 1rem;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/proyectos/proyectos_asignados.js') }}"></script>
@endsection

