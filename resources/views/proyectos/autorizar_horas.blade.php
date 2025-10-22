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

                <!-- Filtros y tabla -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Bitácoras</h4>
                                <div class="card-header-action">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-warning" onclick="filtrarBitacoras('PENDIENTE')">
                                            <i class="fas fa-clock"></i> Pendientes
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="filtrarBitacoras('APROBADAS')">
                                            <i class="fas fa-check"></i> Aprobadas
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="filtrarBitacoras('RECHAZADAS')">
                                            <i class="fas fa-times"></i> Rechazadas
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="filtrarBitacoras('TODAS')">
                                            <i class="fas fa-list"></i> Todas
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tabla_bitacoras">
                                        <thead>
                                            <tr class="text-center">
                                                <th>Fecha</th>
                                                <th>Proyecto</th>
                                                <th>Cliente</th>
                                                <th>Usuario</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Horas</th>
                                                <th>Rubro</th>
                                                <th>Línea</th>
                                                <th>Estado</th>
                                                <th>Autorizado Por</th>
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
            </div>
        </section>
    </div>

    @include('layout.configbar')
@endsection

@section('popup')
    <!-- Modal Ver Descripción -->
    <div class="modal fade" id="modal_descripcion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt"></i> Descripción de Actividades
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Proyecto:</strong> <span id="desc_proyecto"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Usuario:</strong> <span id="desc_usuario"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha:</strong> <span id="desc_fecha"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Horario:</strong> <span id="desc_horario"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rubro:</strong> <span id="desc_rubro"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Línea de Presupuesto:</strong> <span id="desc_linea"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h6><strong>Descripción de Actividades:</strong></h6>
                    <p id="desc_contenido" class="text-justify" style="white-space: pre-wrap;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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

