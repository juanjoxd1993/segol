<template>
    <div>
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Buscar
                    </h3>
                </div>
            </div>

            <!--begin::Form-->
            <div class="kt-form">
                <div class="kt-portlet__body">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label">Compañía:</label>
                                <select class="form-control" v-model="company_id">
                                    <option value="">Seleccionar</option>
                                    <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{
                                        company.name }}</option>
                                </select>
                                <div id="company_id-error" class="error invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label">Almacén:</label>
                                <select class="form-control" name="warehouse_type_id" id="warehouse_type_id"
                                    v-model="warehouse_type_id">
                                    <option value="">Seleccionar</option>
                                    <option v-for="warehouse_type in warehouse_types" :value="warehouse_type.id"
                                        v-bind:key="warehouse_type.id">{{ warehouse_type.name }}</option>
                                </select>
                                <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <div class="row">
                            <div class="col-lg-6">
                                <button class="btn btn-primary" @click="searchGuides"
                                    :disabled="!warehouse_type_id || !company_id">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Form-->
        </div>
        <div class="kt-portlet" v-if="show_table == 1">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Guías Pendientes
                    </h3>
                </div>
            </div>

            <!--begin::Form-->
            <form class="kt-form">
                <div class="kt-portlet__body">
                    <div class="row">
                        <div class="col-12">
                            <!--begin: Datatable -->
                            <div class="kt-datatable"></div>
                            <!--end: Datatable -->
                        </div>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>
    </div>
</template>

<script>
import EventBus from '../event-bus';

export default {
    props: ['warehouse_types', 'companies'],
    data() {
        return {
            company_id: '',
            warehouse_type_id: '',
            show_table: false,

            datatable: null,
        }
    },
    created() {
        let context = this;
        $(document).on('click', '.delete', function () {
            let id = $(this).data('id');

            Swal.fire({
                title: '¡Cuidado!',
                text: '¿Seguro que desea anular la guía?',
                type: "warning",
                heightAuto: false,
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then(result => {
                if (result.value) {
                    context.anularGuia(id);
                }
            });

        })
    },
    methods: {
        searchGuides() {
            this.show_table = true;

            EventBus.$emit('loading', true);

            axios.post('/operaciones/anulacionguias/search', {
                company_id: this.company_id,
                warehouse_type_id: this.warehouse_type_id,
            }).then(response => {

                this.show_table = true;

                if (this.datatable) {
                    this.datatable.destroy();
                }
                this.fillTableX(response.data);

                EventBus.$emit('loading', false);
            })
        },
        fillTableX(data) {
            this.datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'local',
                    source: data,
                    pageSize: 10,
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                },

                // layout definition
                layout: {
                    scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                    height: 400,
                    footer: false // display/hide footer
                },

                // column sorting
                sortable: true,
                pagination: true,

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
                    autoHide: false,
                },

                // columns definition
                columns: [
                    {
                        field: 'id',
                        title: '#',
                        width: 20,
                        textAlign: 'center',
                    },
                    {
                        field: 'movement_number',
                        title: 'N°',
                        textAlign: 'center',
                    },
                    {
                        field: 'creation_date',
                        title: 'Fecha',
                        textAlign: 'center',
                    },
                    {
                        field: 'referral_guide_series',
                        title: 'Serie',
                        textAlign: 'center',
                    },
                    {
                        field: 'referral_guide_number',
                        title: 'Número',
                        textAlign: 'center',
                    },
                    {
                        field: 'options',
                        title: 'Opciones',
                        sortable: false,
                        width: 120,
                        overflow: 'visible',
                        autoHide: false,
                        textAlign: 'center',
                        template: function (row) {
                            return `\
                                <div class="actions">\
                                    <a class="delete btn btn-danger btn-sm btn-icon btn-icon-md" title="Anular" data-id="${row.id}">\
                                        <i class="la la-trash"></i>\
                                    </a>\
                                </div>\
                            `;
                        },
                    },
                ]
            });

            this.datatable.columns('id').visible(false);
        },
        anularGuia(id) {
            axios.post('/operaciones/anulacionguias/anular', {
                id: id,
            }).then(response => {

                this.searchGuides();

                Swal.fire({
                    title: '¡Bien!',
                    text: 'Anulado correctamente',
                    type: "success",
                    heightAuto: false,
                });
            });
        }
    }
}
</script>