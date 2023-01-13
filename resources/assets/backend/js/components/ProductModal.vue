<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} Producto</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <input type="hidden" name="category_id" id="category_id" v-model="model.category_id">
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
                                        <label class="form-control-label">Producto:</label>
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
                                        <label class="form-control-label">DGH:</label>
                                        <input type="text" class="form-control" name="dgh" id="dgh" placeholder="" v-model="model.dgh" @focus="$parent.clearErrorMsg($event)">
                                        <div id="dgh-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                 <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Poliza:</label>
                                        <input type="text" class="form-control" name="police" id="police" placeholder="" v-model="model.police" @focus="$parent.clearErrorMsg($event)">
                                        <div id="police-error" class="error invalid-feedback"></div>
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
                                        <select class="form-control" name="seller_id" id="seller_id"  v-model="model.seller_id" @focus="$parent.clearErrorMsg($event)">
                                         <option disabled value="">Seleccionar</option>
                                            <option value="SANTOS CCAYPANE">SANTOS CCAYPANE</option>
                                            <option value="LITO PINTO">LITO PINTO</option>
                                            <option value="EZEQUIEL MONTAÑEZ">EZEQUIEL MONTAÑEZ</option>
                                            <option value="FAUSTINO ESTRADA">FAUSTINO ESTRADA</option>
                                            <option value="FRANKLIN RONCAL">FRANKLIN RONCAL</option>
                                            <option value="ED MARTIN">ED MARTIN</option>
                                            <option value="JUAN TRUJILLO">JUAN TRUJILLO</option>
                                            <option value="JORGE FLORES ">JORGE FLORES </option>
                                            <option value="MANUEL MESTANZA">MANUEL MESTANZA</option>
                                            <option value="ALBERT VALDIVIA">ALBERT VALDIVIA</option>
                                            <option value="MIGUEL PACHERREZ">MIGUEL PACHERREZ</option>
                                            <option value="JULIO OLIVERA">JULIO OLIVERA</option>
                                            <option value="CARLOS FABIAN">CARLOS FABIAN</option>
                                            <option value="ASENCIOS POMPEYO">ASENCIOS POMPEYO</option>
                                            <option value="JOAQUIN MUNAYA">JOAQUIN MUNAYA</option>
                                            <option value="JORGE JUSTO">JORGE JUSTO</option>
                                            <option value="VICTOR CORTEZ">VICTOR CORTEZ</option>
                                            <option value="JAVIER ARIAS">JAVIER ARIAS</option>
                                            <option value="EDUARDO MORALES">EDUARDO MORALES</option>
                                            <option value="JACKSON GARCIA">JACKSON GARCIA</option>
                                            <option value="Anthony Valencia">Anthony Valencia</option>
                                            <option value="Cesar Coronado">Cesar Coronado</option>
                                            <option value="Mauricio Diaz">Mauricio Diaz</option>
                                            <option value="Percy Velaochaga">Percy Velaochaga</option>
                                            <option value="Yimmy León">Yimmy León</option>
                                            <option value="Genesis Adreani">Genesis Adreani</option>
                                            <option value="Richard Alvarado">Richard Alvarado</option>
                                            <option value="Massiel Alor">Massiel Alor</option>
                                            <option value="Omar Casachahua">Omar Casachahua</option>
                                            <option value="DANI VELASQUEZ">DANI VELASQUEZ</option>
                                            <option value="JORGE SARA">JORGE SARA</option>
                                            <option value="WILSON ROQUE">WILSON ROQUE</option>
                                            <option value="DAVID PALLI">DAVID PALLI</option>
                                            <option value="OSCAR HUANAY">OSCAR HUANAY</option>
                                             <option value="OFICINA PLANTA">OFICINA PLANTA</option>
                                     </select>
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
                                   <label class="form-control-label">Supervisor:</label>
                                   <select class="form-control" name="manager_id" id="manager_id" v-model="model.manager_id" @focus="$parent.clearErrorMsg($event)">
                                    <option disabled value="">Seleccionar</option>
                                            <option value="4">Anthony Valencia</option>
                                            <option value="3">Mauricio Diaz</option>
                                            <option value="1">Percy Velaochaga</option>
                                            <option value="5">Yimmy León</option>
                                            <option value="2">Cesar Coronado</option>
                                            <option value="6">Genesis Adreani</option>
                                            <option value="8">Omar Casachahua</option>
                                            <option value="7">Massiel Alor</option>
                                            <option value="9">OFICINA PLANTA</option>
                                     </select>
                                       <div id="manager_id-error" class="error invalid-feedback"></div>
                                      </div>
                                     </div>
                                    
                                    <div class="col-lg-4">
                                   <div class="form-group">
                                   <label class="form-control-label">Mail:</label>
                                   <select class="form-control" name="manager_mail" id="manager_mail" v-model="model.manager_mail" @focus="$parent.clearErrorMsg($event)">
                                    <option disabled value="">Seleccionar</option>
                                     </select>
                                       <div id="manager_mail-error" class="error invalid-feedback"></div>
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
            
            url_get_clients: {
                type: String,
                default: ''
            },
            url_get_select2: {
                type: String,
                default: ''
            },
            
        },
        data() {
            return {
                model: {
                    id: '',
                    category_id: '',
                    codigo_hys: '',
                    codigo_meco: '',
                    pricipio_activo: '',
                    ds_articulo: '',
                    presentacion: '',
                    estado_meco: '',
                    estado_hys: '',
                    condicion: '',
                    registro: '',
                    categoria_3: '',
                    fecha_presentacion: '',
                    registro_indecopi: '',
                    laboratorio: '',
                  
                },
                button_text: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(category_id) {
                this.button_text = 'Crear';

                this.model.id = '';
                this.model.category_id = category_id;
                this.model.codigo_hys = '';
                this.model.codigo_meco = '';
                this.model.pricipio_activo = '';
                this.model.ds_articulo = '';
                this.model.presentacion = '';
                this.model.estado_meco = '';
                this.model.estado_hys = '';
                this.model.condicion = '';
                this.model.registro = '';
                this.model.categoria_3 = '';
                this.model.fecha_presentacion = '';
                this.model.registro_indecopi = '';
                this.model.laboratorio = '';
            

               
                $('#link_client_id').val(null).trigger('change');
                $('#modal-product').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
				this.model.product_id = model.id;
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
                                company_id: vm.model.category_id,
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
                    this.model.category_id = '';
                    this.model.codigo_hys = '';
                    this.model.codigo_meco = '';
                    this.model.pricipio_activo = '';
                    this.model.ds_articulo = '';
                    this.model.presentacion = '';
                    this.model.estado_meco = '';
                    this.model.estado_hys = '';
					this.model.condicion = '';
                	this.model.registro = '';
					this.model.categoria_3 = '';
                	this.model.fecha_presentacion = '';
                    this.model.registro_indecopi = '';
                    this.model.laboratorio = '';
             
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