<template>
    <div>
        <!--begin::Modal-->
        <div class="modal fade" id="modal-sale" tabindex="-1" role="dialog" aria-labelledby="modalSaleLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSaleLabel">{{ button_text }} venta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cliente:</label>
                                        <select class="form-control kt-select2" name="client_id" id="client_id" v-model="sale.client_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="client_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo Referencia:</label>
                                        <select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id" v-model="sale.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
                                        </select>
                                        <div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Serie de Usuario:</label>
                                        <input type="text" readonly class="form-control" name="sale_serie_num" id="sale_serie_num" v-model="sale.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="sale_serie_num-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Correlativo:</label>
                                        <input type="text" readonly class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="sale.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Fecha de Factura:</label>
                                        <datetime
                                            v-model="sale.sale_date"
                                            placeholder="Selecciona una Fecha"
                                            :format="'dd-LL-yyyy'"
                                            input-id="final_effective_date"
                                            name="final_effective_date"
                                            value-zone="America/Lima"
											zone="America/Lima"
                                            class="form-control"
                                            @focus="$parent.clearErrorMsg($event)">
                                        </datetime>
                                        <div id="sale_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 6 || this.sale.warehouse_document_type_id == 8">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Referencia:</label>
                                        <input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="sale.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-3" v-if="this.sale.warehouse_document_type_id == 5 || this.sale.warehouse_document_type_id == 7 || this.sale.warehouse_document_type_id == 9 || this.sale.warehouse_document_type_id == 17">
                                    <div class="form-group">
                                        <label class="form-control-label">Serie de Guía:</label>
                                        <input type="text" class="form-control" name="referral_guide_series" id="referral_guide_series" v-model="sale.referral_guide_series" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_guide_series-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="this.sale.warehouse_document_type_id == 5 || this.sale.warehouse_document_type_id == 7 || this.sale.warehouse_document_type_id == 9 || this.sale.warehouse_document_type_id == 17">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Guía:</label>
                                        <input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="sale.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_guide_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-3">
									<div class="form-group">
                                        <label class="form-control-label">Moneda:</label>
                                        <select class="form-control" name="currency_id" id="currency_id" v-model="sale.currency_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="currency in currencies" :value="currency.id" v-bind:key="currency.id">{{ currency.name }}</option>
                                        </select>
                                        <div id="currency_id-error" class="error invalid-feedback"></div>
                                    </div>
								</div>
								<div class="col-lg-3" v-if="this.sale.warehouse_document_type_id === 4 || this.sale.warehouse_document_type_id === 5 || this.sale.warehouse_document_type_id === 18">
									<div class="form-group">
                                        <label class="form-control-label">Nº SCOP:</label>
                                        <input type="text" class="form-control"  v-model="sale.scop_number" name="scop_number" id="scop_number" @focus="$parent.clearErrorMsg($event)">
                                        <!-- <div id="scop_number-error" class="error invalid-feedback"></div> -->
                                    </div>
								</div>
                            </div>
                            <div class="row align-items-end">
                                <div class="col-12">
                                    <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                                </div>
                                <div class="col-12">
                                    <div class="kt-section">
                                        <div class="kt-section__title">Artículos</div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control" name="article_id" id="article_id" v-model="model.article_id" @change="getArticlePrice()" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="article in filterArticles" :value="article.id" v-bind:key="article.id">{{ article.name }}</option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Precio:</label>
                                        <input type="text" readonly class="form-control" name="price_igv" id="price_igv" v-model="model.price_igv" @focus="$parent.clearErrorMsg($event)">
                                        <div id="price_igv-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad:</label>
                                        <input type="number" class="form-control" name="quantity" id="quantity" v-model="model.quantity" @focus="$parent.clearErrorMsg($event)">
                                        <div id="quantity-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success" @click.prevent="addArticle()">Agregar</button>
                                        <button type="button" class="btn btn-secondary">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                                    <table class="table table-vertical-middle">
                                        <thead>
                                            <tr>
                                                <th>Artículo</th>
                                                <th style="text-align:right;width:110px;">Precio</th>
                                                <th style="text-align:right;width:110px;">Cantidad</th>
                                                <th style="text-align:right;width:110px;">Valor Venta</th>
                                                <th style="text-align:right;width:110px;">Percepción</th>
                                                <th style="text-align:right;width:110px;">Total</th>
                                                <th style="text-align:right;width:80px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in setDetails" v-bind:key="index">
                                                <td>{{ item.article_name }}</td>
                                                <td style="text-align:right;width:110px;">{{ item.price_igv }}</td>
                                                <td style="text-align:right;width:110px;">{{ item.quantity }}</td>
                                                <td style="text-align:right;width:110px;">{{ item.sale_value }}</td>
                                                <td style="text-align:right;width:110px;">{{ item.igv_perception }}</td>
                                                <td style="text-align:right;width:110px;">{{ item.total_perception }}</td>
                                                <td style="text-align:right;width:80px;">
                                                    <a href="#" class="btn-sm btn btn-label-danger btn-bold" @click.prevent="removeArticle(index)">
                                                        <i class="la la-trash-o pr-0"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td style="text-align:right;width:110px;font-weight:600;"></td>
                                                <td style="text-align:right;width:110px;font-weight:600;"></td>
                                                <td style="text-align:right;width:110px;font-weight:600;">{{ sale.total }}</td>
                                                <td style="text-align:right;width:110px;font-weight:600;">{{ sale.perception }}</td>
                                                <td style="text-align:right;width:110px;font-weight:600;">{{ sale.total_perception }}</td>
                                                <td style="text-align:right;width:80px;font-weight:600;"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" @click.prevent="liquidationModal()">Liquidar</button>
                        <!-- <button type="submit" class="btn btn-success" v-if="sale.payment_id == 2" @click.prevent="addSale()">{{ button_text }}</button> -->
                        <button type="button" class="btn btn-secondary" @click.prevent="closeModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->
    </div>
</template>

<script>
    import EventBus from '../event-bus';
    import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    export default {
        props: {
            warehouse_document_types: {
                type: Array,
                default: '',
            },
			currencies: {
                type: Array,
                default: '',
            },
            url_get_clients: {
                type: String,
                default: ''
            },
            url_get_article_price: {
                type: String,
                default: ''
            },
            url_verify_document_type: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                button_text: '',
                model: {
                    article_id: '',
                    article_name: '',
                    price_igv: '',
                    quantity: '',
                    igv: '',
                    perception: '',
                    sale_value: '',
                    igv_perception: '',
                    total_perception: '',
                },
                sale: {
                    client_id: '',
                    client_name: '',
                    document_type_id: '',
                    warehouse_document_type_id: '',
                    warehouse_document_type_name: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    referral_guide_series: '',
                    referral_guide_number: '',
                    details: [],
                    perception_percentage: '',
                    total: '',
                    perception: '',
                    total_perception: '',
                    payment_id: '',
					currency_id: 1,
                    credit_limit: '',
                    scop_number: '',
                    sale_serie_id: '',
                    sale_serie_num: '',
                },
                filterArticles: [],
                edit_flag: false,
                sale_series: []
            }
        },
        created() {

        },
        mounted() {
            this.newSelect2();

            EventBus.$on('create_modal', function() {
                this.filterArticles = this.$store.state.articles;
                let vm = this;

                this.button_text = 'Crear';
                this.sale.client_id = '';
                this.sale.client_name = '';
                this.sale.document_type_id = '';
                this.sale.warehouse_document_type_id = '';
                this.sale.warehouse_document_type_name = '';
                this.sale.referral_serie_number = '';
                this.sale.referral_voucher_number = '';
                this.sale.referral_guide_series = '';
                this.sale.referral_guide_number = '';
                this.sale.details = [];
                this.sale.perception_percentage = '';
                this.sale.total = '';
                this.sale.perception = '';
                this.sale.total_perception = '';
                this.sale.payment_id = '';
                this.sale.currency_id = 1;
                this.sale.credit_limit = '';

				this.model = {
                    article_id: '',
                    article_name: '',
                    price_igv: '',
                    quantity: '',
                    igv: '',
                    perception: '',
                    sale_value: '',
                    igv_perception: '',
                    total_perception: '',
                };

                $('#client_id').val(null).trigger('change');

                $('#modal-sale').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', data => {
                this.edit_flag = true;
                this.button_text = 'Actualizar';

                axios.post(this.url_get_clients, {
                    client_id: this.sale.client_id
                }).then(response => {
                    let option = new Option(response.data.text, response.data.id, true, true);
                    $('#client_id').append(option).trigger('change');
                    $('#client_id').trigger({
                        type: 'select2:select',
                        params: {
                            data: response.data
                        }
                    });
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
                
                $('#modal-sale').modal('show');
            });
        },
        watch: {
            // 'sale.warehouse_document_type_id': function(val) {
			// 	if ( val != 4 && val != 5 ) {
			// 		console.log(val);
			// 		this.sale.perception = 0;
			// 		this.sale.perception_percentage = 0;
			// 		this.sale.total_perception = this.sale.total;
			// 		this.sale.details.map(element => {
			// 			element.igv_perception = '0.0000';
			// 			element.total_perception = element.sale_value;
			// 		});
			// 	}
			// },
			'sale.warehouse_document_type_id': function(val) {

                const data_filter = this.$store.state.sale_series.filter(item => item.warehouse_document_type_id === val)[0];

                this.sale.sale_serie_id = data_filter.id;
                this.sale.referral_serie_number = data_filter.num_serie;
                this.sale.referral_voucher_number = data_filter.correlative;

                let warehouse_document_type = this.warehouse_document_types.find(element => element.id == val);

                this.sale.warehouse_document_type_name = warehouse_document_type ? warehouse_document_type.name : '';
			},
			// 'sale.sale_serie_id': function(val) {
			// 	let sale_serie = this.sale_series.find(element => element.id == val);
			// 	this.sale.referral_serie_number = sale_serie ? sale_serie.correlative : '';
			// }
        },
        computed: {
            setDetails() {
                let articles = this.$store.state.articles;
                let sale_article_ids = this.sale.details.map(element => element.article_id);
                // this.filterArticles = articles.filter(element => !sale_article_ids.includes(element.article_id));

                return this.sale.details;
            },
        },
        methods: {
            getArticlePrice: function() {
                if ( this.model.article_id != '' ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_article_price, {
                        article_id: this.model.article_id,
                        client_id: this.sale.client_id,
						warehouse_movement_id: this.$store.state.model.warehouse_movement_id,
                    }).then(response => {
                        EventBus.$emit('loading', false);
                        // console.log(response);

                        this.model.price_igv = response.data.price_igv;
                        this.model.igv = response.data.article.igv;
                        this.model.perception = response.data.article.perception;
                    }).catch(error => {
                        EventBus.$emit('loading', false);
                        // console.log(error);
                        // console.log(error.response);

                        Swal.fire({
                            title: '¡Error!',
                            text: 'El Artículo no cuenta con un precio para este Cliente.',
                            type: "error",
                            heightAuto: false,
                            showCancelButton: false,
                            confirmButtonText: 'Ok',
                        });
                    });
                }
            },
            addArticle: function() {
                let article = this.$store.state.articles.find(element => element.id == this.model.article_id)

                if ( this.sale.client_id == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe seleccionar un Cliente.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( this.model.article_id == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe seleccionar un Artículo.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( this.model.price_igv == '' || this.model.price_igv == undefined ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'El Artículo no cuenta con un precio para este Cliente.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( this.model.quantity <= 0 ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'La Cantidad no puede estar vacía o ser igual 0.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( this.model.quantity > Number(article.new_balance_converted_amount) ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'La Cantidad supera el Saldo del Artículo (' + article.new_balance_converted_amount + ').',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else {
					let model = JSON.parse(JSON.stringify(this.model));

                    let price_igv = accounting.toFixed(model.price_igv, 4);
                    let quantity = accounting.toFixed(model.quantity, 4);
                    let perception_percentage = ( model.perception === 1 && (this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 5) ? Number(this.sale.perception_percentage) / 100 : 0 );
                    let sale_value = accounting.toFixed(quantity * price_igv, 2);
                    let igv_perception = accounting.toFixed(sale_value * perception_percentage, 4);
					let total_perception = accounting.toFixed(Number(sale_value) + Number(igv_perception), 4);

                    model.article_name = article.article_name;
                    model.price_igv = price_igv;
                    model.quantity = quantity;
                    model.sale_value = sale_value;
                    model.igv_perception = igv_perception;
                    model.total_perception = total_perception;

                    this.sale.details.push(model);

                    this.model = {
						article_id: '',
						article_name: '',
						price_igv: '',
						quantity: '',
						igv: '',
						perception: '',
						sale_value: '',
						igv_perception: '',
						total_perception: '',
					};

                    this.addTotals();
                }
            },
            removeArticle: function(index) {
                this.sale.details.splice(index, 1);
                this.addTotals();
            },
            addTotals: function() {
                this.sale.total = (this.sale.details.reduce((a, {sale_value}) => Number(a) + Number(sale_value), 0)).toFixed(4);
                this.sale.perception = (this.sale.details.reduce((a, {igv_perception}) => Number(a) + Number(igv_perception), 0)).toFixed(4);
                this.sale.total_perception = (this.sale.details.reduce((a, {total_perception}) => Number(a) + Number(total_perception), 0)).toFixed(4);
            },
            liquidationModal: function() {
                if ( this.sale.warehouse_document_type_id == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe seleccionar un Tipo de Referencia.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( (this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 5) && this.sale.document_type_id != 1 ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'El Cliente no cuenta con un Nº de RUC registrado.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.warehouse_document_type_id != 4 && this.sale.warehouse_document_type_id != 5 && accounting.unformat(this.sale.perception) > 0 ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'La Percepción no puede ser mayor a 0 para este Tipo de Referencia.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.warehouse_document_type_id >= 4 && this.sale.warehouse_document_type_id <= 9 && this.sale.referral_serie_number == '' ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'La Serie de Referencia es obligatoria.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( (this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 6 || this.sale.warehouse_document_type_id == 8) && this.sale.referral_voucher_number == '' ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'El Número de Referencia es obligatorio.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.details.length < 1 ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe agregar al menos 1 Artículo.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else {
					EventBus.$emit('loading', true);
                    

                    this.$store.state.sale_series.map(item => {
                        if (item.id === this.sale.sale_serie_id) {
                            item.last_correlative = item.correlative;
                            item.correlative = item.correlative + 1;
                        };
                    });

					axios.post(this.url_verify_document_type, {
						'model': this.$store.state.model,
						'warehouse_document_type_id': this.sale.warehouse_document_type_id,
						'referral_serie_number': this.sale.referral_serie_number,
						'referral_voucher_number': this.sale.referral_voucher_number
					}).then(response => {
						// console.log(response);
						if ( response.data.verify == false ) {
							EventBus.$emit('loading', false);

							Swal.fire({
								title: '¡Error!',
								text: response.data.msg,
								type: "error",
								heightAuto: false,
								showCancelButton: false,
								confirmButtonText: 'Ok',
							});
						} else {
							EventBus.$emit('loading', false);
							
							let sale = JSON.parse(JSON.stringify(this.sale));

							this.$store.commit('addSale', sale);
							$('#modal-sale').modal('hide');
							$('#modal-sale').on('hidden.bs.modal', function(e) {
								if ( sale.details.length > 0 ) {
									EventBus.$emit('liquidation_modal');
								}
							});

							this.model = {
								article_id: '',
								article_name: '',
								price_igv: '',
								quantity: '',
								igv: '',
								perception: '',
								sale_value: '',
								igv_perception: '',
								total_perception: '',
							};

							this.sale = {
								client_id: '',
								client_name: '',
								document_type_id: '',
								warehouse_document_type_id: '',
								warehouse_document_type_name: '',
								referral_serie_number: '',
								referral_voucher_number: '',
								referral_guide_series: '',
								referral_guide_number: '',
								details: [],
								perception_percentage: '',
								total: '',
								perception: '',
								total_perception: '',
								payment_id: '',
								currency_id: 1,
                              credit_limit: '',
							};
						}
					}).catch(error => {
						console.log(error);
						console.log(error.response);
					});
                }
            },
            addSale: function() {
				if ( this.sale.warehouse_document_type_id == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe seleccionar un Tipo de Referencia.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( (this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 5) && this.sale.document_type_id != 1 ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'El Cliente no cuenta con un Nº de RUC registrado.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.warehouse_document_type_id != 4 && this.sale.warehouse_document_type_id != 5 && accounting.unformat(this.sale.perception) > 0 ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'La Percepción no puede ser mayor a 0 para este Tipo de Referencia.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.warehouse_document_type_id >= 4 && this.sale.warehouse_document_type_id <= 9 && this.sale.referral_serie_number == '' ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'La Serie de Referencia es obligatoria.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( (this.sale.warehouse_document_type_id == 4 || this.sale.warehouse_document_type_id == 6 || this.sale.warehouse_document_type_id == 8) && this.sale.referral_voucher_number == '' ) {
					Swal.fire({
                        title: '¡Error!',
                        text: 'El Número de Referencia es obligatorio.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				} else if ( this.sale.details.length < 1 ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe agregar al menos 1 Artículo.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else {
					EventBus.$emit('loading', true);

					axios.post(this.url_verify_document_type, {
						'model': this.$store.state.model,
						'warehouse_document_type_id': this.sale.warehouse_document_type_id,
						'referral_serie_number': this.sale.referral_serie_number,
						'referral_voucher_number': this.sale.referral_voucher_number
					}).then(response => {
						// console.log(response);
						if ( response.data.verify == false ) {
							EventBus.$emit('loading', false);

							Swal.fire({
								title: '¡Error!',
								text: response.data.msg,
								type: "error",
								heightAuto: false,
								showCancelButton: false,
								confirmButtonText: 'Ok',
							});
						} else {
							EventBus.$emit('loading', false);
							
							let sale = JSON.parse(JSON.stringify(this.sale));
							this.$store.commit('addSale', sale);

							let store_sale = JSON.parse(JSON.stringify(this.$store.state.sale));
							this.$store.commit('addSales');
								
							store_sale.details.forEach(element => {
								let article_id = element.article_id;
								let quantity = element.quantity;

								this.$store.commit('changeBalanceValue', {
									article_id,
									quantity
								});
							});

							EventBus.$emit('refresh_table_sale');

							this.model = {
								article_id: '',
								article_name: '',
								price_igv: '',
								quantity: '',
								igv: '',
								perception: '',
								sale_value: '',
								igv_perception: '',
								total_perception: '',
							};

							this.sale = {
								client_id: '',
								client_name: '',
								document_type_id: '',
								warehouse_document_type_id: '',
								warehouse_document_type_name: '',
								referral_serie_number: '',
								referral_voucher_number: '',
								referral_guide_series: '',
								referral_guide_number: '',
								details: [],
								perception_percentage: '',
								total: '',
								perception: '',
								total_perception: '',
								payment_id: '',
								currency_id: 1,
                                credit_limit: '',
							};

							$('#modal-sale').modal('hide');

							EventBus.$emit('refresh_table_liquidation');
						}
					}).catch(error => {
						console.log(error);
						console.log(error.response);
					});
				}
            },
			closeModal: function() {
				this.model = {
                    article_id: '',
                    article_name: '',
                    price_igv: '',
                    quantity: '',
                    igv: '',
                    perception: '',
                    sale_value: '',
                    igv_perception: '',
                    total_perception: '',
                };

                this.sale = {
                    client_id: '',
                    client_name: '',
                    warehouse_document_type_id: '',
                    warehouse_document_type_name: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    details: [],
                    perception_percentage: '',
                    total: '',
                    perception: '',
                    total_perception: '',
                    payment_id: '',
					currency_id: 1,
                    credit_limit: '',
                };

				$('#modal-sale').modal('hide');
			},
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#client_id").select2({
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
                                company_id: vm.$store.state.model.company_id,
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
                    // var selected_element = $(e.currentTarget);
                    // vm.sale.client_id = parseInt(selected_element.val());
                    vm.sale.client_id = e.params.data.id;
                    vm.sale.client_name = e.params.data.text;
                    vm.sale.document_type_id = e.params.data.document_type_id;
                    vm.sale.payment_id = e.params.data.payment_id;
                    vm.sale.perception_percentage = e.params.data.perception_percentage.value;
                    vm.sale.credit_limit = e.params.data.credit_limit;

                    vm.model.article_id = '';
                    vm.model.article_name = '';
                    vm.model.price_igv = '';
                    vm.model.quantity = '';
                    vm.model.igv = '';
                    vm.model.perception = '';
                    vm.model.perception = '';
                    vm.model.sale_value = '';
                    vm.model.igv_perception = '';
                    vm.model.total_perception = '';
                }).on('select2:unselect', function(e) {
                    vm.sale.client_id = '';
                    vm.sale.client_name = '';
                    vm.sale.document_type_id = '';
                    vm.sale.payment_id = '';
                    vm.sale.credit_limit = '';
                    vm.sale.perception_percentage = 0;
                    vm.sale.total = 0;
                    vm.sale.perception = 0;
                    vm.sale.total_perception = 0;
                    vm.sale.details = [];

                    vm.model.article_id = '';
                    vm.model.article_name = '';
                    vm.model.price_igv = '';
                    vm.model.quantity = '';
                    vm.model.igv = '';
                    vm.model.perception = '';
                    vm.model.sale_value = '';
                    vm.model.igv_perception = '';
                    vm.model.total_perception = '';
                });
            },
        }
    };
</script>