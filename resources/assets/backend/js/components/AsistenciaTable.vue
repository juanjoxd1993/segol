<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        <a href="#" class="btn btn-success" id="createRecord" v-show="flag_modify == 1" @click.prevent="openModal(ids, url_save)">
                            <i class="la la-edit"></i> Guardar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-portlet__body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="flaticon flaticon-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="generalSearch" id="generalSearch" placeholder="Buscar...">
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet__body kt-portlet__body--fit">
            <!--begin: Datatable -->
            <div class="kt-datatable"></div>
            <!--end: Datatable -->
        </div>
    </div>
    <!--end::Portlet-->
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
            asist_types: {
                type: Array,
                default: ''
            },
            url_save: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                datatable: undefined,
                model: '',
                flag_modify: 0,
                ids: [],
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.model = response;
                if ( this.datatable == undefined ) {
                    this.fillTableX();
					this.ids = [];
                } else {
                    this.datatable.setDataSourceParam('model', this.model);
                    this.datatable.load();
					this.ids = [];
                }

                this.datatable.on('kt-datatable--on-ajax-done', function() {
                    EventBus.$emit('loading', false);
                });
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.datatable != undefined ) {
                    this.datatable.load();
					this.ids = [];
                }
            }.bind(this));
        },
        watch: {
            ids: function() {
                if ( this.ids.length > 0 ) {
                    this.flag_modify = 1;
                } else {
                    this.flag_modify = 0;
                }
            }
        },
        computed: {

        },
        methods: {
            openModal: function(employee_ids, url_save) {
                var vm = this;
                var url = url_save;
                var fd = new FormData();
                let data = {};

                employee_ids.forEach(employeeId => {
                    let asist_values = $('.row-employee[data-employee="' + employeeId + '"]').find('.asist-cell').toArray().filter(x => $(x).val() !== '' && $(x).val() > 0).map(function(x) {
                        return {
                            asist_type: $(x).data('asist-type'),
                            asist_value: $(x).val()
                        };
                    });

                    data[employeeId] = asist_values;
                });

           
                fd.append('asist_values', JSON.stringify(data));
                fd.append('ciclo_id', this.model.ciclo_id);


                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea guardar las Asistencias?',
                    type: "warning",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    if ( result.value ) {
                        EventBus.$emit('loading', true);

                        axios.post(url, fd, { headers: {
                                'Content-type': 'application/x-www-form-urlencoded',
                            }
                        }).then(response => {
                            EventBus.$emit('loading', false);

                            $('#modal').modal('hide');

                            this.model.price_ids = [];
                            this.model.operation_id = '';
                            this.model.amount = '';
                            this.model.amount2 = '';
                            this.min_effective_date = '';

                            EventBus.$emit('refresh_table');

                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se actualizó la Asistencia exitosamente.',
                                type: "success",
                                heightAuto: false,
                            });

                        }).catch(error => {
                            EventBus.$emit('loading', false);

                            var obj = error.response.data.errors;
                            $('.modal').animate({
                                scrollTop: 0
                            }, 500, 'swing');
                            $.each(obj, function(i, item) {
                                let c_target = target.find("#" + i + "-error");
                                let p = c_target.parents('.form-group').find('#' + i);
                                p.addClass('is-invalid');
                                c_target.css('display', 'block');
                                c_target.html(item);
                            });
                        });
                    } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                        EventBus.$emit('loading', false);
                    }
                });
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                let dynamicColumns = [
                    {
                        field: 'employ_id',
                        title: '#',
                        sortable: false,
                        width: 30,
                        selector: {class: 'kt-checkbox--solid'},
                        textAlign: 'center',
                    },
                    {
                        field: 'first_name',
                        title: 'Empleado',
                        width: 60,
                    },
                    {
                        field: 'document_number',
                        title: 'Código',
                        width: 60,
                    },
                ];

                let asistTypes = vm.asist_types;

                asistTypes.forEach(asistType => {
                    dynamicColumns.push({
                        field: 'asist_type_' + asistType.id,
                        title: asistType.name,
                        template: function(row) {
                            let value = '';

                            switch (asistType.id) {
                                case 1: //Falta
                                    value = row.laborables;
                                    break;
                                case 2: //Tardanza
                                    value = row.minutos_tarde;
                                    break;
                                case 3: //Horas Extra
                                    value = row.horas_extra_25;
                                    break;
                                case 4: //Horas Extra 35
                                    value = row.horas_extra_35;
                                    break;
                                case 5: //Bonificación Nocturna
                                    value = row.horas_noc_25;
                                    break;
                                case 6: //Dias No sub
                                    value = row.horas_noc_35;
                                    break;
                            }

                            return '<input type="number" min="1" class="form-control asist-cell" data-asist-type="' + asistType.id + '" value="' + value + '"/>';
                        },
                        width: 75
                    });
                });


                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model,
                                },
                                map: function(raw) {
                                    var dataSet = raw;
                                    if (typeof raw.data !== 'undefined') {
                                        dataSet = raw.data;
                                    }
                                    
                                    return dataSet;
                                },
                            },
                        },
                        pageSize: 10,
                        // serverPaging: true,
                        // serverFiltering: true,
                        // serverSorting: true,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: false,

                    search: {
                        input: $('#generalSearch'),
                    },

                    extensions: {
                        checkbox: {
                            vars: {
                                selectedAllRows: 'selectedAllRows',
                                requestIds: 'requestIds',
                                rowIds: 'meta.rowIds',
                            },
                        },
                    },

                    translate: {
                        records: {
                            processing: 'Espere porfavor...',
                            noRecords: 'No hay registros'
                        },
                        toolbar: {
                            pagination: {
                                items: {
                                    default: {
                                        first: 'Primero',
                                        prev: 'Anterior',
                                        next: 'Siguiente',
                                        last: 'Último',
                                        more: 'Más páginas',
                                        input: 'Número de página',
                                        select: 'Seleccionar tamaño de página'
                                    },
                                    info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                                }
                            }
                        }
                    },

                    rows: {
                        autoHide: true,
                        callback: function(row, data, index) {
                            $(row).attr('data-employee', data.employ_id);
                            $(row).addClass('row-employee');
                        }
                    },

                    // columns definition
                    columns: dynamicColumns
                });

                $('.kt-datatable').on('kt-datatable--on-check', function(a, e) {
                    console.log(e);
                    $.each(e, function(key, value) {
                        let index = vm.ids.findIndex((element) => element == value);
                        if ( index < 0 ) {
                            vm.ids.push(value);
                        }
                    });

                    vm.ids.sort(function(a, b) {
                        let aNumber = parseInt(a);
                        let bNumber = parseInt(b);
                        return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                    });
                });

                $('.kt-datatable').on('kt-datatable--on-uncheck', function(a, e) {
                    $.each(e, function(key, value) {
                        let index = vm.ids.findIndex((element) => element == value);
                        if ( index >= 0 ) {
                            vm.ids.splice(index, 1);
                        }
                    });

                    vm.ids.sort(function(a, b) {
                        let aNumber = parseInt(a);
                        let bNumber = parseInt(b);
                        return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                    });
                });
            },
        }
    };
</script>