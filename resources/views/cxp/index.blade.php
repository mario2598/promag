@extends('layout.master')

@section('content')
@include('layout.sidebar')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Cuentas por Pagar</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">Cuentas por Pagar</div>
            </div>
        </div>

        <div class="section-body">
            <!-- Tarjetas de Resumen -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Usuarios</h4>
                            </div>
                            <div class="card-body" id="total_usuarios">
                                0
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total CxP</h4>
                            </div>
                            <div class="card-body" id="total_cxp">
                                0
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Monto Total</h4>
                            </div>
                            <div class="card-body" id="monto_total">
                                ₡0.00
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Usuarios con CxP -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-users"></i> Usuarios con Cuentas por Pagar Pendientes</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary" onclick="cargarUsuariosConCxP()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tabla_usuarios">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Usuario</th>
                                            <th>Beneficiario</th>
                                            <th>Número de Cuenta</th>
                                            <th>CxP Pendientes</th>
                                            <th>Monto Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_usuarios">
                                        <!-- Se llenará dinámicamente -->
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

<!-- Modal Principal - Gestión de CxP del Usuario -->
<div class="modal fade" id="modal_gestionar_cxp" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice-dollar"></i> Gestión de Cuentas por Pagar - <span id="nombre_usuario_modal"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Información del Beneficiario -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-user"></i> Beneficiario:</strong><br>
                                <span id="beneficiario_modal"></span>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-credit-card"></i> Número de Cuenta:</strong><br>
                                <span id="numero_cuenta_modal"></span>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-dollar-sign"></i> Monto Total Pendiente:</strong><br>
                                <h4 class="text-success mb-0" id="monto_total_modal"></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción Superior -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-success btn-block" id="btn_aprobar_todas" onclick="aprobarYPagarTodasCxP()">
                            <i class="fas fa-check-circle"></i> Aprobar y Pagar Seleccionadas (<span id="cant_seleccionadas">0</span>)
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-danger btn-block" id="btn_rechazar_todas" onclick="rechazarTodasCxP()">
                            <i class="fas fa-times-circle"></i> Rechazar Seleccionadas (<span id="cant_seleccionadas_rechazar">0</span>)
                        </button>
                    </div>
                </div>

                <!-- Tabla de CxP -->
                <div id="contenedor_cxp_modal">
                    <!-- Se llenará dinámicamente -->
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

<!-- Modal Aprobar y Pagar CxP -->
<div class="modal fade" id="modal_aprobar_cxp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Aprobar y Pagar Cuentas por Pagar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Información:</strong><br>
                    Se aprobarán y marcarán como pagadas <strong><span id="cant_cxp_aprobar">0</span> CxP(s)</strong> por un monto total de <strong><span id="monto_total_aprobar">₡0.00</span></strong>
                </div>
                
                <div class="form-group">
                    <label><strong>Tipo de Pago</strong> <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_pago_aprobacion" required>
                        <option value="">Seleccione un tipo de pago</option>
                        @foreach(\App\Http\Controllers\SisTipoController::getByCodGeneralGrupo('GEN_TIPOS_PAGOS') as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seleccione el método de pago utilizado.</small>
                </div>

                <div class="form-group">
                    <label><strong>Número de Comprobante</strong></label>
                    <input type="text" class="form-control" id="num_comprobante_aprobacion" placeholder="Ej: COMP-2025-001, Transferencia #123456">
                    <small class="text-muted">Número de comprobante, transferencia, SINPE, etc.</small>
                </div>

                <div class="form-group">
                    <label><strong>Observaciones</strong></label>
                    <textarea class="form-control" id="observaciones_aprobacion" rows="3" placeholder="Observaciones adicionales (opcional)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarAprobacion()">
                    <i class="fas fa-check"></i> Aprobar y Pagar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar CxP -->
<div class="modal fade" id="modal_rechazar_cxp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Rechazar Cuentas por Pagar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong><i class="fas fa-exclamation-triangle"></i> Atención:</strong><br>
                    Al rechazar las CxP, también se rechazarán las bitácoras asociadas y se desasociarán de las CxP.
                </div>
                <div class="form-group">
                    <label><strong>Motivo del rechazo</strong> <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="motivo_rechazo" rows="4" placeholder="Ingrese el motivo del rechazo..." required></textarea>
                    <small class="text-muted">Este motivo se guardará en las bitácoras rechazadas.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarRechazo()">
                    <i class="fas fa-ban"></i> Rechazar CxP
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/js/cxp/cxp.js') }}"></script>
@endsection
