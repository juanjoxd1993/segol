<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>CERTIFICADO</title>
    <style>
        .center {
            margin-left: auto;
            margin-right: auto;
        }

        .text-custom {
            font-size: 1rem;
        }

        .right {
            margin-left: auto; 
            margin-right: 0;
        }
    </style>
</head>
<body>
    <table class="center" style="width: 80%;">
        <thead>
            <tr>
                <th>
                    <img src="data:image/png;base64,{{ $base64_logo }}"/>
                </th>
            </tr>
            <tr><th><h3><strong>CERTIFICADO DE TRABAJO</strong></h3></th></tr>
        </thead>
        <tbody>
            <tr><td class="text-custom">El Sr. JHONATAN GOMEZ MONROY, Identificado con DNI N° 45485080, Gerente General de PUNTO GAS S.A.C., con RUC 2052125587</td></tr>
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr><th><h3><strong>CERTIFICA:</strong></h3></th></tr>
            <tr><td></td></tr>
            <tr><td class="text-custom">Que, el</td></tr>
            <tr><td class="text-custom">Sr(a). {{ $employ->first_name . ' ' . $employ->last_name }},</td></tr>
            <tr><td></td></tr>
            <tr><td class="text-custom">Identificado con {{ $employ->document_type->name }} {{ $employ->document_number }} , ha laborado en nuestra empresa como {{ $employ->cargo }} ,</td></tr>
            <tr><td class="text-custom">Durante el periodo comprendido desde el {{ $employ->fecha_inicio ? \Carbon\Carbon::createFromFormat('Y-m-d', $employ->fecha_inicio)->format('d/m/Y') : '-' }}  hasta el {{ $employ->fecha_cese ? \Carbon\Carbon::createFromFormat('Y-m-d', $employ->fecha_cese)->format('d/m/Y') : '-' }}, demostrando durante su permanencia responsabilidad, honestidad y dedicación en las labores que le fueron encomendadas. Se expide la presente a solicitud del interesado, para los fines que crea conveniente.</td></tr>
            <tr><td></td></tr>
            <tr><td></td></tr>
        </tbody>
    </table>
    <table class="right">
        <tbody>
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr><td class="text-custom">Lima, {{ $date->day }} de {{ $date->locale('es')->monthName }} del {{ $date->year }}</td></tr>
        </tbody>
    </table>
</body>
</html>
