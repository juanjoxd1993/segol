<table>
    <thead>
		<tr>
			<th colspan="32" style="text-align:center;font-weight:bold;font-size:14pt;">REPORTE DE LIQUIDACIONES DEL {{ $initial_date }} AL {{ $final_date }}</th>
		</tr>
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
    </thead>
    <tbody>
		<tr>
			<td style="font-weight:bold;">#</td>
			<td style="font-weight:bold;">Compañía</td>
			<td style="font-weight:bold;">Fecha de Despacho</td>
			<td style="font-weight:bold;">Unidad de Negocio</td>
            <td style="font-weight:bold;">Canal</td>
            <td style="font-weight:bold;">Zona</td>
            <td style="font-weight:bold;">Sector</td>
            <td style="font-weight:bold;">Ruta</td>
			<td style="font-weight:bold;">Tipo</td>
			<td style="font-weight:bold;"># Serie</td>
			<td style="font-weight:bold;"># Documento</td>
			<td style="font-weight:bold;">Articulo</td>
			<td style="font-weight:bold;">TM</td>
			<td style="font-weight:bold;">Precio</td>
			<td style="font-weight:bold;">Total</td>
			<td style="font-weight:bold;">Código del Cliente</td>
			<td style="font-weight:bold;">Razón Social</td>
			<td style="font-weight:bold;"># de Parte</td>
			<td style="font-weight:bold;">Tipo Movimiento</td>
			<td style="font-weight:bold;">Guía</td>
			<td style="font-weight:bold;">Placa</td>
			<td style="font-weight:bold;">Distrito</td>
			<td style="font-weight:bold;">Provincia</td>

		</tr>
		@foreach ($elements as $element)
		<tr>
			<td>{{ $loop->iteration }}</td>
			<td>{{ $element->company_short_name }}</td>
			<td>{{ $element->sale_date }}</td>
			<td>{{ $element->business_unit_name }}</td>
			<td>{{ $element->client_channel_name }}</td>
			<td>{{ $element->client_zone_name }}</td>
			<td>{{ $element->client_sector_name }}</td>
			<td>{{ $element->client_route_name }}</td>
			<td>{{ $element->warehouse_document_type_short_name }}</td>
			<td>{{ $element->referral_serie_number }}</td>
			<td>{{ $element->referral_voucher_number }}</td>
			<td>{{ $element->article_name }}</td>
			<td>{{ $element->sum_total }}</td>
			<td>{{ $element->price }}</td>
			<td>{{ $element->total }}</td>
			<td>{{ $element->client_code }}</td>
			<td>{{ $element->client_business_name }}</td>
			<td>{{ $element->warehouse_movement_movement_number }}</td>
			<td>{{ $element->movement_type_name }}</td>
			<td>{{ $element->guide }}</td>
			<td>{{ $element->plate}}</td>	
			<td>{{ $element->district}}</td>			
			<td>{{ $element->province}}</td>	
		</tr>
		@endforeach
    </tbody>
</table>