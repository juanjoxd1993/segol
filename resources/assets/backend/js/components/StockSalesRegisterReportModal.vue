<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <input type="hidden" name="account_id" id="account_id" v-model="model.account_id">

                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Proveedor:</label>
										<input type="text" class="form-control" name="account_name" id="account_name" v-model="model.account_name" @focus="$parent.clearErrorMsg($event)">
										<div id="account_name-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">N° Orden de Pedido:</label>
										<input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_guide_number-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Serie de Factura:</label>
										<input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_serie_number-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Número de Factura:</label>
										<input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_voucher_number-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Número de SCOP:</label>
										<input type="text" class="form-control" name="scop_number" id="scop_number" v-model="model.scop_number" @focus="$parent.clearErrorMsg($event)">
										<div id="scop_number-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Costo Facturado:</label>
										<input type="text" class="form-control" name="total" id="total" v-model="model.total" @focus="$parent.clearErrorMsg($event)">
										<div id="total-error" class="error invalid-feedback"></div>
									</div>
								</div>

                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Fecha de Emisión:</label>
										<datetime
											v-model="model.fecha"
											placeholder="Selecciona una Fecha"
											:format="'dd-LL-yyyy'"
											input-id="fecha"
											name="fecha"
											value-zone="America/Lima"
											zone="America/Lima"
											class="form-control"
                                            :max-datetime="current_date"
										
											@focus="$parent.clearErrorMsg($event)">
										</datetime>
										<div id="date-error" class="error invalid-feedback"></div>
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Fecha de LLegada:</label>
										<datetime
											v-model="model.date"
											placeholder="Selecciona una Fecha"
											:format="'dd-LL-yyyy'"
											input-id="date"
											name="date"
											value-zone="America/Lima"
											zone="America/Lima"
											class="form-control"
											:max-datetime="current_date"
											@focus="$parent.clearErrorMsg($event)">
										</datetime>
										<div id="date-error" class="error invalid-feedback"></div>
									</div>
								</div>

                                <!-- <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Cantidad:</label>
										<input type="text" class="form-control" name="cantidad" id="cantidad" v-model="model.cantidad" @focus="$parent.clearErrorMsg($event)">
										<div id="cantidad-error" class="error invalid-feedback"></div>
									</div>
								</div> -->

                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">TC:</label>
										<input type="text" class="form-control" name="tc" id="tc" v-model="model.tc" @focus="$parent.clearErrorMsg($event)">
										<div id="tc-error" class="error invalid-feedback"></div>
									</div>
								</div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Almacén:</label>
                                        <select class="form-control" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="warehouse_type in warehouse_types" v-bind:value="warehouse_type.id">{{ warehouse_type.name }}</option>
                                            <!-- <option value="8">PLUSPETROL</option>
                                            <option value="9">PLUS-ZETA</option>
                                            <option value="10">NUMAY CALLAO</option>
                                            <option value="11">UNNA</option>
                                            <option value="12">NUMAY PISCO</option>
                                            <option value="14">NUMAY SOLGAS</option>
                                            <option value="16">PETROPERU TALARA</option>
                                            <option value="17">PETROPERU CALLAO</option>
                                            <option value="40">SOLGAS CALLAO</option>
                                            <option value="41">SOLGAS TRUJILLO</option>
                                            <option value="42">SOLGAS CHICLAYO</option>
                                            <option value="43">ZETA GAS CALLAO</option>
                                            <option value="44">SAVIA</option> -->
                                        </select>
                                        <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <table class="table">
                                        <thead>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Cantidad</th>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in model.details">
                                                <input type="hidden" :name="`details[${index}][id]`" :value="item.id">
                                                <td>{{ item.code }}</td>
                                                <td>
                                                    <select class="form-control" name="article_code" id="article_code" v-model="item.article_code" @focus="$parent.clearErrorMsg($event)">
                                                        <option disabled value="">Seleccionar</option>
                                                        <option v-for="article in articles" v-bind:value="article.code">{{ article.name }}</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <!-- <input type="number" step=".00001" class="form-control" :name="`details[${index}][converted_amount]`" style="width:200px" :value="item.converted_amount"> -->
                                                    <input type="number" step=".00001" class="form-control" :name="`details[${index}][converted_amount]`" style="width:200px" v-model="item.converted_amount">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
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
	import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
            url_get_articles: {
                type: String,
                default: ''
            },
            url_get_warehouse_types: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    account_id: '',
                    account_name: '',
                    old_warehouse_type_id: '',
                    warehouse_type_id: '',
                    referral_guide_number: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    scop_number: '',
                    created_at: '',
                    date: '',
                    min_datetime: '',
                    max_datetime: '',
                    cantidad: '',
                    total: '',
                    tc: '',
                    price_mes: '',
                    details: []
                },
                button_text: '',
                warehouse_types: [],
                articles: []
            }
        },
        watch: {
        },
        computed: {
        },
        created() {
            EventBus.$on('create_modal', function() {
                this.button_text = 'Crear';
				this.model = {
                    account_id: '',
                    account_name: '',
                    warehouse_type_id: '',
                    referral_guide_number: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    scop_number: '',
                    created_at: '',
                    date: '',
                    min_datetime: '',
                    max_datetime: '',
                    cantidad: '',
                    total: '',
                    tc: '',
                    price_mes: '',
                    details: []
                };

                $('#modal').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
                this.button_text = 'Actualizar';

                axios.post(this.url_get_warehouse_types)
                    .then(response => {
                        const data = response.data;
                        this.warehouse_types = data;
                    })
                    .catch(error => {
                        console.log(error);
                        console.log(error.response);
                    })

                axios.post(this.url_get_articles)
                    .then(response => {
                        const data = response.data;
                        this.articles = data;
                    })
                    .catch(error => {
                        console.log(error);
                        console.log(error.response);
                    })

                $('#modal').modal('show');
            }.bind(this));
        },
        mounted() {
        },
        methods: {
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);
                const model = this.model;

                EventBus.$emit('loading', true);

                // EventBus.$emit('loading', true);
                axios.post(url, { ...model }).then(response => {
                    EventBus.$emit('loading', false);
                    $('#modal').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model = {
                        id: '',
                        account_id: '',
                        account_name: '',
                        old_warehouse_type_id: '',
                        warehouse_type_id: '',
                        referral_guide_number: '',
                        referral_serie_number: '',
                        referral_voucher_number: '',
                        scop_number: '',
                        created_at: '',
                        date: '',
                        min_datetime: '',
                        max_datetime: '',
                        cantidad: '',
                        total: '',
                        tc: '',
                        price_mes: '',
                        details: []
					};

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