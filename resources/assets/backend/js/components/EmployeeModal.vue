<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-client" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} Empleado</h5>
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
										<select class="form-control" name="document_type_id" id="document_type_id"  v-model="model.document_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="document_type in document_types" :value="document_type.id" v-bind:key="document_type.id">{{ document_type.name }}</option>
										</select>
										<div id="document_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Documento:</label>
                                        <input type="text" class="form-control" name="document_number" id="document_number" placeholder="123456789" v-on:keyup="searchRuc" v-model="model.document_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="document_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                         <!--       <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Código de Cliente:</label>
                                        <input type="text" class="form-control" name="id" id="id" placeholder="1234" v-model="model.id" @focus="$parent.clearErrorMsg($event)">
                                        <div id="id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>-->
                                 <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">ID:</label>
                                        <input type="text" class="form-control" name="id" id="id"  v-model="model.id" @focus="$parent.clearErrorMsg($event)"  readonly="readonly" >
                                        <div id="id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombres de Trabajador:</label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder=" Nombres y Apellidos" v-model="model.business_name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="first_name-error" class="error invalid-feedback"></div>
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
                                        <label class="form-control-label">Nombre de Contacto :</label>
                                        <input type="text" class="form-control" name="contact_name_1" id="contact_name_1" placeholder="Nombre Apellido" v-model="model.contact_name_1" @focus="$parent.clearErrorMsg($event)">
                                        <div id="contact_name_1-error" class="error invalid-feedback"></div>
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
                                        <label class="form-control-label">Teléfono:</label>
                                        <input type="text" class="form-control" name="phone_number_1" id="phone_number_1" placeholder="987654321" v-model="model.phone_number_1" @focus="$parent.clearErrorMsg($event)">
                                        <div id="phone_number_1-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                              
								
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Área:</label>
                                        <select class="form-control" name="area_id" id="area_id" v-model="model.area_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="area in areas" :value="area.id" v-bind:key="area.id">{{ area.name }}</option>
										</select>
                                        <div id="area_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                 <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Autogenerado:</label>
                                        <input type="text" class="form-control" name="dgh" id="dgh" placeholder="" v-model="model.dgh" @focus="$parent.clearErrorMsg($event)">
                                        <div id="dgh-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                 <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Sueldo:</label>
                                        <input type="text" class="form-control" name="sueldo" id="sueldo" placeholder="" v-model="model.sueldo" @focus="$parent.clearErrorMsg($event)">
                                        <div id="sueldo-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Asignación Familiar:</label>
                                        <select class="form-control" name="asignacion_id" id="asignacion_id" v-model="model.asignacion_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option value="1">Si</option>
                                            <option value="2">No</option>                                        </select>
                                        <div id="asignacion_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">AFP-ONP:</label>
                                        <select class="form-control" name="tasa_id" id="tasa_id" v-model="model.tasa_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="tasa in tasas" :value="tasa.id" v-bind:key="tasa.id">{{ tasa.name }}</option>
                                        </select>
                                        <div id="tasa_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                                                             

                                <div class="col-lg-3">
                                <div class="form-group">
                                <label class="form-control-label">Fecha de Alta:</label>
                                <datetime
                                v-model="model.since_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="since_date"
                                name="since_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="this.current_date"                        
                                @focus="$parent.clearErrorMsg($event)">
                                </datetime>
                                <div id="since_date-error" class="error invalid-feedback"></div>
                                </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Estado:</label>
                                        <input type="text" class="form-control" name="grupo" id="grupo" placeholder="" v-model="model.grupo" @focus="$parent.clearErrorMsg($event)">
                                        <div id="grupo-error" class="error invalid-feedback"></div>
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
            url_get_employees: {
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
            current_date: {
                type: String,
                default: ''
            },
			
            tasas: {
                type: Array,
                default: ''
            },
            sctasas: {
                type: Array,
                default: ''
            },
            saludtasas: {
                type: Array,
                default: ''
            },
            areas: {
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
                    first_name: '',
                    address: '',
                    address_reference: '',
                    ubigeo_id: '',
                    contact_name_1: '',
                    email: '',
                    phone_number_1: '',
                    business_type: '',
                    grupo:'',
                    since_date: this.current_date,
                    dgh: '',
                    asignacion_id:'',
                    sueldo: '',
					tasa_id: '',
                    saludtasa_id: '',
                    sctasa_id: '',
                    area_id: '',
                   
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
                this.model.first_name = '';
                this.model.address = '';
                this.model.address_reference = '';
                this.model.ubigeo_id = '';
                this.model.contact_name_1 = '';
                this.model.contact_name_2 = '';
                this.model.email = '';
                this.model.since_date = this.current_date;
                this.model.phone_number_1 = '';
                this.model.phone_number_2 = '';
                this.model.business_unit_id = '';
                this.model.tasa_id = '';
                this.model.asignacion_id = '';
                this.model.sctasa_id = '';
                this.model.saludtasa_id = '';
                this.model.area_id = '';
                this.model.business_type = '';
                this.model.dgh = '';
                this.model.grupo = '';
                this.model.manager_id= '';
                this.model.sueldo = '';
              

                $('#ubigeo_id').val(null).trigger('change');
                $('#modal-client').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
				this.model.client_code = model.code;
                this.button_text = 'Actualizar';
                
                axios.post(this.url_get_select2, {
                    ubigeo_id: this.model.ubigeo_id,
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
                    this.model.first_name = '';
                    this.model.address = '';
                    this.model.address_reference = '';
                    this.model.ubigeo_id = '';
					this.model.contact_name_1 = '';
                	this.model.contact_name_2 = '';
                    this.model.email = '';
                    this.model.phone_number_1 = '';
					this.model.area_id = '';
                    this.model.zone_id = '';
                    this.model.channel_id = '';
                    this.model.route_id = '';
                    this.model.asignacion_id = '';
                    this.model.phone_number_1 = '';
                    this.model.phone_number_2 = '';
                    this.model.business_unit_id = '';
                    this.model.tasa_id = '';
                    this.model.asignacion_id = '';
                    this.model.sctasa_id = '';
                    this.model.saludtasa_id = '';
                    this.model.area_id = '';
                    this.model.business_type = '';
                    this.model.dgh = '';
                    this.model.grupo = '';
                    this.model.manager_id= '';
                    this.model.sueldo = '';

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
            searchRuc: function() {
                let documentType = $('#document_type_id');
                let documentNumber = $('#document_number');

                if (documentType.val() == 1 && documentNumber.val().length == 11) {
                    EventBus.$emit('loading', true);
                    axios.get(this.url_search_client, {
                        params: {
                            ruc: documentNumber.val()
                        }
                    }).then(response => {
                        EventBus.$emit('loading', false);
                        this.model.business_name = response.data.business_name;
                        this.model.address = response.data.address;
                        $('#business_name').val(response.data.business_name);
                        $('#address').val(response.data.address);
                        $('#document_number-error').css('display', 'none');
                    }).catch(error => {
                        EventBus.$emit('loading', false);
                        $('#document_number-error').css('display', 'block');
                        $('#document_number-error').html('Error, no encontrado');
                        this.model.business_name = '';
                        this.model.address = '';
                    });
                }
            }
        }
    };
</script>