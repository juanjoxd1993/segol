<table>
    <thead>
		<tr>
			<th colspan="15" style="text-align:center;font-weight:bold;font-size:14pt;">{{ $warehouse_type_name }}</th>
		</tr>
		<tr>
			<th colspan="15" style="text-align:center;font-weight:bold;font-size:14pt;">REPORTE DE ARTÍCULOS AL {{ $datetime }}</th>
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
			<td style="font-weight:bold;">Código</td>
			<td style="font-weight:bold;">Descripción</td>
			<td style="font-weight:bold;">Unidad de Medida</td>
			<td style="font-weight:bold;"># de Empaque</td>
			<td style="font-weight:bold;">Unidad de Medida Almacén</td>
			<td style="font-weight:bold;"># de Empaque Almacén</td>
			<td style="font-weight:bold;">Stock buen estado</td>
			<td style="font-weight:bold;">Stock por reparar</td>
			<td style="font-weight:bold;">Stock por devolver</td>
			<td style="font-weight:bold;">Stock mal estado</td>
			<td style="font-weight:bold;">Familia / Marca</td>
			<td style="font-weight:bold;">Grupo</td>
			<td style="font-weight:bold;">Subgrupo</td>
			<td style="font-weight:bold;">Ubicación</td>
		</tr>
		@foreach ($elements as $element)
		<tr>
			<td>{{ $loop->iteration }}</td>
			<td>{{ $element->article_code }}</td>
			<td>{{ $element->article_name }}</td>
			<td>{{ $element->sale_unit_name }}</td>
			<td>{{ $element->package_sale }}</td>
			<td>{{ $element->warehouse_unit_name }}</td>
			<td>{{ $element->package_warehouse }}</td>
			<td>{{ $element->stock_good }}</td>
			<td>{{ $element->stock_repair }}</td>
			<td>{{ $element->stock_return }}</td>
			<td>{{ $element->stock_damaged }}</td>
			<td>{{ $element->family_name }}</td>
			<td>{{ $element->group_name }}</td>
			<td>{{ $element->subgroup_name }}</td>
			<td>{{ $element->ubication }}</td>
		</tr>
		@endforeach
    </tbody>
</table>