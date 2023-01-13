<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">
		<title>PetroAmérica</title>
	</head>
	<body class="mail">
		<div class="body" style="background-color:#f2f3f8;padding:30px 15px;">
			<div class="content" style="max-width:600px;padding:30px;background-color:#FFF;margin:0 auto;font-family:Arial,sans-serif;">
				<img src="{{ assewt('backend/img/logo-pdf.png') }}" alt="" style="display:block;width:150px;height:auto;margin:0 0 0 auto;">
				<h3>Estimado Cliente Sr(es). <b>{{ $mail_info->client_name }}</b></h3>
				@if ( $mail_info->voucher_type_type == 'RC' )
					<p style="margin:0 0 20px;font-size:15px;">Se realizó envío de Resumen de la empresa {{ $mail_info->company_name }} RC-{{ $mail_info->summary_date }} ticket: {{ $mail_info->summary_ticket }} por {{ $mail_info->ids_count }} documentos</p>
				@else
					@if ( $mail_info->low_number )
						<p style="margin:0 0 20px;font-size:15px;">Informamos a usted que el comprobante <b>Comunicación de Baja N. {{ $mail_info->issue_date }}-{{ $mail_info->low_number }}</b>, ya se encuentra disponible.</p>
					@else
						<p style="margin:0 0 20px;font-size:15px;">Informamos a usted que el comprobante <b>{{ $mail_info->voucher_type_name }} N. {{ $mail_info->serie_number }}-{{ $mail_info->voucher_number }}</b>, ya se encuentra disponible.</p>
					@endif
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">RUC Emisor:</p>
					<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->company_document_number }}</p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Tipo:</p>
					@if ( $mail_info->low_number )
						<p style="margin:0 0 20px;font-size:15px;">Comunicación de Baja</p>
					@else
						<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->voucher_type_name }}</p>
					@endif
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Número:</p>
					<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->serie_number }}-{{ $mail_info->voucher_number }}</p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Monto:</p>
					<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->currency_symbol }} {{ $mail_info->total }}</p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Fecha de Emisión:</p>
					<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->issue_date }}</p>
					@if ( $mail_info->expiry_date )
						<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Fecha de Vencimiento:</p>
						<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->expiry_date }}</p>
					@endif
				@endif
				<p style="margin:0;font-size:15px;">Atentamente,</p>
				<p style="margin:0 0 20px;font-size:15px;">{{ $mail_info->company_name }}</p>
			</div>
		</div>
	</body>
</html>