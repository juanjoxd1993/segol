<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-client" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <input type="hidden" name="company_id" id="company_id" v-model="model.company_id">
                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Tipo de Documento:</label>
										<select class="form-control" name="document_type_id" id="document_type_id" v-model="model.document_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="document_type in document_types" :value="document_type.id" v-bind:key="document_type.id">{{ document_type.name }}</option>
										</select>
										<div id="document_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Documento:</label>
                                        <input type="text" class="form-control" name="document_number" id="document_number" placeholder="123456789" v-model="model.document_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="document_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Código de Cliente:</label>
                                        <input type="text" class="form-control" name="client_code" id="client_code" placeholder="1234" v-model="model.client_code" @focus="$parent.clearErrorMsg($event)">
                                        <div id="client_code-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombre o Razón Social:</label>
                                        <input type="text" class="form-control" name="business_name" id="business_name" placeholder="Empresa S.A.C." v-model="model.business_name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="business_name-error" class="error invalid-feedback"></div>
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
                                        <select class="form-control kt-select2" name="ubigeo_id" id="ubigeo_id" v-model="model.ubigeo_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="ubigeo_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">GPS X:</label>
                                        <input type="text" class="form-control" name="gps_x" id="gps_x" placeholder="12.18435168" v-model="model.gps_x" @focus="$parent.clearErrorMsg($event)">
                                        <div id="gps_x-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">GPS Y:</label>
                                        <input type="text" class="form-control" name="gps_y" id="gps_y" placeholder="12.8754354" v-model="model.gps_y" @focus="$parent.clearErrorMsg($event)">
                                        <div id="gps_y-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombre de Contacto 1:</label>
                                        <input type="text" class="form-control" name="contact_name_1" id="contact_name_1" placeholder="Nombre Apellido" v-model="model.contact_name_1" @focus="$parent.clearErrorMsg($event)">
                                        <div id="contact_name_1-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombre de Contacto 2:</label>
                                        <input type="text" class="form-control" name="contact_name_2" id="contact_name_2" placeholder="Nombre Apellido" v-model="model.contact_name_2" @focus="$parent.clearErrorMsg($event)">
                                        <div id="contact_name_2-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Email:</label>
                                        <input type="text" class="form-control" name="email" id="email" placeholder="nombre@dominio.com" v-model="model.email" @focus="$parent.clearErrorMsg($event)">
                                        <div id="email-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Teléfono 1:</label>
                                        <input type="text" class="form-control" name="phone_number_1" id="phone_number_1" placeholder="987654321" v-model="model.phone_number_1" @focus="$parent.clearErrorMsg($event)">
                                        <div id="phone_number_1-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Teléfono 2:</label>
                                        <input type="text" class="form-control" name="phone_number_2" id="phone_number_2" placeholder="014754512" v-model="model.phone_number_2" @focus="$parent.clearErrorMsg($event)">
                                        <div id="phone_number_2-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Teléfono Fijo:</label>
                                        <input type="text" class="form-control" name="phone_number_3" id="phone_number_3" placeholder="014754512" v-model="model.phone_number_3" @focus="$parent.clearErrorMsg($event)">
                                        <div id="phone_number_3-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Cliente de enlace:</label>
                                        <select class="form-control kt-select2" name="link_client_id" id="link_client_id" v-model="model.link_client_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="link_client_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Unidad de Negocio:</label>
                                        <select class="form-control" name="business_unit_id" id="business_unit_id" v-model="model.business_unit_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="business_unit in business_units" :value="business_unit.id" v-bind:key="business_unit.id">{{ business_unit.name }}</option>
										</select>
                                        <div id="business_unit_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Zona:</label>
                                        <select class="form-control" name="zone_id" id="zone_id" v-model="model.zone_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="zone in client_zones" :value="zone.id" v-bind:key="zone.id">{{ zone.name }}</option>
                                        </select>
                                        <div id="zone_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Canal:</label>
                                        <select class="form-control" name="channel_id" id="channel_id" v-model="model.channel_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="channel in client_channels" :value="channel.id" v-bind:key="channel.id">{{ channel.name }}</option>
                                        </select>
                                        <div id="channel_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Ruta:</label>
                                        <select class="form-control" name="route_id" id="route_id" v-model="model.route_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="route in client_routes" :value="route.id" v-bind:key="route.id">{{ route.name }}</option>
                                        </select>
                                        <div id="route_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Sector:</label>
                                        <select class="form-control" name="sector_id" id="sector_id" v-model="model.sector_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="sector in client_sectors" :value="sector.id" v-bind:key="sector.id">{{ sector.name }}</option>
                                        </select>
                                        <div id="sector_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Vendedor:</label>
                                        <input type="text" class="form-control" name="seller_id" id="seller_id" placeholder="" v-model="model.seller_id" @focus="$parent.clearErrorMsg($event)">
                                        <div id="seller_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo de Negocio:</label>
                                        <input type="text" class="form-control" name="business_type" id="business_type" placeholder="" v-model="model.business_type" @focus="$parent.clearErrorMsg($event)">
                                        <div id="business_type-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Condición de Pago:</label>
										<select class="form-control" name="payment_id" id="payment_id" v-model="model.payment_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="payment in payments" :value="payment.id" v-bind:key="payment.id">{{ payment.name }}</option>
										</select>
                                        <div id="payment_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4" v-if="model.payment_id == 2">
                                    <div class="form-group">
                                        <label class="form-control-label">Límite de Crédito:</label>
                                        <input type="number" class="form-control" name="credit_limit" id="credit_limit" placeholder="1000" step="any" min="0" v-model="model.credit_limit" @focus="$parent.clearErrorMsg($event)">
                                        <div id="credit_limit-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4" v-if="model.payment_id == 2">
                                    <div class="form-group">
                                        <label class="form-control-label">Días de Crédito:</label>
                                        <input type="number" class="form-control" name="credit_limit_days" id="credit_limit_days" step="1" min="0" placeholder="10" v-model="model.credit_limit_days" @focus="$parent.clearErrorMsg($event)">
                                        <div id="credit_limit_days-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Percepción:</label>
										<select class="form-control" name="perception_percentage_id" id="perception_percentage_id" v-model="model.perception_percentage_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="perception in perceptions" :value="perception.id" v-bind:key="perception.id">{{ perception.value }}%</option>
										</select>
										<div id="perception_percentage_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="add_article_2">
                            {{ button_text }}
                        </button>
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
            url_get_clients: {
                type: String,
                default: ''
            },
            url_get_select2: {
                type: String,
                default: ''
            },
            document_types: {
                type: Array,
                default: ''
            },
			payments: {
                type: Array,
                default: ''
            },
            perceptions: {
                type: Array,
                default: ''
            },
			business_units: {
                type: Array,
                default: ''
            },
            client_zones: {
                type: Array,
                default: ''
            },
            client_channels: {
                type: Array,
                default: ''
            },
            client_routes: {
                type: Array,
                default: ''
            },
            client_sectors: {
                type: Array,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    company_id: '',
                    document_type_id: '',
                    document_number: '',
                    client_code: '',
                    business_name: '',
                    address: '',
                    address_reference: '',
                    ubigeo_id: '',
                    gps_x: '',
                    gps_y: '',
                    contact_name_1: '',
                    contact_name_2: '',
                    email: '',
                    phone_number_1: '',
                    phone_number_2: '',
                    phone_number_3: '',
                    link_client_id: '',
                    business_unit_id: '',
                    zone_id: '',
                    channel_id: '',
                    route_id: '',
                    sector_id: '',
                    seller_id: '',
                    business_type: '',
					payment_id: '',
                    credit_limit: '',
                    credit_limit_days: '',
                    perception_percentage_id: '',
                },
                button_text: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(company_id) {
                this.button_text = 'Crear';

                this.model.id = '';
                this.model.company_id = company_id;
                this.model.document_type_id = '';
                this.model.document_number = '';
                this.model.client_code = '';
                this.model.business_name = '';
                this.model.address = '';
                this.model.address_reference = '';
                this.model.ubigeo_id = '';
                this.model.gps_x = '';
                this.model.gps_y = '';
                this.model.contact_name_1 = '';
                this.model.contact_name_2 = '';
                this.model.email = '';
                this.model.phone_number_1 = '';
                this.model.phone_number_2 = '';
                this.model.phone_number_3 = '';
                this.model.link_client_id = '';
                this.model.business_unit_id = '';
                this.model.zone_id = '';
                this.model.channel_id = '';
                this.model.route_id = '';
                this.model.sector_id = '';
                this.model.seller_id = '';
                this.model.business_type = '';
                this.model.payment_id = '';
                this.model.credit_limit = '';
                this.model.credit_limit_days = '';
                this.model.perception_percentage_id = '';

                $('#ubigeo_id').val(null).trigger('change');
                $('#link_client_id').val(null).trigger('change');
                $('#modal-client').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
				this.model.client_code = model.code;
                this.button_text = 'Actualizar';
                
                axios.post(this.url_get_select2, {
                    ubigeo_id: this.model.ubigeo_id,
                    link_client_id: this.model.link_client_id,
                }).then(response => {
                    if ( response.data.ubigeo ) {
                        let option = new Option(response.data.ubigeo.text, response.data.ubigeo.id, true, true);
                        $('#ubigeo_id').append(option).trigger('change');
                        $('#ubigeo_id').trigger({
                            type: 'select2:select',
                            params: {
                                data: response.data.ubigeo
                            }
                        });
                    }

                    if ( response.data.link_client ) {
                        let option = new Option(response.data.link_client.text, response.data.link_client.id, true, true);
                        $('#link_client_id').append(option).trigger('change');
                        $('#link_client_id').trigger({
                            type: 'select2:select',
                            params: {
                                data: response.data.link_client
                            }
                        });
                    }

                    EventBus.$emit('loading', false);
                    $('#modal-client').modal('show');
                    console.log(response);
                }).catch(error => {
                    console.log(error.response);
                    console.log(error.response);
                });
            }.bind(this));
        },
        mounted() {
            this.newSelect2();
        },
        methods: {
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#ubigeo_id").select2({
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
                    vm.model.ubigeo_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.ubigeo_id = '';
                });

                $("#link_client_id").select2({
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
                        url: this.url_get_clients,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.model.company_id,
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
                    minimumInputLength: 1,
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.link_client_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.link_client_id = '';
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
                    EventBus.$emit('loading', false);
                    $('#modal-client').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.id = '';
                    this.model.company_id = '';
                    this.model.document_type_id = '';
                    this.model.document_number = '';
                    this.model.client_code = '';
                    this.model.business_name = '';
                    this.model.address = '';
                    this.model.address_reference = '';
                    this.model.ubigeo_id = '';
					this.model.gps_x = '';
                	this.model.gps_y = '';
					this.model.contact_name_1 = '';
                	this.model.contact_name_2 = '';
                    this.model.email = '';
                    this.model.phone_number_1 = '';
                    this.model.phone_number_2 = '';
                    this.model.phone_number_3 = '';
                    this.model.link_client_id = '';
					this.model.business_unit_id = '';
                    this.model.zone_id = '';
                    this.model.channel_id = '';
                    this.model.route_id = '';
                    this.model.sector_id = '';
                    this.model.seller_id = '';
                    this.model.business_type = '';
                    this.model.payment_id = '';
                    this.model.credit_limit = '';
                    this.model.credit_limit_days = '';
                    this.model.perception_percentage_id = '';

                    EventBus.$emit('refresh_table');

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
                            let p = c_target.prev();
                            p.addClass('is-invalid');
                        } else {
                            c_target.css('display', 'block');
                        }
                        c_target.html(item);
                    });
                });
            },
        }
    };
</script>