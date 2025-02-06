@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Dashboard')
@section('subtitle', '')

@section('content')

    <div class="row g-3">
        <div class="col-md-2">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color:crimson;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            # Clientes
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartClientes" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color:darkturquoise;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Areas
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartAreas" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-md-12">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: darkorange;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Total venta por Mes - Contado y Crédito - PORCENTAJE
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartTipoVentaPorcentaje" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: darkorange;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Total venta por Día - Contado y Crédito
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartTipoVenta" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: darkorange;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Total venta por Día
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartBarra" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: green;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Cantidad vendida (10 KG y 45 KG) por Fecha
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chart" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: blueviolet;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Stock (%) Artículo
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartPorcentaje" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="kt-portlet" style="width: 100%;">
                <div class="kt-portlet__head" style="background-color: darkblue;">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white">
                            Stock Artículo
                        </h3>
                    </div>
                </div>
                <div class="kt-portlet__body">
                    <div id="chartStockArticulos" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ****INICIO GRAFICO DE LINEAS****
        var resultadosPorFecha = @json($resultadosPorFecha);

        var fechas = [];
        var cantidad10kg = [];
        var cantidad45kg = [];

        for (var fecha in resultadosPorFecha) {
            fechas.push(fecha);

            var total10kg = 0;
            var total45kg = 0;

            resultadosPorFecha[fecha].forEach(function(producto) {
                if (producto.weight == '10 KG') {
                    total10kg += producto.total_quantity;
                } else if (producto.weight == '45 KG') {
                    total45kg += producto.total_quantity;
                }
            });

            cantidad10kg.push(total10kg);
            cantidad45kg.push(total45kg);
        }

        var options = {
            series: [{
                    name: '10 KG',
                    data: cantidad10kg,
                    color: '#1E90FF',
                },
                {
                    name: '45 KG',
                    data: cantidad45kg,
                    color: '#FF6347',
                }
            ],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: true
                },
                toolbar: {
                    show: true,
                },
                width: '100%'
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ['#fff'],
                },
            },
            stroke: {
                width: [2, 4],
                curve: 'straight',
            },
            title: {
                text: '',
                align: 'left',
                style: {
                    fontSize: '18px',
                    fontWeight: 'bold',
                    color: '#333',
                },
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                markers: {
                    width: 12,
                    height: 12,
                    radius: 12,
                },
            },
            xaxis: {
                categories: fechas,
                title: {
                    text: 'Fecha',
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                        color: '#333',
                    },
                },
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px',
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Cantidad Vendida',
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                        color: '#333',
                    },
                },
            },
            tooltip: {
                y: [{
                    title: {
                        formatter: function(val) {
                            return val + " unidades";
                        }
                    }
                }],
                shared: true,
                intersect: false,
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 5,
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

        // FIN  GRAFICO DE LINEAS
    });

    document.addEventListener('DOMContentLoaded', function() {

        var options = {
            series: [{
                name: "S/",
                data: @json($barrasData)
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    },
                    autoSelected: 'zoom'
                },
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 8,
                    columnWidth: '55%',
                    colors: {
                        backgroundBarColors: ['#f3f3f3'],
                        backgroundBarOpacity: 0.6
                    }
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ['#FFFFFF']
                },
                formatter: function(val) {
                    return 'S/ ' + val.toFixed(2);
                }
            },
            xaxis: {
                type: 'category',
                labels: {
                    rotate: -45,
                    style: {
                        colors: '#4B5563',
                        fontSize: '12px',
                        fontWeight: 600
                    },
                    formatter: function(val) {
                        return val;
                    }
                },
                title: {
                    text: 'Fecha',
                    style: {
                        color: '#4B5563',
                        fontSize: '14px',
                        fontWeight: 'bold'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#4B5563',
                        fontSize: '12px',
                        fontWeight: 600
                    },
                    formatter: function(val) {
                        return 'S/ ' + val.toFixed(2);
                    }
                },
                title: {
                    text: 'Monto (S/)',
                    style: {
                        color: '#4B5563',
                        fontSize: '14px',
                        fontWeight: 'bold'
                    }
                }
            },
            grid: {
                borderColor: '#E0E0E0',
                strokeDashArray: 5,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                enabled: true,
                theme: 'dark',
                x: {
                    formatter: function(val) {
                        return 'Fecha: ' + val;
                    }
                },
                y: {
                    formatter: function(val) {
                        return 'S/ ' + val.toFixed(2);
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                labels: {
                    colors: '#4B5563'
                }
            }
        };


        var chart = new ApexCharts(document.querySelector("#chartBarra"), options);
        chart.render();

    });


    document.addEventListener('DOMContentLoaded', function() {

        const articles = @json($articles);
        const categories = articles.map(item => `${item.warehouse_type_name} - ${item.article_name}`);

        const seriesData = articles.map(item => {
            const percentage = ((item.article_stock / item.article_minimum) * 100).toFixed(
                2);
            return parseFloat(percentage) + "%";
        });

        var options = {
            series: [{
                name: "Stock (%)",
                data: seriesData
            }],
            chart: {
                type: "bar",
                height: 350,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                },
            },
            xaxis: {
                categories: categories,
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Arial, sans-serif',
                    },
                    offsetX: 0,
                    maxWidth: 300
                },

            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + "%";
                    }
                }
            },
            fill: {
                opacity: 1,
            },
            legend: {
                position: "top",
                horizontalAlign: "left",
                offsetX: 40,
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return parseFloat(val).toFixed(2) + "%";
                },
                style: {
                    colors: ['#000']
                },
                offsetX: 10,
            },
            colors: ['#FFC300']
        };

        var chart = new ApexCharts(document.querySelector("#chartPorcentaje"), options);
        chart.render();

    });


    document.addEventListener('DOMContentLoaded', function() {

        const clients = @json($clients);

        var options = {
            chart: {
                type: 'radialBar',
                width: 150,
                height: 150
            },
            series: [clients],
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    hollow: {
                        margin: 15,
                        size: '50%',
                        background: '#fff',
                    },
                    track: {
                        background: '#e6e6e6',
                        strokeWidth: '97%',
                        margin: 5
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            offsetY: -10,
                            show: true
                        },
                        value: {
                            fontSize: '30px',
                            fontWeight: 'bold',
                            color: '#000',
                            show: true,
                            offsetY: 10,
                            formatter: function(val) {
                                return val;
                            }
                        }
                    }
                }
            },
            labels: ['Clientes'],
        };


        var chart = new ApexCharts(document.querySelector("#chartClientes"), options);
        chart.render();


    });

    document.addEventListener('DOMContentLoaded', function() {

        const articles_stock = @json($articles_stock);
        const colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26A69A', '#D10CE8'];

        const categories = articles_stock.map(article => {
            return article.producto;
        });

        var options = {
            series: [{
                data: articles_stock.map(article => article.article_stock)
            }],
            chart: {
                height: 350,
                type: 'bar',
                events: {
                    click: function(chart, w, e) {}
                }
            },
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    borderRadius: 5,
                    dataLabels: {
                        position: 'center',
                    }
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold',
                    colors: ['#000']
                },
                formatter: function(val) {
                    return val;
                }
            },
            legend: {
                show: false
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: {
                        colors: colors,
                        fontSize: '14px',
                        fontWeight: '600',
                    },
                    align: 'center',
                    offsetX: 30,
                    maxWidth: 250,
                    rotate: -45,
                },
                axisBorder: {
                    show: true,
                    color: '#B2B2B2',
                    width: 2,
                },
                axisTicks: {
                    show: true,
                    color: '#B2B2B2',
                    width: 2,
                }
            },
            yaxis: {
                axisBorder: {
                    show: true,
                    color: '#B2B2B2',
                    width: 2,
                },
                axisTicks: {
                    show: true,
                    color: '#B2B2B2',
                    width: 2,
                },
                labels: {
                    padding: 20,
                    style: {
                        fontSize: '12px',
                        colors: ['#0644f7']
                    },
                    maxWidth: 600,
                    overflow: 'ellipsis',
                }
            },
            tooltip: {
                enabled: true,
                shared: false,
                theme: 'dark',
                x: {
                    show: false
                },
                y: {
                    formatter: function(val) {
                        return `Stock: <strong>${val}</strong>`;
                    }
                },
                custom: function({
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    const article = articles_stock[dataPointIndex];
                    const productName = article.producto;
                    const stockAmount = article.article_stock;

                    return `<div style="padding: 10px; background: #333; color: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); font-size: 14px;">
                            <strong style="font-size: 16px; color: #00E396;">${productName}</strong><br>
                            <span style="font-size: 14px;">Stock: <strong>${stockAmount}</strong></span>
                            </div>`;
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartStockArticulos"), options);
        chart.render();

    });


    document.addEventListener('DOMContentLoaded', function() {


        var options = {
            series: [{
                data: [{
                        x: 'GERENCIA',
                        y: 1
                    },
                    {
                        x: 'ADMINISTRACIÓN',
                        y: 1
                    },
                    {
                        x: 'OPERACIONES',
                        y: 1
                    },
                    {
                        x: 'COMERCIAL',
                        y: 1
                    }
                ]
            }],
            legend: {
                show: false
            },
            chart: {
                height: 107,
                type: 'treemap'
            },
            title: {
                text: '',
                align: 'center'
            },
            colors: [
                '#7F94B0',
                '#F7B844',
                '#ADD8C7',
                '#EC3C65',
            ],
            plotOptions: {
                treemap: {
                    distributed: true,
                    enableShades: false
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartAreas"), options);
        chart.render();

    });



    document.addEventListener('DOMContentLoaded', function() {


        var options = {
            series: [{
                name: '💰 Contado',
                data: {!! json_encode($tipo_ventas->pluck('total_contado')) !!}
            }, {
                name: '🏦 Crédito',
                data: {!! json_encode($tipo_ventas->pluck('total_credito')) !!}
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 8,
                    borderRadiusApplication: 'end',
                    dataLabels: {
                        position: 'top'
                    }
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return "S/ " + val.toFixed(2);
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                background: {
                    enabled: true,
                    foreColor: '#000',
                    padding: 6,
                    borderRadius: 4,
                    borderWidth: 1,
                    borderColor: '#000'
                }
            },
            stroke: {
                show: true,
                width: 3,
                colors: ['transparent']
            },
            colors: ['#faf713', '#0041fd'],
            xaxis: {
                categories: {!! json_encode($tipo_ventas->pluck('fecha')) !!},
                labels: {
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold',
                        colors: ['#333']
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Ventas en S/',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#333'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "💵 S/ " + val.toFixed(2);
                    }
                },
                theme: 'dark'
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontWeight: 'bold',
                markers: {
                    width: 12,
                    height: 12,
                    radius: 12
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartTipoVenta"), options);
        chart.render();

    });

    document.addEventListener('DOMContentLoaded', function() {

        var meses = @json($tipo_ventas_porcentaje->pluck('mes')->toArray());
        var total_contado = @json($tipo_ventas_porcentaje->pluck('total_contado')->toArray());
        var total_credito = @json($tipo_ventas_porcentaje->pluck('total_credito')->toArray());
        var porcentaje_contado = @json($tipo_ventas_porcentaje->pluck('porcentaje_contado')->toArray());
        var porcentaje_credito = @json($tipo_ventas_porcentaje->pluck('porcentaje_credito')->toArray());

        var options = {
            series: [{
                name: 'Contado',
                data: total_contado,
                color: '#eb5efa'
            }, {
                name: 'Credito',
                data: total_credito,
                color: '#11feda'
            }],
            chart: {
                type: 'bar',
                height: 430,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '100%',
                    dataLabels: {
                        position: 'center',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: 12,
                offsetY: -6,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ['#000'],
                    background: '#000',
                    borderRadius: 4,
                    padding: 8,
                    dropShadow: {
                        enabled: true,
                        blur: 5,
                        opacity: 0.7,
                        color: '#000'
                    }
                },
                formatter: function(val, opts) {
                    var porcentaje = opts.seriesIndex === 0 ? porcentaje_contado[opts.dataPointIndex] :
                        porcentaje_credito[opts.dataPointIndex];
                    return `S/ ${val} (${porcentaje.toFixed(2)}%)`;
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['#fff']
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val, opts) {
                        var porcentaje = opts.seriesIndex === 0 ? porcentaje_contado[opts
                            .dataPointIndex] : porcentaje_credito[opts.dataPointIndex];
                        return `S/ ${val} (${porcentaje.toFixed(2)}%)`;
                    }
                },
                theme: 'dark',
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                floating: true,
                fontSize: '16px',
                fontFamily: 'Arial, sans-serif',
                labels: {
                    useSeriesColors: true
                },
                markers: {
                    width: 16,
                    height: 16,
                    radius: 4
                }
            },
            xaxis: {
                categories: meses,
                title: {
                    text: 'Total Ventas S/.',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#333'
                    }
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: ['#666']
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Meses',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#333'
                    }
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: ['#666']
                    }
                }
            },
            grid: {
                borderColor: '#e0e0e0',
                strokeDashArray: 5,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            fill: {
                opacity: 0.9,
            },
        };

        var chart = new ApexCharts(document.querySelector("#chartTipoVentaPorcentaje"), options);
        chart.render();

    });
</script>
