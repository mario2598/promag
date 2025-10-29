@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .badge-pago {
            font-size: 0.9rem;
            padding: 0.4em 0.8em;
        }
        .card-proyecto {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        .card-proyecto:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .bg-soft-primary {
            background-color: #cfe2ff !important;
            color: #084298;
        }
        .bg-soft-success {
            background-color: #d4edda !important;
            color: #155724;
        }
        .bg-soft-info {
            background-color: #d1ecf1 !important;
            color: #0c5460;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.85rem;
            }
            .card-header h5 {
                font-size: 1rem;
            }
        }
        @media (max-width: 576px) {
            .table td, .table th {
                padding: 0.5rem 0.3rem;
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1><i class="fas fa-history"></i> Historial de Pagos por Proyecto</h1>
            </div>

            <div class="section-body">
                <!-- Tarjetas de Resumen -->
                <div class="row" id="resumen_cards">
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #007bff; opacity: 0.85;">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Pagos</h4>
                                </div>
                                <div class="card-body" id="total_pagos">
                                    0
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #28a745; opacity: 0.85;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Monto Total (CRC)</h4>
                                </div>
                                <div class="card-body" id="monto_total">
                                    ₡0.00
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #17a2b8; opacity: 0.85;">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Proyectos con Pagos</h4>
                                </div>
                                <div class="card-body" id="proyectos_con_pagos">
                                    0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listado de Proyectos -->
                <div class="card">
                    <div class="card-header bg-soft-primary">
                        <h4 class="mb-0"><i class="fas fa-project-diagram"></i> Proyectos con Historial de Pagos</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabla_proyectos">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-project-diagram"></i> Proyecto</th>
                                        <th class="text-center"><i class="fas fa-file-invoice-dollar"></i> Pagos Realizados</th>
                                        <th class="text-right"><i class="fas fa-dollar-sign"></i> Total Pagado (CRC)</th>
                                        <th class="text-center" style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_proyectos">
                                    <!-- Datos cargados dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- Modal Pagos del Proyecto -->
    <div class="modal fade" id="modal_pagos_proyecto" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-soft-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-project-diagram"></i> 
                        <span id="nombre_proyecto_modal"></span> - Historial de Pagos
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Resumen del Proyecto -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card bg-soft-info">
                                <div class="card-body text-center py-2">
                                    <h6 class="mb-1"><i class="fas fa-file-invoice-dollar"></i> Total Pagos</h6>
                                    <h4 class="mb-0" id="modal_total_pagos">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-soft-success">
                                <div class="card-body text-center py-2">
                                    <h6 class="mb-1"><i class="fas fa-dollar-sign"></i> Monto Total</h6>
                                    <h4 class="mb-0" id="modal_monto_total">₡0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-soft-warning">
                                <div class="card-body text-center py-2">
                                    <h6 class="mb-1"><i class="fas fa-users"></i> Beneficiarios</h6>
                                    <h4 class="mb-0" id="modal_beneficiarios">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Pagos -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="tabla_pagos_proyecto">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Fecha Pago</th>
                                    <th>Beneficiario</th>
                                    <th>N° Comprobante</th>
                                    <th class="text-center">CxPs</th>
                                    <th class="text-right">Monto (CRC)</th>
                                    <th class="text-center" style="width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_pagos_proyecto">
                                <!-- Datos cargados dinámicamente -->
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="5" class="text-right"><strong>TOTAL:</strong></th>
                                    <th class="text-right"><strong id="modal_footer_total">₡0.00</strong></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Desglose por Líneas de Presupuesto -->
                    <hr class="my-4">
                    
                    <div class="card">
                        <div class="card-header bg-soft-info">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Consumo por Líneas de Presupuesto</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm" id="tabla_lineas_presupuesto">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th style="width: 80px;"># Línea</th>
                                            <th>Descripción</th>
                                            <th class="text-right">Monto Autorizado</th>
                                            <th class="text-right bg-success text-white">Consumido (Aprobadas)</th>
                                            <th class="text-right bg-warning">Pendiente</th>
                                            <th class="text-right">Disponible</th>
                                            <th class="text-center" style="width: 100px;">% Consumido</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_lineas_presupuesto">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

    <!-- Modal Detalle de Pago Individual -->
    <div class="modal fade" id="modal_detalle_pago" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-soft-info">
                    <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Detalle del Pago</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detalle_pago_content">
                    <!-- Contenido cargado dinámicamente -->
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
    <script src="{{ asset('assets/bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/js/cxp/historial_pagos_proyectos.js') }}"></script>
@endsection

