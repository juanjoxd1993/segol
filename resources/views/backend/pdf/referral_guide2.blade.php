<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Guía de Remisión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            border: 1px solid black;
            box-sizing: border-box;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px; /* Espacio adicional encima de la tabla */
        }
        th, td {
            border: 1px solid black;
            text-align: left;
            padding: 5px; /* Aumento del padding en las celdas */
        }
        th {
            background-color: #D3D3D3;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .header {
            display: flex;
            position: relative;
            font-size: 10px;
            padding-bottom: 5px;
        }

        .header-left {
            flex: 1;
            padding-right: 10px; /* Espacio entre los headers */
        }

        .header-right {
            width: 30%;
            text-align: center;
            padding: 5px;
            border: 1px solid black;
            box-sizing: border-box;
            position: absolute;
            right: 0;
            top: 0;
            height: 50px; /* Altura fija para el header-right */
        }

        .clear {
            clear: both;
        }

        .header-title {
            font-weight: bold;
            font-size: 11px;
        }
        .header-subtitle {
            font-size: 11px;
            padding-top: 2px;
        }
        .section {
            margin-top: 20px; /* Aumento del margen superior para las secciones */
        }

        .fixed-width-container {
            width: 400px; /* Establece el ancho fijo aquí */
            overflow: hidden; /* Asegura que el contenido no desborde */
        }
      
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="fixed-width-container">
                <div class="header-title">{{ $companyData->name }}</div>
                <div class="header-subtitle">{{ $companyData->address }}</div>
            </div>

            <div class="section">
                <strong>DATOS DEL INICIO DEL TRASLADO</strong>
            </div>
            <div class="section">
                Fecha de emisión: {{ date('Y-m-d', strtotime($since_date)) }}
            </div>

            <div class="section">
                Fecha y hora de inicio del traslado: {{ $warehouse_movement->traslate_date }} 00:00:00
            </div>

            <div class="section">
                Dirección del punto de partida: {{ $companyData->address }}
            </div>
            <div class="section">
                Motivo del traslado: {{ $traslado_motivo ?? '' }}
            </div>
            <div class="section">
                Modalidad de transporte: Transporte Privado 
            </div>
            <div class="section">
                Unidad de transporte: {{ $license_plate ?? '' }}
            </div>
        </div>
        <div class="header-right">
            <div>GUÍA DE REMISIÓN</div>
            <div>ELECTRÓNICA BF REMITENTE</div>
            <div>RUC: {{ $companyData->ruc ?? '' }}</div>
            <div>{{ $warehouse_movement->referral_guide_series ?? 'G001' }}-{{ $warehouse_movement->referral_guide_number ?? '' }}</div>
        </div>
        <div class="clear"></div>
    </div>
    <!-- Resto del cuerpo de la guía de remisión -->
    <div class="section">
        <strong>DATOS DEL PUNTO DE DESTINO</strong>
        <!-- Suponiendo que estos también se proporcionan dinámicamente -->
        <div class="section">
            Dirección del punto de llegada: {{ $firstClientAddress ?? '' }}
        </div>
        <div class="section">
            Ruta fiscal: {{ $warehouse_movement->fiscal_route ?? '' }}
        </div>
    </div>
    <div class="section">
        <strong>DATOS DEL DESTINATARIO</strong>
        <div class="section">
            Apellidos y nombres, denominación o razón social: {{ $clientData->business_name ?? '' }}
        </div>
        <div class="section">
            Documento de identidad: RUC {{ $clientData->document_number ?? '' }}
        </div>
    </div>
    <div class="section">
        <strong>Datos de/los conductor/es</strong>
        <table>
            <thead>
                <tr>
                    <th>N° documento de identidad</th>
                    <th>Apellidos y nombres</th>
                    <th>N° Licencia de conducir</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $DocChofer  }}</td>
                    <td>{{ $nombreChofer }}</td>
                    <td>{{ $breveteChofer }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    <div class="section">
        <strong>Datos de/los bien/es</strong>
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Cod. bien</th>
                    <th>Descripción detallada</th>
                    <th>Unidad de medida</th>
                    <th>Cantidad</th>
                    <th>Peso</th>
                    <th>Peso total</th>
                </tr>
            </thead>
            <tbody>
			@foreach($processed_articles as $article)
            <tr>
				<td>{{ $loop->iteration }}</td>
                <td>{{ $article['article_code'] }}</td>
                <td>{{ $article['article_name'] }}</td>
                <td>NIU</td>
                <td>{{ $article['quantity'] }}</td>
				<td>{{ $article['conversion'] }}</td>
				<td>{{ $article['converted_amount'] }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="section">
        Código de verificación: {{ $warehouse_movement->verification_code ?? '' }}
    </div>
    <div class="section">
        Observaciones: {{ $warehouse_movement->observations ?? '' }}
    </div>
</body>
</html>