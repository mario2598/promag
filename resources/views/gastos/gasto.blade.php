@extends('layout.master')

@section('content')
    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Información del gasto - {{ $data['gasto']->nombreUsuario }}</h4>
                                <div class="card-header-action">
                                    <span class="badge badge-info">Solo lectura</span>
                                </div>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->fecha }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Proveedor</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->proveedorNombre ?? 'Ninguno' }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de pago</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->tipo_pago_nombre ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de gasto</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->tipo_gasto_nombre ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Estado</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->estadoUsuario ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Número comprobante</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->num_factura ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input type="text" class="form-control" readonly
                                                    value="₡{{ number_format($data['gasto']->monto ?? 0, 2) }}">
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Moneda</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->codigo_moneda ?? 'CRC' }}">
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de Cambio</label>
                                                <input type="text" class="form-control" readonly
                                                       value="{{ number_format($data['gasto']->tipo_cambio ?? 1.0000, 4) }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Descripción del gasto</label>
                                                <textarea class="form-control" readonly>{{ $data['gasto']->descripcion ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Observación</label>
                                                <textarea class="form-control" readonly>{{ $data['gasto']->observacion ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        @if ($data['gasto']->url_factura != null)
                                            <div class="col-sm-12 col-md-6 col-xl-4">
                                                <div class="form-group mb-0">
                                                    <label>Foto comprobante</label> <br>
                                                    <button id="showImageBtn" type="button" class="btn btn-primary"
                                                        onclick="toggleImage()">Mostrar comprobante</button>
                                                    <img id="idFactImg" src="{{ $data['gasto']->url_factura }}"
                                                        style="max-width: 100%; height: auto; display: none;"
                                                        alt="Imagen">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Sección de CxP Relacionadas -->
                                @if(isset($data['cxps']) && count($data['cxps']) > 0)
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-link"></i> Cuentas por Pagar Relacionadas
                                        <span class="badge badge-light">{{ count($data['cxps']) }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Este gasto está asociado a <strong>{{ count($data['cxps']) }}</strong> cuenta(s) por pagar.
                                    </div>

                                    @foreach($data['cxps'] as $cxp)
                                    <div class="card border-primary mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <strong><i class="fas fa-file-invoice-dollar"></i> CxP: {{ $cxp->numero_cxp }}</strong>
                                            <span class="float-right">
                                                @if($cxp->estado_codigo == 'CXP_PAGADA')
                                                    <span class="badge badge-success">{{ $cxp->estado_nombre }}</span>
                                                @elseif($cxp->estado_codigo == 'CXP_CANCELADA')
                                                    <span class="badge badge-danger">{{ $cxp->estado_nombre }}</span>
                                                @elseif($cxp->estado_codigo == 'CXP_APROBADA')
                                                    <span class="badge badge-info">{{ $cxp->estado_nombre }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $cxp->estado_nombre }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong><i class="fas fa-user"></i> Beneficiario:</strong> {{ $cxp->beneficiario }}</p>
                                                    <p><strong><i class="fas fa-credit-card"></i> Cuenta:</strong> {{ $cxp->numero_cuenta ?? 'No especificado' }}</p>
                                                    <p><strong><i class="fas fa-tag"></i> Tipo:</strong> {{ $cxp->tipo_nombre }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong><i class="fas fa-calendar"></i> Fecha Creación:</strong> {{ date('d/m/Y', strtotime($cxp->fecha_creacion)) }}</p>
                                                    @if($cxp->fecha_vencimiento)
                                                    <p><strong><i class="fas fa-calendar-times"></i> Vencimiento:</strong> {{ date('d/m/Y', strtotime($cxp->fecha_vencimiento)) }}</p>
                                                    @endif
                                                    <p><strong><i class="fas fa-dollar-sign"></i> Monto Total:</strong> <span class="text-success"><strong>₡{{ number_format($cxp->monto_total, 2) }}</strong></span></p>
                                                </div>
                                            </div>

                                            @if($cxp->observaciones)
                                            <div class="mt-2">
                                                <strong><i class="fas fa-comment"></i> Observaciones:</strong>
                                                <p class="mb-0">{{ $cxp->observaciones }}</p>
                                            </div>
                                            @endif

                                            <!-- Deducciones de esta CxP -->
                                            @if(count($cxp->deducciones) > 0)
                                            <div class="mt-3">
                                                <strong><i class="fas fa-calculator"></i> Deducciones Aplicadas:</strong>
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Concepto</th>
                                                                <th class="text-right">Base</th>
                                                                <th class="text-center">%</th>
                                                                <th class="text-right">Deducción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $totalDeducciones = 0;
                                                                $montoBase = 0;
                                                            @endphp
                                                            @foreach($cxp->deducciones as $deduccion)
                                                                @php
                                                                    $totalDeducciones += $deduccion->monto_deduccion;
                                                                    $montoBase = $deduccion->monto_base;
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <i class="fas fa-arrow-right text-danger"></i> {{ $deduccion->rubro_nombre }}
                                                                    </td>
                                                                    <td class="text-right">₡{{ number_format($deduccion->monto_base, 2) }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-warning">{{ number_format($deduccion->porcentaje, 2) }}%</span>
                                                                    </td>
                                                                    <td class="text-right text-danger">
                                                                        - ₡{{ number_format($deduccion->monto_deduccion, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="thead-light">
                                                            <tr class="bg-light">
                                                                <td colspan="3" class="text-right"><strong><i class="fas fa-clock"></i> Monto Base (Antes de Deducciones):</strong></td>
                                                                <td class="text-right"><strong>₡{{ number_format($montoBase, 2) }}</strong></td>
                                                            </tr>
                                                            <tr class="table-info">
                                                                <td colspan="3" class="text-right"><strong><i class="fas fa-minus-circle"></i> Total Deducciones:</strong></td>
                                                                <td class="text-right text-danger"><strong>₡{{ number_format($totalDeducciones, 2) }}</strong></td>
                                                            </tr>
                                                            <tr class="table-success">
                                                                <td colspan="3" class="text-right"><strong><i class="fas fa-check-circle"></i> MONTO FINAL (CxP):</strong></td>
                                                                <td class="text-right text-success"><strong>₡{{ number_format($cxp->monto_total, 2) }}</strong></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                <div class="card-footer text-right">
                                    @if ($data['gasto']->codEstadoUsuario == 'EST_GASTO_APB')
                                        <button type="button" onclick="eliminarGastoAdmin('{{ $data['gasto']->id }}')"
                                            class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    @endif
                                    <button type="button"
                                        onclick="window.location='{{ URL::to('gastos/administracion') }}'"
                                        class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i> Volver a todos los gastos
                                    </button>
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
<script>
    function toggleImage() {
        var img = document.getElementById("idFactImg");
        var btn = document.getElementById("showImageBtn");

        // Si la imagen está oculta, mostrarla
        if (img.style.display === "none") {
            img.style.display = "block";
            btn.innerText = "Ocultar comprobante";
        } else {
            img.style.display = "none";
            btn.innerText = "Mostrar comprobante";
        }
    }
</script>
@endsection
