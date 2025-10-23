@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <style>
        /* Colores suaves y menos invasivos */
        .bg-soft-success { background-color: #d4edda !important; color: #155724; }
        .bg-soft-danger { background-color: #f8d7da !important; color: #721c24; }
        .bg-soft-info { background-color: #d1ecf1 !important; color: #0c5460; }
        .bg-soft-warning { background-color: #fff3cd !important; color: #856404; }
        .bg-soft-primary { background-color: #cfe2ff !important; color: #084298; }
        .bg-soft-secondary { background-color: #e2e3e5 !important; color: #41464b; }
        
        /* Cards responsive */
        @media (max-width: 768px) {
            .card-statistic-1 .card-body {
                font-size: 1.2rem;
            }
            .card-statistic-1 .card-header h4 {
                font-size: 0.9rem;
            }
            .table-responsive {
                font-size: 0.85rem;
            }
            .card-header h4 {
                font-size: 1rem;
            }
        }
        
        /* Mejorar legibilidad de badges */
        .badge {
            padding: 0.4em 0.8em;
            font-size: 0.9em;
        }
        
        /* Tablas más compactas en móvil */
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
                <h1><i class="fas fa-chart-line"></i> Resumen Contable</h1>
            </div>

            <div class="section-body">
                <!-- Filtros -->
                <div class="card">
                    <div class="card-header bg-soft-primary">
                        <h4><i class="fas fa-filter"></i> Filtros</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ URL::to('informes/resumencontable/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><i class="fas fa-building"></i> Sucursal</label>
                                        <select class="form-control" id="select_sucursal" name="sucursal">
                                            <option value="T" selected>Todas las sucursales</option>
                                            @foreach ($data['sucursales'] as $i)
                                                <option value="{{ $i->id ?? '' }}" 
                                                    @if ($i->id == $data['filtros']['sucursal']) selected @endif>
                                                    {{ $i->descripcion ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt"></i> Desde</label>
                                        <input type="date" class="form-control" name="desde"
                                            value="{{ $data['filtros']['desde'] ?? '' }}" />
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-check"></i> Hasta</label>
                                        <input type="date" class="form-control" name="hasta" id="hasta"
                                            value="{{ $data['filtros']['hasta'] ?? '' }}" />
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(isset($data['resumen']))
                <!-- Tarjetas de Resumen Principal -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #28a745; opacity: 0.85;">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Ingresos</h4>
                                </div>
                                <div class="card-body">
                                    ₡{{ number_format($data['resumen']['ingresos'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #dc3545; opacity: 0.85;">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Gastos</h4>
                                </div>
                                <div class="card-body">
                                    ₡{{ number_format($data['resumen']['gastosGeneral'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: #ffc107; opacity: 0.85;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Comisiones</h4>
                                </div>
                                <div class="card-body">
                                    ₡{{ number_format($data['resumen']['totalPagoTarjetaGeneral'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background-color: {{ ($data['resumen']['totalFondosGeneral'] ?? 0) >= 0 ? '#007bff' : '#dc3545' }}; opacity: 0.85;">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Fondos Disponibles</h4>
                                </div>
                                <div class="card-body">
                                    ₡{{ number_format($data['resumen']['totalFondosGeneral'] ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desglose de Ingresos -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-soft-success">
                                <h4 class="mb-0"><i class="fas fa-coins"></i> Desglose de Ingresos (en Colones - CRC)</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><i class="fas fa-tag"></i> Concepto</th>
                                                <th class="text-right"><i class="fas fa-dollar-sign"></i> Monto</th>
                                                <th class="text-right"><i class="fas fa-percentage"></i> Porcentaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalIngresos = $data['resumen']['ingresos'] ?? 1;
                                            @endphp
                                            <tr>
                                                <td><i class="fas fa-mobile-alt text-primary"></i> Ingresos SINPE</td>
                                                <td class="text-right">₡{{ number_format($data['resumen']['totalIngresosSinpeGeneral'] ?? 0, 2) }}</td>
                                                <td class="text-right">{{ number_format((($data['resumen']['totalIngresosSinpeGeneral'] ?? 0) / $totalIngresos) * 100, 2) }}%</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-money-bill-wave text-success"></i> Ingresos Efectivo</td>
                                                <td class="text-right">₡{{ number_format($data['resumen']['totalIngresosEfectivoGeneral'] ?? 0, 2) }}</td>
                                                <td class="text-right">{{ number_format((($data['resumen']['totalIngresosEfectivoGeneral'] ?? 0) / $totalIngresos) * 100, 2) }}%</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-credit-card text-info"></i> Ingresos Tarjeta (Bruto)</td>
                                                <td class="text-right">₡{{ number_format($data['resumen']['totalIngresosTarjetaGeneral'] ?? 0, 2) }}</td>
                                                <td class="text-right">{{ number_format((($data['resumen']['totalIngresosTarjetaGeneral'] ?? 0) / $totalIngresos) * 100, 2) }}%</td>
                                            </tr>
                                            <tr class="bg-soft-warning">
                                                <td><i class="fas fa-minus-circle text-danger"></i> Comisiones Bancarias (2.5%)</td>
                                                <td class="text-right text-danger">- ₡{{ number_format($data['resumen']['totalPagoTarjetaGeneral'] ?? 0, 2) }}</td>
                                                <td class="text-right">{{ number_format((($data['resumen']['totalPagoTarjetaGeneral'] ?? 0) / $totalIngresos) * 100, 2) }}%</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-soft-secondary">
                                            <tr class="font-weight-bold">
                                                <td><strong><i class="fas fa-calculator"></i> Subtotal Fondos</strong></td>
                                                <td class="text-right"><strong>₡{{ number_format($data['resumen']['subTotalFondosGeneral'] ?? 0, 2) }}</strong></td>
                                                <td class="text-right"><strong>100.00%</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desglose por Moneda - Ingresos -->
                @if(isset($data['resumen']['resumenMonedas']) && count($data['resumen']['resumenMonedas']) > 0)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-soft-info">
                                <h4 class="mb-0"><i class="fas fa-globe"></i> Desglose de Ingresos por Moneda</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaIngresosPorMoneda">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center"><i class="fas fa-coins"></i> Moneda</th>
                                                <th class="text-right"><i class="fas fa-money-bill-wave"></i> Efectivo</th>
                                                <th class="text-right"><i class="fas fa-credit-card"></i> Tarjeta</th>
                                                <th class="text-right"><i class="fas fa-mobile-alt"></i> SINPE</th>
                                                <th class="text-right bg-light"><i class="fas fa-calculator"></i> Total en Moneda</th>
                                                <th class="text-right bg-soft-warning"><i class="fas fa-exchange-alt"></i> Equivalente CRC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['resumen']['resumenMonedas'] as $moneda)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">{{ $moneda['codigo_moneda'] }}</span>
                                                </td>
                                                <td class="text-right">{{ $moneda['codigo_moneda'] }} {{ number_format($moneda['total_efectivo'], 2) }}</td>
                                                <td class="text-right">{{ $moneda['codigo_moneda'] }} {{ number_format($moneda['total_tarjeta'], 2) }}</td>
                                                <td class="text-right">{{ $moneda['codigo_moneda'] }} {{ number_format($moneda['total_sinpe'], 2) }}</td>
                                                <td class="text-right bg-light"><strong>{{ $moneda['codigo_moneda'] }} {{ number_format($moneda['total'], 2) }}</strong></td>
                                                <td class="text-right bg-soft-warning"><strong>₡{{ number_format($moneda['total_crc'], 2) }}</strong></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="font-weight-bold">
                                                <td colspan="5" class="text-right"><strong>TOTAL EN COLONES:</strong></td>
                                                <td class="text-right bg-soft-success"><strong>₡{{ number_format($data['resumen']['ingresos'] ?? 0, 2) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Desglose por Moneda - Gastos -->
                @if(isset($data['resumen']['resumenGastosPorMoneda']) && count($data['resumen']['resumenGastosPorMoneda']) > 0)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-soft-danger">
                                <h4 class="mb-0"><i class="fas fa-receipt"></i> Desglose de Gastos por Moneda</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaGastosPorMoneda">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center"><i class="fas fa-coins"></i> Moneda</th>
                                                <th class="text-right bg-light"><i class="fas fa-calculator"></i> Total en Moneda</th>
                                                <th class="text-right bg-soft-warning"><i class="fas fa-exchange-alt"></i> Equivalente CRC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['resumen']['resumenGastosPorMoneda'] as $moneda)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-danger">{{ $moneda['codigo_moneda'] }}</span>
                                                </td>
                                                <td class="text-right bg-light"><strong>{{ $moneda['codigo_moneda'] }} {{ number_format($moneda['total'], 2) }}</strong></td>
                                                <td class="text-right bg-soft-warning"><strong>₡{{ number_format($moneda['total_crc'], 2) }}</strong></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="font-weight-bold">
                                                <td colspan="2" class="text-right"><strong>TOTAL GASTOS EN COLONES:</strong></td>
                                                <td class="text-right bg-soft-danger"><strong>₡{{ number_format($data['resumen']['gastosGeneral'] ?? 0, 2) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Resumen Final -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-soft-primary">
                                <h4 class="mb-0"><i class="fas fa-chart-pie"></i> Resumen Final (Todo en Colones - CRC)</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tablaFondos">
                                        <tbody>
                                            <tr class="bg-light">
                                                <td colspan="2" class="text-center"><h5 class="mb-0"><strong>INGRESOS</strong></h5></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-plus-circle text-success"></i> Subtotal de Ingresos</td>
                                                <td class="text-right"><strong>₡{{ number_format($data['resumen']['subTotalFondos'] ?? 0, 2) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-minus-circle text-warning"></i> Comisiones Bancarias</td>
                                                <td class="text-right text-danger">- ₡{{ number_format($data['resumen']['totalPagoTarjetaGeneral'] ?? 0, 2) }}</td>
                                            </tr>
                                            <tr class="bg-soft-success">
                                                <td><strong><i class="fas fa-check-circle"></i> Total Ingresos Netos</strong></td>
                                                <td class="text-right"><strong>₡{{ number_format($data['resumen']['subTotalFondosGeneral'] ?? 0, 2) }}</strong></td>
                                            </tr>
                                            
                                            <tr class="bg-light">
                                                <td colspan="2" class="text-center"><h5 class="mb-0"><strong>GASTOS</strong></h5></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-minus-circle text-danger"></i> Total Gastos</td>
                                                <td class="text-right text-danger"><strong>- ₡{{ number_format($data['resumen']['gastosGeneral'] ?? 0, 2) }}</strong></td>
                                            </tr>
                                            
                                            <tr class="bg-light">
                                                <td colspan="2" class="text-center"><h5 class="mb-0"><strong>RESULTADO</strong></h5></td>
                                            </tr>
                                            <tr class="{{ ($data['resumen']['totalFondosGeneral'] ?? 0) >= 0 ? 'bg-soft-primary' : 'bg-soft-danger' }}">
                                                <td><strong><i class="fas fa-wallet"></i> TOTAL FONDOS DISPONIBLES</strong></td>
                                                <td class="text-right"><h4 class="mb-0"><strong>₡{{ number_format($data['resumen']['totalFondosGeneral'] ?? 0, 2) }}</strong></h4></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var sucursal = $("#select_sucursal option[value='" + "{{ $data['filtros']['sucursal'] }}" + "']").html();

            var topMesage = 'Resumen General Contable';
            var bottomMesage = 'Resumen general contable filtrado por';

            if ("{{ $data['filtros']['desde'] }}" != '') {
                topMesage += ' desde el ' + "{{ $data['filtros']['desde'] }}";
            }
            if ("{{ $data['filtros']['hasta'] }}" != '') {
                topMesage += ' hasta el ' + "{{ $data['filtros']['hasta'] }}";
            }
            var fechaActual = new Date();
            var fechaFormateada = fechaActual.toLocaleString();
            topMesage += '.' + ' Solicitud realizada el ' + fechaFormateada + ' por ' +
                "{{ session('usuario')['usuario'] }}" + '.';

            if ("{{ $data['filtros']['sucursal'] }}" != 'T') {
                bottomMesage += ' sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' sucursal [ Todas ].';
            }

            bottomMesage += ' {{ env('APP_NAME', 'SPACE SOFTWARE CR') }}. ';


            $('#tablaFondos').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                "info": false,
                buttons: [{
                    extend: 'excel',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    footer: true,
                    messageBottom: bottomMesage,
                    filename: 'resumen_contable_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'pdf',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    footer: true,
                    messageBottom: bottomMesage,
                    filename: 'resumen_contable_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'print',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    footer: true,
                    messageBottom: bottomMesage,
                    filename: 'resumen_contable_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }]
            });

            @if(isset($data['resumen']['resumenMonedas']))
            $('#tablaIngresosPorMoneda').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                "info": false,
                buttons: ['excel', 'pdf', 'print']
            });
            @endif

            @if(isset($data['resumen']['resumenGastosPorMoneda']))
            $('#tablaGastosPorMoneda').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                "info": false,
                buttons: ['excel', 'pdf', 'print']
            });
            @endif
        }
    </script>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/reportes/resumenContable.js') }}"></script>
@endsection
