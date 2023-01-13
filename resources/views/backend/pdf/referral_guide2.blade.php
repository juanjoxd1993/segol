<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>PetroAmérica</title>
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

		<!--begin::Global Theme Styles -->
		<link href="{{ asset('backend/css/pdf.css') }}" rel="stylesheet" type="text/css" />

		<style>
			@page {
				size: A5;
			}

			@page {
				size: 14.82cm 20.99cm;
			}
		</style>
		<!--end::Global Theme Styles -->
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body id="guia-remision">
		<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
						<tr>
							<td style="width:1cm;"> </td>
							<td style="width:2.5cm;height:0.4cm;vertical-align:middle;">{{ $warehouse_movement->creation_date }}</td>
							<td style="width:1.9cm;"> </td>
							<td style="width:2cm;height:0.4cm;vertical-align:middle;">{{ $warehouse_movement->traslate_date }}</td>
							<td></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td style="height:0.52cm;font-size:0;line-height:0;"> </td></tr>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
						<tr>
							<td style="vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:3.2cm;"> </td>
													<td style="width:3.6cm;height:0.5cm;vertical-align:top;">{{ $warehouse_movement->account_name }}</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr><td style="height:0.12cm;font-size:0;line-height:0;"> </td></tr>
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:1.27cm;"> </td>
													<td style="width:5.53cm;height:0.2cm;vertical-align:middle;">{{ $warehouse_movement->account_document_number }}</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
							<td style="width:0.25cm;"> </td>
							<td style="vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:2.1cm;"> </td>
													<td style="width:3.8cm;height:0.2cm;vertical-align:top;">{{ $warehouse_movement->license_plate }}</td>
													<td> </td>
												</tr>
											</table>
										</td>
									</tr>
									<tr><td style="height:0.09cm;font-size:0;line-height:0;"> </td></tr>
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:2.3cm;"> </td>
													<td style="width:3.6cm;height:0.2cm;vertical-align:middle;"> </td>
													<td> </td>
												</tr>
											</table>
										</td>
									</tr>
									<tr><td style="height:0.12cm;font-size:0;line-height:0;"> </td></tr>
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:1.8cm;"> </td>
													<td style="width:4.0cm;height:0.2cm;vertical-align:middle;">{{ $warehouse_movement->employee_license }}</td>
													<td> </td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td style="height:0.36cm;font-size:0;line-height:0;"> </td></tr>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
						<tr>
							<td style="vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:0.5cm;"> </td>
													<td style="width:6.3cm;height:0.2cm;vertical-align:top;">{{ $warehouse_movement->company_address }}</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr><td style="height:0.10cm;font-size:0;line-height:0;"> </td></tr>
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:2.7cm;vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:1.0cm;"> </td>
																<td style="width:1.7cm;height:0.2cm;vertical-align:middle;text-transform:uppercase;">{{ $warehouse_movement->company_district }}</td>
															</tr>
														</table>
													</td>
													<td style="width:2.3cm;vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:0.6cm;"> </td>
																<td style="width:1.7cm;height:0.2cm;vertical-align:middle;text-transform:uppercase;">{{ $warehouse_movement->company_province }}</td>
															</tr>
														</table>
													</td>
													<td style="vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:0.5cm;"> </td>
																<td style="width:1.3cm;height:0.2cm;vertical-align:middle;text-transform:uppercase;">{{ $warehouse_movement->company_department }}</td>
																<td> </td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
							<td style="width:0.25cm;"> </td>
							<td style="vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:0.4cm;"> </td>
													<td style="width:6.3cm;height:0.2cm;vertical-align:top;"> </td>
												</tr>
											</table>
										</td>
									</tr>
									<tr><td style="height:0.10cm;font-size:0;line-height:0;"> </td></tr>
									<tr>
										<td>
											<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
												<tr>
													<td style="width:2.7cm;vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:1.0cm;"> </td>
																<td style="width:1.7cm;height:0.2cm;vertical-align:middle;"> </td>
															</tr>
														</table>
													</td>
													<td style="width:2.3cm;vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:0.6cm;"> </td>
																<td style="width:1.7cm;height:0.2cm;vertical-align:middle;"> </td>
															</tr>
														</table>
													</td>
													<td style="vertical-align:top;">
														<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">
															<tr>
																<td style="width:0.5cm;"> </td>
																<td style="width:1.3cm;height:0.2cm;vertical-align:middle;"> </td>
																<td> </td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td style="height:0.75cm;font-size:0;line-height:0;"> </td></tr>
			<tr>
				<td style="height:2.65cm;vertical-align:top;">
					<table cellspacing="0" cellpadding="0" border="0" style="width:100%;table-layout:auto;">
						<tr>
							<td style="width:6.65cm;vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;table-layout:auto;">
									@foreach ($elements as $item)
										<tr>
											<td style="width:0.6cm;vertical-align:top;text-align:center;">
												{{ $item->article_code }}
											</td>
											<td style="width:0.06cm;"> </td>
											<td style="width:1.75cm;vertical-align:top;">
												{{ $item->article_name }}
											</td>
											<td style="width:0.08cm;"> </td>
											<td style="width:1.05cm;vertical-align:top;text-align:right;">
												{{ $item->converted_amount }}
											</td>
											<td style="width:0.1cm;"> </td>
											<td style="vertical-align:top;"> </td>
										</tr>
										<tr><td colspan="7" style="height:0.05cm;font-size:0;line-height:0;"> </td></tr>
									@endforeach
								</table>
							</td>
							<td style="width:0.2cm;"> </td>
							<td style="width:6.65cm;vertical-align:top;">
								<table cellspacing="0" cellpadding="0" border="0" style="width:100%;table-layout:auto;">
									@foreach ($packaging as $package)
										<tr>
											<td style="width:0.55cm;vertical-align:top;text-align:center;">

											</td>
											<td style="width:0.06cm;"> </td>
											<td style="width:1.75cm;vertical-align:top;">
												{{ $package->classification_name }}
											</td>
											<td style="width:0.08cm;"> </td>
											<td style="width:1.1cm;vertical-align:top;text-align:right;">
												{{ $package->total_converted_amount }}
											</td>
											<td style="width:0.1cm;"> </td>
											<td style="vertical-align:top;">

											</td>
										</tr>
										<tr><td colspan="7" style="height:0.05cm;font-size:0;line-height:0;"> </td></tr>
									@endforeach
								</table>
							</td>
							<td> </td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td style="height:0.44cm;font-size:0;line-height:0;"> </td></tr>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" style="width:4.5cm;">
						<tr>
							<td style="width:0.9cm;"> </td>
							<td style="vertical-align:top;"> </td>
						</tr>
						<tr><td colspan="2" style="height:0.1cm;font-size:0;line-height:0;"> </td></tr>
						<tr>
							<td style="width:0.9cm;"> </td>
							<td style="vertical-align:top;"> </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
	<!-- end::Body -->
</html>
