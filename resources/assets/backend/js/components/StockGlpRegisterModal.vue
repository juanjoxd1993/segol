<template>
    <!--begin::Modal-->
    <div class="modal fade" id="stock-glp-register-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="sendForm()">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar artículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control kt-select2" name="article" id="article" v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option 
												v-for="article in filterArticles"
												:value="article.id"
												v-bind:key="article.id"
												>
												{{ article.full_name }}
											</option>
                                        </select>
                                        <div id="article-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad: (Stock: {{ Number(stock) }})</label>
                                        <input type="tel" class="form-control" name="quantity" id="quantity" placeholder="0" v-model="model.quantity" @focus="$parent.clearErrorMsg($event)">
                                        <div id="quantity-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad: (Peso Inicial: {{ Number(stock) }})</label>
                                        <input type="tel" class="form-control" name="quantity_2" id="quantity_2" placeholder="0" v-model="model.quantity_2" @focus="$parent.clearErrorMsg($event)">
                                        <div id="quantity_2-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                 <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad: (Peso Bruto: {{ Number(stock) }})</label>
                                        <input type="tel" class="form-control" name="quantity_3" id="quantity_3" placeholder="0" v-model="model.quantity_3" @focus="$parent.clearErrorMsg($event)">
                                        <div id="quantity_3-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                               
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="add_article_2" disabled>
                            <i class="fa fa-plus"></i> Agregar artículo
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
            url_get_article: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                model: {
                    article_id: '',
                    quantity: '',
                    quantity_2: '',
                    quantity_3: '',
              
                },
                articles: [],
                article_list: [],
                movement_class_id: '',
                movement_type_id: '',
                stock_good: 0,
                stock_repair: 0,
                stock_return: 0,
                stock_damaged: 0,
                stock: 0,
            }
        },
        watch: {
            'model.quantity': function(val) {
                this.model.sale_value = (val * this.model.price).toFixed(4);
                if ( this.movement_class_id == 2 && Number(val) > Number(this.stock) ) {
                    // $('input#quantity').addClass('is-invalid');
                    // $('#quantity-error').html('La cantidad supera el Stock disponible');
                    document.getElementById('add_article_2').disabled = false;
                } else if ( this.model.article_id != '' && (Number(val) != '' && Number(val) > 0) ) {
                    $('input#quantity').removeClass('is-invalid');
                    $('#quantity-error').html('');
                    document.getElementById('add_article_2').disabled = false;
                } else {
                    document.getElementById('add_article_2').disabled = true;
                }
            },
            'model.price': function(val) {
                this.model.sale_value = (this.model.quantity * val).toFixed(4);
            },
            'model.article_id': function(val) {
                if ( val != '' && (this.model.quantity != '' || Number(this.model.quantity) > 0)  ) {
                    document.getElementById('add_article_2').disabled = false;
                } else {
                    document.getElementById('add_article_2').disabled = true;
                }
            }
        },
        computed: {
            getIgv: function() {
                if ( this.igv_flag == 0 ) {
                    this.model.igv = 0;
                } else {
                    let igv = this.model.sale_value * (this.igv / 100);
                    this.model.igv = igv.toFixed(4);
                }
                return this.model.igv;
            },
            getTotal: function() {
                let total = Number(this.model.sale_value) - Number(this.model.inaccurate_value) + Number(this.model.igv);
                this.model.total = total.toFixed(4);
                return this.model.total;
            },
            getPerception: function() {
                if ( this.perception_flag == 0 ) {
                    this.perception_percentage = 0;
                } else {
                    let perception = Number(this.model.total) * (this.perception_percentage / 100);
                    this.model.perception = perception.toFixed(4);
                }
                return this.model.perception;
            },
			filterArticles() {
				if ( this.movement_class_id == 1 && ( this.movement_type_id == 1 || this.movement_type_id == 2 ) ) {
					this.articles.map( element => {
						element.full_name = element.code+' - '+element.name+' '+element.sale_unit_id+' x '+element.package_sale;
					});
				} else {
					this.articles.map( element => {
						element.full_name = element.code+' - '+element.name+' '+element.warehouse_unit_id+' x '+element.package_warehouse;
					});
				}
				return this.articles;
			}
        },
        created() {
            EventBus.$on('stock-glp-register-modal', function(articles, movement_class_id, movement_type_id) {
                this.articles = articles;
                this.movement_class_id = movement_class_id;
                this.movement_type_id = movement_type_id;
                $('#stock-glp-register-modal').modal('show');
            }.bind(this));

            EventBus.$on('stock-glp-register-modal_hide', function() {
                this.model.article_id = '';
                this.model.quantity = '';
                this.model.quantity_2 = '';
                this.model.quantity_3 = '';
                $('#article').val(null).trigger('change');
                $('#stock-glp-register-modal').modal('hide');
                // document.getElementById('add_article_2').disabled = false;
            }.bind(this));

            EventBus.$on('add_article_id', function(id) {
                this.article_list.push(id);
            }.bind(this));

            EventBus.$on('remove_article_id', function(id) {
                let index = this.article_list.findIndex((element) => element == id);
                this.article_list.splice(index, 1);
                console.log(index, id);
            }.bind(this));

            EventBus.$on('reset_stock_glp_register', function() {
                this.model.article_id = '';
                this.model.quantity = '';
                this.model.quantity_2 = '';
                this.model.quantity_3 = '';
                this.model.price = '';
                this.model.sale_value = '';
                this.model.inaccurate_value = 0;
                this.model.igv = 0;
                this.model.total = 0;
                this.model.perception = 0;
                this.articles = [];
                this.article_list = [];
                this.igv = '';
                this.perception_percentage = '';
                this.igv_flag = 0;
                this.perception_flag = 0;
              //  this.currency = {};
                this.movement_class_id = '';
                this.movement_type_id = '';
                this.stock_good = 0;
                this.stock_repair = 0;
                this.stock_return = 0;
                this.stock_damaged = 0;
                this.stock = 0;
            }.bind(this));
        },
        mounted() {
            this.newSelect2();

            $('#stock-glp-register-modal').on('hide.bs.modal', function(e) {
                this.model.article_id = '';
                this.model.quantity = '';
                this.model.quantity_2 = '';
                this.model.quantity_3 = '';
                $('#article').val(null).trigger('change');
                // document.getElementById('add_article_2').disabled = false;
            }.bind(this));
        },
        methods: {
            newSelect2: function() {
                let vm = this;

                $('#article').select2({
                    placeholder: "Selecciona un Artículo",
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.article_id = parseInt(selected_element.val());

                    var current_article = vm.articles.find(article => article.id === vm.model.article_id);

                    if ( vm.article_list.includes(current_article.id) ) {
                        Swal.fire({
                            title: '¡Error!',
                            text: 'Este artículo ya se encuentra en el registro.',
                            type: "error",
                            heightAuto: false,
                        });

                        vm.model.article_id = '';
                        $('#article').val(null).trigger('change');

                        document.getElementById('add_article_2').disabled = true;
                    } else {
                        document.getElementById('add_article_2').disabled = false;

                        vm.perception_flag = current_article.perception;
                        vm.stock_good = current_article.stock_good;
                        vm.stock_repair = current_article.stock_repair;
                        vm.stock_return = current_article.stock_return;
                        vm.stock_damaged = current_article.stock_damaged;

                        if ( vm.movement_class_id == 1 ) {
                            vm.stock = vm.stock_good;
                        } else if ( vm.movement_class_id == 2 ) {
                            if ( vm.movement_type_id == 15 ) {
                                vm.stock = vm.stock_return;
                            } else if ( vm.movement_type_id == 4 ) {
                                vm.stock = vm.stock_repair;
                            } else {
                                vm.stock = vm.stock_good;
                            }
                        }
                    }
                }).on('select2:unselect', function(e) {
                    vm.model.article_id = '';
                    vm.igv_flag = 0;
                    vm.perception_flag = 0;
                });
            },
            sendForm: function() {
                // axios.post(this.url_get_article, {
                //     model: model,
                //     igv_percentage: igv_percentage,
                //     perception_percentage: perception_percentage,
                //     currency: this.currency.id,
                //     item_number: this.article_list.length,
                // }).then(response => {
                //     console.log(response.data);
                //     this.article_list.push(response.data);
                //     this.datatable.destroy();
                //     this.fillTableX();
                //     EventBus.$emit('loading', false);
                //     EventBus.$emit('stock_register_modal_hide');
                //     EventBus.$emit('add_article_id', response.data.id);
                // }).catch(error => {
                //     console.log(error);
                //     console.log(error.response);
                // });

				document.getElementById('add_article_2').disabled = true;

                EventBus.$emit('sendForm', this.model);
            }
        }
    };
</script>