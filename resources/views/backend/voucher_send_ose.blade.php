@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Dashboard')
@section('subtitle', '')

@section('content')

    <div class="row g-3">
        <div class="col-md-6">
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
    </div>

@endsection

<script>
    
    document.addEventListener('DOMContentLoaded', function () {

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
            series: [
                {
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
                y: [
                    {
                        title: {
                            formatter: function (val) {
                                return val + " unidades";
                            }
                        }
                    }
                ],
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

    document.addEventListener('DOMContentLoaded', function () {

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
</script>
