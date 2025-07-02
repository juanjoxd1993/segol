<table>
    <thead>
        <tr>
            <th colspan="20" style="font-weight:bold; text-align: center;background-color: aqua">Reporte Credito
                Histórico</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight:bold;">FECHA CIERRE</td>
            <td style="font-weight:bold;">COMPAÑIA</td>
            <td style="font-weight:bold;">TIPO COMPROBANTE</td>
            <td style="font-weight:bold;">GUIA</td>
            <td style="font-weight:bold;">N° SERIE</td>
            <td style="font-weight:bold;">N° DOC.</td>
            <td style="font-weight:bold;">FECHA VENTA</td>
            <td style="font-weight:bold;">FECHA VENC.</td>
            <td style="font-weight:bold;">RUTA</td>
            <td style="font-weight:bold;">ID CLIENTE</td>
            <td style="font-weight:bold;">DOC. CLIENTE</td>
            <td style="font-weight:bold;">N° DOC.</td>
            <td style="font-weight:bold;">RAZON SOCIAL</td>
            <td style="font-weight:bold;">TIPO VENTA</td>
            <td style="font-weight:bold;">MONEDA</td>
            <td style="font-weight:bold;">TOTAL</td>
            <td style="font-weight:bold;">PAGO A CUENTA</td>
            <td style="font-weight:bold;">SALDO</td>
            <td style="font-weight:bold;">UNIDAD NEGOCIO</td>
            <td style="font-weight:bold;">SUPERVISOR</td>
        </tr>

        @foreach ($lista as $item)
            <tr>
                <td>{{ $item->fecha_cierre }}</td>
                <td>{{ $item->company_short_name }}</td>
                <td>{{ $item->warehouse_document_type_name }}</td>
                <td>{{ $item->guia }}</td>
                <td>{{ $item->referral_serie_number }}</td>
                <td>{{ $item->referral_voucher_number }}</td>
                <td>{{ $item->sale_date }}</td>
                <td>{{ $item->expiry_date }}</td>
                <td>{{ $item->client_route_id }}</td>
                <td>{{ $item->client_id }}</td>
                <td>{{ $item->document_type_name }}</td>
                <td>{{ $item->document_number }}</td>
                <td>{{ $item->business_name }}</td>
                <td>{{ $item->payment_name }}</td>
                <td>{{ $item->currency_symbol }}</td>
                <td>{{ $item->total_perception }}</td>
                <td>{{ $item->paid }}</td>
                <td>{{ $item->balance }}</td>
                <td>{{ $item->business_unit_name }}</td>
                <td>{{ $item->manager }}</td>
            </tr>
        @endforeach
    </tbody>

</table>
