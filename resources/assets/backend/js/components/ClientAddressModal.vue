<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-client-address" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Direcciones</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <input type="hidden" name="client_id" id="client_id" v-model="model.client_id">
                                <input type="hidden" name="item_number" id="item_number" v-model="model.item_number">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo de Dirección:</label>
                                        <select class="form-control" name="address_type_id" id="address_type_id" v-model="model.address_type_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="address_type in address_types" :value="address_type.id" v-bind:key="address_type.id">{{ address_type.name }}</option>
                                        </select>
                                        <div id="address_type_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Dirección:</label>
                                        <input type="text" class="form-control" name="address" id="address" placeholder="Av. Ejemplo 123" v-model="model.address" @focus="$parent.clearErrorMsg($event)">
                                        <div id="address-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Referencia:</label>
                                        <input type="text" class="form-control" name="address_reference" id="address_reference" placeholder="Av. Ejemplo 123" v-model="model.address_reference" @focus="$parent.clearErrorMsg($event)">
                                        <div id="address_reference-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Ubigeo:</label>
                                        <select class="form-control kt-select2" name="address_ubigeo_id" id="address_ubigeo_id" v-model="model.address_ubigeo_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="address_ubigeo_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-success" id="add_article_2">
                                        {{ button_text }}
                                    </button>
                                    <button class="btn btn-secondary" @click.prevent="resetForm()">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                            <div class="kt-separator kt-separator--border-dashed kt-separator--space-lg"></div>
                            <div class="row">
                                <div class="col-12" @click="manageActions">
                                    <!--begin: Datatable -->
                                    <div class="kt-addresses-datatable"></div>
                                    <!--end: Datatable -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>
    <!--end::Modal-->
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
            url_get_ubigeos: {
                type: String,
                default: ''
            },
            url_address_list: {
                type: String,
                default: ''
            },
            url_address_detail: {
                type: String,
                default: ''
            },
            url_address_delete: {
                type: String,
                default: ''
            },
            address_types: {
                type: Array,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    client_id: '',
                    address_type_id: '',
                    item_number: '',
                    address: '',
                    address_reference: '',
                    address_ubigeo_id: '',
                    addresses: '',
                },
                button_text: '',
                address_datatable: undefined,
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('address_modal', function(client_id) {
                this.button_text = 'Crear';

                this.model.id = '';
                this.model.client_id = client_id;
                this.model.address_type_id = '';
                this.model.item_number = '';
                this.model.address = '';
                this.model.address_reference = '';
                this.model.address_ubigeo_id = '';

                $('#address_ubigeo_id').val(null).trigger('change');
                EventBus.$emit('loading', false);
                $('#modal-client-address').modal('show');
                this.fillTableX();
                
            }.bind(this));

            // EventBus.$on('edit_address_modal', function(model) {
            //     this.model = model;
            //     this.button_text = 'Actualizar';
                
            //     axios.post(this.url_get_select2, {
            //         ubigeo_id: this.model.ubigeo_id,
            //     }).then(response => {
            //         let option = new Option(response.data.text, response.data.id, true, true);
            //         $('#address_ubigeo_id').append(option).trigger('change');
            //         $('#address_ubigeo_id').trigger({
            //             type: 'select2:select',
            //             params: {
            //                 data: response.data
            //             }
            //         });

            //         EventBus.$emit('loading', false);
            //         $('#modal-client-address').modal('show');
            //         console.log(response);
            //     }).catch(error => {
            //         console.log(error.response);
            //         console.log(error.response);
            //     });
            // }.bind(this));
        },
        mounted() {
            this.newSelect2();

            $('#modal-client-address').on('hide.bs.modal', function(e) {
                this.address_datatable.destroy();
            }.bind(this));

            $('.kt-select2').on('select2:select', function() {
                $(this).parent().find('select').removeClass('is-invalid');
                $(this).parent().find('.error').html('');
            });
        },
        methods: {
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#address_ubigeo_id").select2({
                    placeholder: "Buscar",
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return 'No hay resultados';
                        },
                        searching: function() {
                            return 'Buscando...';
                        },
                        inputTooShort: function() {
                            return 'Ingresa 1 o más caracteres';
                        },
                        errorLoading: function() {
                            return 'No se pudo cargar la información'
                        }
                    },
                    ajax: {
                        url: this.url_get_ubigeos,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                _token: token,
                            }

                            return queryParameters;
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;

                            return {
                                results: data,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.address_ubigeo_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.address_ubigeo_id = '';
                });
            },
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);

                // EventBus.$emit('loading', true);
                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    console.log(response);
                    EventBus.$emit('loading', false);
                    // $('#modal-client-address').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.id = '';
                    this.model.client_id = '';
                    this.model.address_type_id = '';
                    this.model.item_number = '';
                    this.model.address = '';
                    this.model.address_reference = '';
                    this.model.address_ubigeo_id = '';

                    // EventBus.$emit('refresh_table');
                    $('#address_ubigeo_id').val(null).trigger('change');
                    this.address_datatable.load();

                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('.modal').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        let c_target = target.find("#" + i + "-error");
                        if (!c_target.attr('data-required')) {
                            let p = c_target.parent().find('.form-control');
                            p.addClass('is-invalid');
                        } else {
                            c_target.css('display', 'block');
                        }
                        c_target.html(item);
                    });
                });
            },
            resetForm: function() {
                this.button_text = 'Crear';

                this.model.id = '';
                this.model.address_type_id = '';
                this.model.item_number = '';
                this.model.address = '';
                this.model.address_reference = '';
                this.model.address_ubigeo_id = '';

                $('#address_ubigeo_id').val(null).trigger('change');
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.address_datatable = $('.kt-addresses-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url_address_list,
                                params: {
                                    _token: token,
                                    client_id: vm.model.client_id,
                                }
                            },
                        },
                        pageSize: 10,
                        serverPaging: true,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 300,
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
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
						{
							field: 'address_type_name',
							title: 'Tipo de Dir.',
							width: 100,
						},
                        {
                            field: 'address',
                            title: 'Dirección',
                            width: 200,
                        },
                        {
                            field: 'ubigeo_name',
                            title: 'Ubigeo',
                            width: 200,
                        },
                        {
                            field: 'id',
                            title: 'ID',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 80,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                let actions = '<div class="actions">';
									actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
										actions += '<i class="la la-edit"></i>';
									actions += '</a>';
                                    actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                        actions += '<i class="la la-trash"></i>';
                                    actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        },
                    ]
                });

                this.address_datatable.columns('id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el registro?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', true);

                        if ( result.value ) {
                            axios.post(this.url_address_delete, {
                                id: id,
                            }).then(response => {
                                if ( this.model.id == id ) {
                                    this.button_text = 'Crear';

                                    this.model.id = '';
                                    this.model.client_id = '';
                                    this.model.address_type_id = '';
                                    this.model.item_number = '';
                                    this.model.address = '';
                                    this.model.address_reference = '';
                                    this.model.address_ubigeo_id = '';

                                    $('#address_ubigeo_id').val(null).trigger('change');
                                }

								this.address_datatable.load();
                                EventBus.$emit('loading', false);
                                Swal.fire({
                                    title: '¡Ok!',
                                    text: 'Se ha eliminado correctamente',
                                    type: "success",
                                    heightAuto: false,
                                });
                            }).catch(error => {
                                console.log(error);
                                console.log(error.response);
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    EventBus.$emit('loading', true);

                    axios.post(this.url_address_detail, {
                        id: id,
                    }).then(response => {
                        console.log(response);
                        this.button_text = 'Actualizar';

                        this.model.id = response.data.element.id;
                        this.model.client_id = response.data.element.client_id;
                        this.model.address_type_id = response.data.element.address_type_id;
                        this.model.item_number = response.data.element.item_number;
                        this.model.address = response.data.element.address;
                        this.model.address_reference = response.data.element.address_reference;
                        this.model.address_ubigeo_id = response.data.element.ubigeo_id;

                        let option = new Option(response.data.option.text, response.data.option.id, true, true);
                        $('#address_ubigeo_id').append(option).trigger('change');
                        $('#address_ubigeo_id').trigger({
                            type: 'select2:select',
                            params: {
                                data: response.data.option
                            }
                        });

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
        }
    };
</script>