<!DOCTYPE html>
<html lang="es">
<!-- begin::Head -->

<head>
    <meta charset="utf-8" />
    <title>Punto de Distribución</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

    <!--begin::Global Theme Styles -->
    <link href="{{ public_path('backend/css/pdf_guia_electronic.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Theme Styles -->
</head>
<!-- end::Head -->

<!-- begin::Body -->

<body>
    <div id="pdf">
        <table class="pdf-head" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <div class="logo">
                        @if ( $obj->company_id == 1 )
                        <img src="{{ public_path('backend/img/logo-pdf-puntod.png') }}" alt="">
                        @elseif ( $obj->company_id == 2 )
                        <img src="{{ public_path('backend/img/logo-pdf-cordia.png') }}" alt="">
                        @endif
                    </div>
                    <div class="info">
                        <div class="business-name">{{ $obj->company_name }}</div>
                        <div class="address">
                            <p>{{ $obj->company_address }}</p>
                            <p>{{ $obj->company_district }} - {{ $obj->company_province }} -
                                {{ $obj->company_department }}
                            </p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="voucher-data">
                        <p class="ruc">R.U.C. Nº {{ $obj->company_document_number }}</p>
                        <p class="voucher-type">Guía Electrónica</p>
                        <p class="voucher-number">{{ $obj->electronic }}</p>
                        <p class="referral-guide-title">{{ $obj->movement_type_name }}</p>
                        <p class="referral-guide-number">{{ $obj->reference }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" style="line-height:1;font-size:0;"></td>
            </tr>
        </table>

        <table class="pdf-subhead" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td height="25" style="line-height:1;font-size:0;"></td>
            </tr>
            <tr>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">Cliente</div>
                        <div class="subhead-text">Emisor Itinerante</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">RUC</div>
                        <div class="subhead-text">{{ $obj->company_document_number }}</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">Emisión | Traslado</div>
                        <div class="subhead-text">{{ $obj->issue_date }} | {{ $obj->traslate_date }}</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">Dirección</div>
                        <div class="subhead-text">Lima - Lima - Lima</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" style="line-height:1;font-size:0;"></td>
            </tr>
            <tr>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">Conductor</div>
                        <div class="subhead-text">{{ $obj->chofer }}</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">N° de documento</div>
                        <div class="subhead-text">{{ $obj->chofer_document_number }}</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">N° de licencia</div>
                        <div class="subhead-text">{{ $obj->chofer_license }}</div>
                    </div>
                </td>
                <td style="vertical-align:top;">
                    <div class="subhead-obj">
                        <div class="subhead-title">Placa</div>
                        <div class="subhead-text">{{ $obj->vehicle_placa }}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" style="line-height:1;font-size:0;"></td>
            </tr>
        </table>

        <table class="pdf-items" cellspacing="0" cellpadding="0" border="0">
            <thead>
                <tr>
                    <td>Descripción</td>
                    <td>Und.</td>
                    <td>Cantidad</td>
                    <td>V. Unitario</td>
                    <td>V. Venta</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $item)
                <tr>
                    <td>{{ $item->article_name }}</td>
                    <td>UNIDAD</td>
                    <td>{{ $item->quantity }}</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="pdf-items-footer" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td>
                        <table class="pdf-total-text" cellspacing="0" cellpadding="0" border="0">
                            <tr>

                                <td style="width: 40%; padding-right: 4%;">
                                    <div class="total-text-item">
                                        <div class="total-text-title">PUNTO PARTIDA</div>
                                        <div class="total-text-text">
                                            {{ $obj->company_province }} - {{ $obj->company_department }} - {{ $obj->company_district }} <br>
                                            {{ $obj->company_address }}
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 40%; padding-left: 4%;">
                                    <div class="total-text-item">
                                        <div class="total-text-title">PUNTO LLEGADA</div>
                                        <div class="total-text-text">
                                            {{ $obj->company_province }} - {{ $obj->company_department }} - {{ $obj->company_district }} <br>
                                            {{ $obj->company_address }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="total-text-item">
                                        <div class="total-text-title">Peso Bruto</div>
                                        <div class="total-text-text">{{ $kg }}</div>
                                    </div>
                                </td>

                            </tr>
                            <tr>
                                <td>
                                    <div class="total-text-item">
                                        <img src="data:image/png;base64, {{ $document_qrcode }}">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
<!-- end::Body -->

</html>