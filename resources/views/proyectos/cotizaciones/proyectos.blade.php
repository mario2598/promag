@extends('layout.master')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
<link rel="stylesheet"
    href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
@include('layout.sidebar')

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Gestión de Proyectos</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Proyectos</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary" onclick="nuevoProyecto()">
                                    <i class="fas fa-plus"></i> Nuevo Proyecto
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabla_proyectos">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Nombre Proyecto</th>
                                            <th>Encargado</th>
                                            <th>Ubicación</th>
                                            <th>Estado</th>
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
<!-- Modal Proyecto -->
<div class="modal fade" id="modal_proyecto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo_modal">Nuevo Proyecto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="proyecto_id" value="0">

                <div class="row">
                    <!-- Cliente -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>* Cliente</label>
                            <select class="form-control select2" id="proyecto_cliente" required>
                                <option value="">Seleccione un cliente</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Nombre -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>* Nombre del Proyecto</label>
                            <input type="text" class="form-control" id="proyecto_nombre" maxlength="200" required>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Ubicación</label>
                            <input type="text" class="form-control" id="proyecto_ubicacion" maxlength="500">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea class="form-control" id="proyecto_descripcion" rows="3"></textarea>
                        </div>
                    </div>


                    <!-- Líneas de Presupuesto -->
                    <div class="col-md-12" id="seccion_lineas_presupuesto" style="display:none;">
                        <hr>
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Líneas de Presupuesto</label>
                            <button type="button" class="btn btn-sm btn-primary float-right"
                                onclick="agregarLineaPresupuesto()">
                                <i class="fas fa-plus"></i> Agregar Línea
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th width="80px">#</th>
                                        <th>Descripción</th>
                                        <th width="150px">Monto Autorizado</th>
                                        <th width="150px">Monto Consumido</th>
                                        <th width="150px">Disponible</th>
                                        <th width="100px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_lineas_presupuesto">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i>No hay líneas de presupuesto</i>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr class="font-weight-bold">
                                        <td colspan="2" class="text-right">TOTAL:</td>
                                        <td class="text-center" id="total_autorizado">₡0.00</td>
                                        <td class="text-center" id="total_consumido">₡0.00</td>
                                        <td class="text-center" id="total_disponible">₡0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarProyecto()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Línea de Presupuesto -->
<div class="modal fade" id="modal_linea_presupuesto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="titulo_modal_linea">
                    <i class="fas fa-dollar-sign"></i> Nueva Línea de Presupuesto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="linea_id" value="0">
                <input type="hidden" id="linea_monto_consumido" value="0">

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Número de Línea</label>
                            <input type="text" class="form-control" id="linea_numero" readonly>
                            <small class="form-text text-muted">
                                Se asigna automáticamente según el orden en el proyecto
                            </small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>* Descripción</label>
                            <textarea class="form-control" id="linea_descripcion" rows="3" maxlength="500"
                                required></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>* Monto Autorizado (₡)</label>
                            <input type="number" class="form-control" id="linea_monto_autorizado" min="0" step="0.01"
                                required>
                        </div>
                    </div>
                    <div class="col-md-12" id="info_monto_consumido" style="display:none;">
                        <div class="alert alert-info">
                            <strong>Monto Consumido:</strong> ₡<span id="display_monto_consumido">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarLineaPresupuesto()">
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
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/proyectos/cotizaciones/proyectos.js') }}"></script>
@endsection