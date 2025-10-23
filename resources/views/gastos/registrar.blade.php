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
                            <form action="{{ URL::to('gastos/guardar') }}" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="-1">
                                <div class="card-header">
                                    <h4>Ingresar gasto</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-xl-4">
                                            <div class="form-group">
                                                <label>Fecha Gasto </label>
                                                <input type="date" id="fecha" name="fecha" max='{{ date('Y-m-d') }}'
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Proveedor</label>
                                                <select class="form-control" name="proveedor">
                                                    <option value="" selected>Ninguno</option>
                                                    <?php $proveedoresAux = \App\Http\Controllers\MantenimientoProveedorController::getProvedoresActivos()?>
                                                    @foreach ($proveedoresAux as $i)
                                                        <option value="{{ $i->id ?? -1 }}"
                                                            title="{{ $i->descripcion ?? '' }}">{{ $i->nombre ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de gasto</label>
                                                <select class="form-control" name="tipo_pago">
                                                    <?php $tipos_pagoAux = \App\Http\Controllers\SisTipoController::getByCodGeneralGrupo("GEN_GASTOS") ?>
                                                    @foreach ($tipos_pagoAux as $i)
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->nombre ?? '' }}">
                                                            {{ $i->nombre ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Forma de Pago</label>
                                                <select class="form-control" name="tipo_pago">
                                                    <?php $tipos_pagoAux = \App\Http\Controllers\SisTipoController::getByCodGeneralGrupo("GEN_TIPOS_PAGOS") ?>
                                                    @foreach ($tipos_pagoAux as $i)
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->nombre ?? '' }}">
                                                            {{ $i->nombre ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Número comprobante</label>
                                                <input type="text" class="form-control" name="num_comprobante"
                                                    maxlength="50">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Moneda</label>
                                                <select class="form-control" name="codigo_moneda" id="codigo_moneda" onchange="actualizarTipoCambio()">
                                                    <?php $monedas = \App\Http\Controllers\MantenimientoMonedasController::getMonedasActivas() ?>
                                                    @foreach ($monedas as $moneda)
                                                        <option value="{{ $moneda->codigo }}" 
                                                                data-tipo-cambio="{{ $moneda->tipo_cambio }}"
                                                                {{ $moneda->codigo == 'CRC' ? 'selected' : '' }}>
                                                            {{ $moneda->codigo }} - {{ $moneda->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de Cambio</label>
                                                <input type="number" step="0.0001" class="form-control" 
                                                       name="tipo_cambio" id="tipo_cambio" value="1.0000" 
                                                       min="0.0001" required readonly>
                                                <small class="form-text text-muted">Tipo de cambio al momento de la transacción</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input type="number" step="any" class="form-control" placeholder="0.00"
                                                    name="total" id="total" min="0.01" required>
                                                <small class="form-text text-muted" id="total_label">Total en CRC</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Descripción del gasto</label>
                                                <textarea class="form-control" required="" name="descripcion"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group ">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group ">
                                                <label>Foto comprobante</label>
                                                <input type="file" class="form-control" id="foto_comprobante"
                                                    name="foto_comprobante" accept="image/png, image/jpeg, image/jpg"
                                                    onchange="fileValidation()">
                                                <input type="text" id="foto_comprobante_b64" style='display:none;'
                                                    name="foto_comprobante_b64">

                                            </div>
                                        </div>


                                    </div>
                                </div>


                                <div class="card-footer text-right">
                                    <input type="submit" class="btn btn-primary" value="Registrar" />
                                    
                                </div>
                                <div class="card-footer text-right">
                                    <button type="button"
                                        onclick="window.location='{{ URL::to('gastos/administracion') }}'"
                                        class="btn btn-warning">Volver a todos los gastos</button>
                                </div>
                               
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>


@section('script')

    <script src="{{ asset('assets/js/gastos/gasto.js') }}"></script>
    
    <script>
    function actualizarTipoCambio() {
        const selectMoneda = document.getElementById('codigo_moneda');
        const inputTipoCambio = document.getElementById('tipo_cambio');
        const labelTotal = document.getElementById('total_label');
        
        const opcionSeleccionada = selectMoneda.options[selectMoneda.selectedIndex];
        const tipoCambio = opcionSeleccionada.getAttribute('data-tipo-cambio');
        const codigoMoneda = opcionSeleccionada.value;
        
        inputTipoCambio.value = tipoCambio;
        labelTotal.textContent = `Total en ${codigoMoneda}`;
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        actualizarTipoCambio();
    });
    </script>

@endsection

@endsection
