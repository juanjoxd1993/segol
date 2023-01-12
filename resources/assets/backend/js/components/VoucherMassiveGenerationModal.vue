<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Generación masiva de Comprobantes</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
								<input type="hidden" name="article_convertion" id="article_convertion" v-model="model.article_convertion">
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Tipo de Documento:</label>
										<select class="form-control" name="voucher_type_id" id="voucher_type_id" v-model="model.voucher_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="voucher_type in voucher_types" :value="voucher_type.id" v-bind:key="voucher_type.id">{{ voucher_type.name }}</option>
										</select>
										<div id="voucher_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Serie:</label>
                                        <input type="number" class="form-control" name="serie_number" id="serie_number" placeholder="0" step="any" min="1" v-model="model.serie_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="serie_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
									<div class="form-group">
                                        <label class="form-control-label">Cliente:</label>
                                        <select class="form-control kt-select2" name="client_id" id="client_id" v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                        </select>
                                        <div id="client_id-error" class="error invalid-feedback"></div>
                                    </div>
								</div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Kg (max. {{ formated_sum_kg }}):</label>
                                        <input type="number" class="form-control" name="kg" id="kg" placeholder="0.00" step="any" min="1" v-model="model.kg" @focus="$parent.clearErrorMsg($event)">
                                        <div id="kg-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
								<div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Precio:</label>
                                        <input type="number" class="form-control" name="price" id="price" placeholder="0.00" step="any" min="0.01" v-model="model.price" @focus="$parent.clearErrorMsg($event)">
                                        <div id="price-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
									<div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control kt-select2" name="article_id" id="article_id" v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
                                    </div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Fecha:</label>
										<datetime
											v-model="model.issue_date"
											placeholder="Selecciona una Fecha"
											:format="'dd-LL-yyyy'"
											input-id="issue_date"
											name="issue_date"
											value-zone="America/Lima"
											zone="America/Lima"
											class="form-control"
											:min-datetime="min_datetime"
											:max-datetime="today"
											@focus="$parent.clearErrorMsg($event)">
										</datetime>
										<div id="issue_date-error" class="error invalid-feedback"></div>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            Generar
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
			voucher_types: {
				type: Array,
				default: ''
			},
			min_datetime: {
				type: String,
				default: ''
			},
			today: {
				type: String,
				default: ''
			},
            url: {
                type: String,
                default: ''
            },
			url_get_clients: {
                type: String,
                default: ''
            },
			url_get_articles: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    voucher_type_id: '',
                    serie_number: '',
                    client_id: '',
                    kg: '',
                    price: '',
                    article_id: '',
					article_convertion: '',
                    issue_date: '',
                },
				form_model: {},
				sum_kg: '',
				sum_total: '',
                button_text: '',
            }
        },
        watch: {
            'model.voucher_type_id': function (val) {
				this.model.client_id = '';
				$('#client_id').val(null).trigger('change');
			}
        },
        computed: {
            formated_sum_kg: function() {
				return accounting.formatNumber(this.sum_kg, 2);
			}
        },
        created() {
            EventBus.$on('open_modal', function(form_model, sum_kg, sum_total) {
                EventBus.$emit('loading', true);

                this.model = {
                    voucher_type_id: '',
                    serie_number: '',
                    client_id: '',
                    kg: '',
                    price: '',
                    article_id: '',
					article_convertion: '',
                    issue_date: '',
                };
				this.form_model = form_model;
				this.sum_kg = sum_kg;
				this.sum_total = sum_total;

                $('#client_id').val(null).trigger('change');
                $('#article_id').val(null).trigger('change');
                $('#modal').modal('show');
                EventBus.$emit('loading', false);
            }.bind(this));
        },
        mounted() {
            this.newSelect2();

			$('#modal').on('hide.bs.modal', function(e) {
                this.model = {
					voucher_type_id: '',
                    serie_number: '',
                    client_id: '',
                    kg: '',
                    price: '',
                    article_id: '',
					article_convertion: '',
                    issue_date: '',
				};
				this.form_model = {};
				this.sum_kg = '';
				this.sum_total = '';
            }.bind(this));
        },
        methods: {
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
								company_id: vm.form_model.company_id,
                                voucher_type_id: vm.model.voucher_type_id,
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
                    vm.model.client_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.client_id = '';
                });

                $("#article_id").select2({
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
                        url: this.url_get_articles,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
								business_unit_id: vm.form_model.business_unit_id,
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
                    vm.model.article_id = parseInt(selected_element.val());
                    vm.model.article_convertion = accounting.unformat(e.params.data.convertion);
                }).on('select2:unselect', function(e) {
                    vm.model.article_id = '';
                    vm.model.article_convertion = '';
                });
            },
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);
				Object.keys(vm.form_model).forEach(element => fd.append(element, vm.form_model[element]));

				if ( vm.model.voucher_type_id == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe seleccionar un Tipo de Documento.'
					});
				} else if ( vm.model.serie_number == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe digitar un Número de Serie.'
					});
				} else if ( vm.model.client_id == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe seleccionar un Cliente.'
					});
				} else if ( vm.model.metric_tons == 0 ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Los Kilos deben ser mayor a 0.'
					});
				} else if ( vm.model.metric_tons > vm.sum_kg ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Los Kilos digitados no pueden superar el total de Kg del periodo seleccionado.'
					});
				} else if ( vm.model.price == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe digitar un Precio.'
					});
				} else if ( vm.model.price == 0 ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'El Precio debe ser mayor a 0.'
					});
				} else if ( vm.model.article_id == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe seleccionar un Artículo.'
					});
				} else if ( vm.model.issue_date == '' ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Debe seleccionar una Fecha.'
					});
				} else if ( accounting.unformat(vm.model.metric_tons) % vm.model.article_convertion != 0 ) {
					vm.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'La presentación del Artículo no es múltiplo de los Kg digitados.'
					});
				} else {
					EventBus.$emit('loading', true);

					axios.post(url, fd, { headers: {
							'Content-type': 'application/x-www-form-urlencoded',
						}
					}).then(response => {
						// console.log(response.data);
						EventBus.$emit('loading', false);
						$('#modal').modal('hide');
						this.$parent.alertMsg(response.data);

						this.model = {
							voucher_type_id: '',
							serie_number: '',
							client_id: '',
							kg: '',
							price: '',
							article_id: '',
							article_convertion: '',
							issue_date: '',
						};
						this.form_model = {};
						this.sum_kg = '';
						this.sum_total = '';

						EventBus.$emit('clear_form');
						EventBus.$emit('clear_table');

					}).catch(error => {
						EventBus.$emit('loading', false);
						console.log(error.response);
						var obj = error.response.data.errors;
						$('.modal').animate({
							scrollTop: 0
						}, 500, 'swing');
						$.each(obj, function(i, item) {
							let c_target = target.find("#" + i + "-error");
							if ( !c_target.attr('data-required') ) {
								let p = c_target.parent().find('#' + i);
								p.addClass('is-invalid');
							} else {
								c_target.css('display', 'block');
							}
							c_target.html(item);
						});
					});
				}
            },
        }
    };
</script>