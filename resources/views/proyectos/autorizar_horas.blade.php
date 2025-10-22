@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .badge-xl {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }
        .card-stats {
            border-left: 4px solid;
        }
        .card-stats.pendiente {
            border-left-color: #ffc107;
        }
        .card-stats.aprobada {
            border-left-color: #28a745;
        }
        .card-stats.rechazada {
            border-left-color: #dc3545;
        }
    </style>
@endsection

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Autorización de Horas</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Gestión de bitácoras de proyectos</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Tarjetas de resumen -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 card-stats pendiente">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Pendientes</h4>
                                </div>
                                <div class="card-body">
                                    <span id="total_pendientes" class="h3">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 card-stats aprobada">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Aprobadas</h4>
                                </div>
                                <div class="card-body">
                                    <span id="total_aprobadas" class="h3">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 card-stats rechazada">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Rechazadas</h4>
                                </div>
                                <div class="card-body">
                                    <span id="total_rechazadas" class="h3">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total</h4>
                                </div>
                                <div class="card-body">
                                    <span id="total_general" class="h3">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listado de Usuarios con Bitácoras Pendientes -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Usuarios con Bitácoras Pendientes de Autorización</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tabla_usuarios_pendientes">
                                        <thead>
                                            <tr class="text-center">
                                                <th>Usuario</th>
                                                <th>Proyectos</th>
                                                <th>Bitácoras Pendientes</th>
                                                <th>Total Horas</th>
                                                <th>Monto Estimado</th>
                                                <th>Acciones</th>
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

                <!-- Listado de Todas las Bitácoras con Filtros -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Historial de Bitácoras</h4>
                                <div class="card-header-action">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary active" onclick="filtrarBitacoras('TODAS')">
                                            <i class="fas fa-list"></i> Todas
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="filtrarBitacoras('PENDIENTE')">
                                            <i class="fas fa-clock"></i> Pendientes
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="filtrarBitacoras('APROBADA')">
                                            <i class="fas fa-check"></i> Aprobadas
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="filtrarBitacoras('RECHAZADA')">
                                            <i class="fas fa-times"></i> Rechazadas
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm" id="tabla_historial_bitacoras">
                                        <thead>
                                            <tr class="text-center">
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Proyecto</th>
                                                <th>Cliente</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Horas</th>
                                                <th>Rubro</th>
                                                <th>Línea</th>
                                                <th>Estado</th>
                                                <th>Costo (₡)</th>
                                                <th>Autorizado Por</th>
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
    <!-- Modal Desglose de Bitácoras del Usuario -->
    <div class="modal fade" id="modal_desglose_bitacoras" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-check"></i> Autorizar Bitácoras de <span id="desglose_usuario_nombre"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="desglose_usuario_id">
                    <input type="hidden" id="rechazo_bitacora_id">
                    <input type="hidden" id="rechazo_es_multiple" value="0">

                    <!-- Panel de Observación de Rechazo (Oculto inicialmente) -->
                    <div id="panel_observacion_rechazo" class="card border-danger mb-3" style="display: none;">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-times-circle"></i> Motivo de Rechazo</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label><strong>Observación del Rechazo</strong> <span class="text-muted">(Opcional)</span></label>
                                <textarea id="rechazo_observacion" class="form-control" rows="3" placeholder="Indique el motivo del rechazo..."></textarea>
                                <small class="form-text text-muted">
                                    Esta observación será visible para el usuario que registró la bitácora
                                </small>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-secondary" onclick="cerrarPanelRechazo()">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-danger" onclick="confirmarRechazo()">
                                    <i class="fas fa-check"></i> Confirmar Rechazo
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones masivas -->
                    <div class="mb-3" id="botones_acciones_masivas">
                        <button type="button" class="btn btn-success btn-lg" onclick="aprobarTodasBitacoras()">
                            <i class="fas fa-check-double"></i> Aprobar Todas
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" onclick="rechazarTodasBitacoras()">
                            <i class="fas fa-times-circle"></i> Rechazar Todas
                        </button>
                        <span class="ml-3 text-muted">
                            <i class="fas fa-info-circle"></i> O puede aprobar/rechazar individualmente
                        </span>
                    </div>

                    <!-- Contenedor de bitácoras agrupadas por proyecto -->
                    <div id="contenedor_bitacoras_proyectos">
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
    <script src="{{ asset('assets/js/proyectos/autorizar_horas.js') }}"></script>
@endsection

