@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
    @include('layout.sidebar', ['menus' => $data['menus']])

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Rubros Extra Salariales</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Gestión de multiplicadores de pago</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Rubros Extra Salariales</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-primary" onclick="nuevoRubro()">
                                        <i class="fas fa-plus"></i> Nuevo Rubro
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tabla_rubros">
                                        <thead>
                                            <tr class="text-center">
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Multiplicador</th>
                                                <th>Ejemplo (₡10,000/hr)</th>
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

    @include('layout.configbar', ['panel_configuraciones' => $data['panel_configuraciones']])
@endsection

@section('popup')
    <!-- Modal Rubro -->
    <div class="modal fade" id="modal_rubro" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="titulo_modal_rubro">
                        <i class="fas fa-money-bill-wave"></i> Nuevo Rubro Extra Salarial
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rubro_id" value="-1">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>* Nombre del Rubro</label>
                                <input type="text" class="form-control" id="rubro_nombre" placeholder="Ej: Hora Extra, Día Feriado, etc." required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea class="form-control" id="rubro_descripcion" rows="3" placeholder="Descripción del rubro (opcional)"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>* Multiplicador</label>
                                <input type="number" class="form-control" id="rubro_multiplicador" step="0.01" min="0.01" placeholder="Ej: 1.5" required>
                                <small class="form-text text-muted">
                                    Factor por el cual se multiplicará el precio/hora
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ejemplo de Cálculo</label>
                                <div class="alert alert-info mb-0">
                                    <p class="mb-0">
                                        <strong>Precio Base:</strong> ₡10,000/hr<br>
                                        <strong>Con multiplicador <span id="preview_multiplicador">1.00</span>:</strong><br>
                                        <strong class="text-primary">₡<span id="preview_total">10,000</span>/hr</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> <strong>Ejemplos comunes:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>1.00</strong> = Pago normal (sin cambios)</li>
                            <li><strong>1.25</strong> = 25% adicional (turno nocturno)</li>
                            <li><strong>1.50</strong> = 50% adicional (hora extra)</li>
                            <li><strong>2.00</strong> = Pago doble (día feriado)</li>
                            <li><strong>2.50</strong> = 150% adicional (hora extra en feriado)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarRubro()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/mantenimiento/mant_rubros_extra_salario.js') }}"></script>
@endsection

