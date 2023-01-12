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
			<td style="font-weight:bold;">Fecha de Liquidación</td>
			<td style="font-weight:bold;">Fecha de Despacho</td>
			<td style="font-weight:bold;">Unidad de Negocio</td>
			<td style="font-weight:bold;">Tipo</td>
			<td style="font-weight:bold;"># Serie</td>
			<td style="font-weight:bold;"># Documento</td>
			<td style="font-weight:bold;">Varlor Venta</td>
			<td style="font-weight:bold;">IGV</td>
			<td style="font-weight:bold;">Total</td>
			<td style="font-weight:bold;">Percepción</td>
			<td style="font-weight:bold;">Total Percepción</td>
			<td style="font-weight:bold;">Condición de Pago</td>
			<td style="font-weight:bold;">Crédito</td>
			<td style="font-weight:bold;">Efectivo</td>
			<td style="font-weight:bold;">Depósito/Transferencia</td>
			<td style="font-weight:bold;">Banco</td>
			<td style="font-weight:bold;">Código del Cliente</td>
			<td style="font-weight:bold;">Razón Social</td>
			<td style="font-weight:bold;">Tipo de Doc.</td>
			<td style="font-weight:bold;"># de Doc.</td>
			<td style="font-weight:bold;"># de Parte</td>
			<td style="font-weight:bold;">Tipo Movimiento</td>
			<td style="font-weight:bold;">Guía</td>
			<td style="font-weight:bold;">Galones</td>
			<td style="font-weight:bold;">1K</td>
			<td style="font-weight:bold;">5K</td>
			<td style="font-weight:bold;">10K</td>
			<td style="font-weight:bold;">15K</td>
			<td style="font-weight:bold;">45K</td>
			<td style="font-weight:bold;">Total Kg.</td>
		</tr>
		@foreach ($elements as $element)
		<tr>
			<td>{{ $loop->iteration }}</td>
			<td>{{ $element->company_short_name }}</td>
			<td>{{ $element->liquidation_date }}</td>
			<td>{{ $element->sale_date }}</td>
			<td>{{ $element->business_unit_name }}</td>
			<td>{{ $element->warehouse_document_type_short_name }}</td>
			<td>{{ $element->referral_serie_number }}</td>
			<td>{{ $element->referral_voucher_number }}</td>
			<td>{{ $element->sale_value }}</td>
			<td>{{ $element->igv }}</td>
			<td>{{ $element->total }}</td>
			<td>{{ $element->perception }}</td>
			<td>{{ $element->total_perception }}</td>
			<td>{{ $element->payment_name }}</td>
			<td>{{ $element->credit }}</td>
			<td>{{ $element->cash_liquidation_amount }}</td>
			<td>{{ $element->deposit_liquidation_amount }}</td>
			<td>{{ $element->bank_short_name }}</td>
			<td>{{ $element->client_code }}</td>
			<td>{{ $element->client_business_name }}</td>
			<td>{{ $element->document_type_name }}</td>
			<td>{{ $element->client_document_number }}</td>
			<td>{{ $element->warehouse_movement_movement_number }}</td>
			<td>{{ $element->movement_type_name }}</td>
			<td>{{ $element->guide }}</td>
			<td>{{ $element->gallons }}</td>
			<td>{{ $element->sum_1k }}</td>
			<td>{{ $element->sum_5k }}</td>
			<td>{{ $element->sum_10k }}</td>
			<td>{{ $element->sum_15k }}</td>
			<td>{{ $element->sum_45k }}</td>
			<td>{{ $element->sum_total }}</td>
		</tr>
		@endforeach
    </tbody>
</table>