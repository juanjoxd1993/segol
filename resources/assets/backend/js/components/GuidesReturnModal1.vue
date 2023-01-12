<template>
    <div>
        <!--begin::Modal-->
        <div class="modal fade" id="modal-warehouse" tabindex="-1" role="dialog" aria-labelledby="modalWarehouseLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalWarehouseLabel">{{ button_text }} Retorno</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control" name="article_id" id="article_id" v-model="model.article_id" @change="getArticlePrice()" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="article in filterArticles" :value="article.article_id" v-bind:key="article.article_id">{{ article.article_name }}</option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
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
                                                
                                                <th style="text-align:right;width:110px;">Cantidad</th>
                                                
                                                <th style="text-align:right;width:80px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in setDetails" v-bind:key="index">
                                                <td>{{ item.article_name }}</td>
                                                
                                                <td style="text-align:right;width:110px;">{{ item.quantity }}</td>
                                               
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
                                                <td style="text-align:right;width:80px;font-weight:600;"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" @click.prevent="liquidationModal()">Retornar</button>
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
    export default {
        props: {
          
            url_get_article_price: {
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
                    quantity: '',
 
                },
                warehouse: {
 
                    details: [],
                 },
                filterArticles: [],
                edit_flag: false,
            }
        },
        created() {

        },
        mounted() {
            this.newSelect2();

            EventBus.$on('create_modal', function() {
                let vm = this;

                this.button_text = 'Crear';
               
                this.warehouse.details = [];
                

				this.model = {
                    article_id: '',
                    article_name: '',
                    quantity: '',
                    };

                $('#client_id').val(null).trigger('change');

                $('#modal-sale').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', data => {
                this.edit_flag = true;
                this.button_text = 'Actualizar';

              
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
			
        },
        computed: {
            setDetails() {
                let articles = this.$store.state.articles;
                let warehouse_article_ids = this.warehouse.details.map(element => element.article_id);
                this.filterArticles = articles.filter(element => !warehouse_article_ids.includes(element.article_id));

                return this.warehouse.details;
            },
        },
        methods: {
            getArticlePrice: function() {
                if ( this.model.article_id != '' ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_article_price, {
                        article_id: this.model.article_id,
						warehouse_movement_id: this.$store.state.model.warehouse_movement_id,
                    }).then(response => {
                        EventBus.$emit('loading', false);
                        // console.log(response);

                       
                    }).catch(error => {
                        EventBus.$emit('loading', false);
                        // console.log(error);
                        // console.log(error.response);

                       
                    });
                }
            },
            addArticle: function() {
                let article = this.$store.state.articles.find(element => element.article_id == this.model.article_id)

                 if ( this.model.article_id == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe seleccionar un Artículo.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } 
                else if ( this.model.quantity <= 0 ) {
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

                    
                    let quantity = accounting.toFixed(model.quantity, 4);
                   
                    model.article_name = article.article_name;
                   
                    model.quantity = quantity;
                   

                    this.warehouse.details.push(model);

                    this.model = {
						article_id: '',
						article_name: '',
						quantity: '',
					};

                    this.addTotals();
                }
            },
            removeArticle: function(index) {
                this.warehouse.details.splice(index, 1);
                this.addTotals();
            },
          
            
            addWarehouse: function() {
				 if ( this.warehouse.details.length < 1 ) {
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
							
							let warehouse = JSON.parse(JSON.stringify(this.warehouse));
							this.$store.commit('addWarehouse', warehouse);

							let store_warehouse = JSON.parse(JSON.stringify(this.$store.state.warehouse));
							this.$store.commit('addWarehouses');
								
							store_warehouse.details.forEach(element => {
								let article_id = element.article_id;
								let quantity = element.quantity;

								this.$store.commit('changeBalanceValue', {
									article_id,
									quantity
								});
							});

							EventBus.$emit('refresh_table_warehouse');

							this.model = {
								article_id: '',
								article_name: '',
								quantity: '',

							};

							this.warehouse = {
							
								details: [],
								
							};

							$('#modal-warehouse').modal('hide');

							EventBus.$emit('refresh_table_warehouse');
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
                    quantity: '',
                   
                };

                this.warehouse = {
                    details: [],
                    };

				$('#modal-warehouse').modal('hide');
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
                    
                    vm.model.article_id = '';
                    vm.model.article_name = '';

                    vm.model.quantity = '';

                }).on('select2:unselect', function(e) {
                  
                    vm.warehouse.details = [];

                    vm.model.article_id = '';
                    vm.model.article_name = '';
                    vm.model.quantity = '';

                });
            },
        }
    };
</script>