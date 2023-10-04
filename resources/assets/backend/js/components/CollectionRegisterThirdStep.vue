<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Detalle
                    </h3>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
                <!--begin: Datatable -->
                <div class="kt-datatable-collection-register"></div>
                <!--end: Datatable -->

				<div class="row row-no-padding row-col-separator-xl">
					<div class="col-md-12 col-lg-12 col-xl-4">
						<div class="kt-widget1">
							<div class="kt-widget1__item">
								<div class="kt-widget1__info">
									<div class="kt-widget1__title">Total Cobrado</div>
									<span class="kt-widget1__desc"></span>
								</div>
								<span class="kt-widget1__number kt-font-brand">
									{{ totalCobrado }}
								</span>
							</div>
						</div>
					</div>
					<div class="col-md-12 col-lg-12 col-xl-4">
						<div class="kt-widget1">
							<div class="kt-widget1__item">
								<div class="kt-widget1__info">
									<div class="kt-widget1__title">Total Registrado</div>
									<span class="kt-widget1__desc"></span>
								</div>
								<span :class="'kt-widget1__number' + total_paid_class">
									{{ totalRegistrado }}
								</span>
							</div>
						</div>
					</div>
					<div class="col-md-12 col-lg-12 col-xl-4">
						<div class="kt-widget1">
							<div class="kt-widget1__item">
								<div class="kt-widget1__info">
									<div class="kt-widget1__title">Cobranza por asignar</div>
									<span class="kt-widget1__desc"></span>
								</div>
								<span :class="'kt-widget1__number ' + to_be_assigned_class">
									{{ porAsignar }}
								</span>
							</div>
						</div>
					</div>
				</div>
            </div>

			<div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button class="btn btn-primary" @click.prevent="saveCollectionRegister()">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Portlet-->
    </div>
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
			url_store: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                show_table: false,
				model: {},
                collection_register_datatable: undefined,
				items: [],
				total_paid: 0,
				total_paid_class: ' kt-font-success',
				to_be_assigned: 0,
				to_be_assigned_class: ' kt-font-success',
				ids: [],
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('secondStateUpdate', function(response) {
				let vm = this;
                vm.show_table = true;
				vm.model = response;

				axios.post(this.url, vm.model).then(response => {
					vm.items = response.data;
					vm.items.map( element => {
						element.paid = accounting.toFixed(0, 2);
					});

                    const newArr = [];

                    vm.items.map(item => {
                        if (item.warehouse_document_type_id != 7) {
                            const obj = {
                                ...item,
                                warehouse_document_type_name: "Boleta de Venta Electronica"
                            };

                            newArr.push(obj);
                        }
                    })

                    vm.items = newArr;

					if ( vm.collection_register_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.collection_register_datatable.originalDataSet = vm.items;
						vm.collection_register_datatable.load();
					}

					vm.collection_register_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				}).catch(error => {
					console.log(error);
					console.log(error.response);
					EventBus.$emit('loading', false);
				})
            }.bind(this));

            EventBus.$on('update_table', function(sale) {
				let vm = this;
				let index = vm.items.findIndex( element => element.id == sale.id );
				sale.paid = accounting.toFixed(sale.paid, 2);
				vm.items[index] = sale;

				vm.ids = [];
				vm.total_paid = 0;
				vm.collection_register_datatable.originalDataSet = vm.items;
				vm.collection_register_datatable.load();
				EventBus.$emit('loading', false);
            }.bind(this));
        },
        watch: {
            items: function (val) {
				if ( val.length > 0 ) {
					this.items.map( (element, index) => {
						element.item = ++index;
						element.paid = accounting.toFixed(element.paid, 2);
					});

					this.collection_register_datatable.originalDataSet = this.items;
					this.collection_register_datatable.load();
					EventBus.$emit('loading', false);
				}
			},
        },
        computed: {
			totalCobrado: function() {
				return accounting.formatMoney(this.model.amount, 'S/ ',2, ',', '.');
			},
			totalRegistrado: function() {
				this.total_paid_class = this.total_paid <= this.model.amount ? ' kt-font-success' : ' kt-font-danger';

				return accounting.formatMoney(this.total_paid, 'S/ ', 2, ',', '.');
			},
			porAsignar: function() {
				this.to_be_assigned = this.model.amount - this.total_paid;
				this.to_be_assigned_class = this.to_be_assigned >= 0 ? ' kt-font-success' : ' kt-font-danger';

				return accounting.formatMoney(this.to_be_assigned, 'S/ ',2, ',', '.');
			},
        },
        methods: {
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.collection_register_datatable = $('.kt-datatable-collection-register').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.items,
                        pageSize: 10,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: false,
                    pagination: false,

                    search: {
                        input: $('#generalSearch'),
                    },

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
                            title: '',
                            width: 40,
							selector: { class: 'kt-checkbox--solid' },
                            textAlign: 'center',
                        }, {
                            field: 'item',
                            title: '#',
                            width: 40,
                            textAlign: 'center',
                        }, {
                            field: 'warehouse_document_type_name',
                            title: 'Tipo',
                            width: 120,
                            textAlign: 'center',
                        }, {
                            field: 'referral_serie_number',
                            title: 'Serie Documento',
                            width: 80,
                        }, {
                            field: 'referral_voucher_number',
                            title: 'Nº de Documento',
                            width: 80,
                        }, {
                            field: 'total_perception',
                            title: 'Total',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'balance',
                            title: 'Saldo',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'paid',
                            title: 'Monto',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'sale_date',
                            title: 'Fecha Emisión',
                            width: 100,
                            textAlign: 'center',
                        }, {
                            field: 'expiry_date',
                            title: 'Fecha Vencimiento',
                            width: 100,
                            textAlign: 'center',
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
                                    // actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                    //     actions += '<i class="la la-trash"></i>';
                                    // actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        },
                    ]
                });

                // this.collection_register_datatable.columns('id').visible(false);

				$('.kt-datatable').on('kt-datatable--on-check', function(a, e) {
					if ( e.length > 1 ) {
						vm.ids = [];
						vm.total_paid = 0;
					}

                    $.each(e, function(key, value) {
                        let item = vm.items.find((element) => element.id == value);
                        let index_item = vm.items.findIndex((element) => element.id == value);
                        let index = vm.ids.findIndex((element) => element == value);

                        if (vm.items[index_item].paid <= 0) {
                            const rest = vm.model.amount - item.balance;
                            if (rest < 0) {
                                vm.items[index_item].paid = accounting.toFixed(vm.model.amount, 2);
                            } else {
                                vm.items[index_item].paid = accounting.toFixed(item.balance, 2);
                            };
                        };

                        vm.collection_register_datatable.originalDataSet = vm.items;

                        if ( index < 0 ) {
                            vm.ids.push(parseInt(value));
                        };

						let paid = accounting.unformat(vm.items.find(element => element.id == value).paid);
						vm.total_paid += paid;
                    });
                }).on('kt-datatable--on-uncheck', function(a, e) {
                    $.each(e, function(key, value) {
                        let index = vm.ids.findIndex((element) => element == value);
                        if ( index >= 0 ) {
                            vm.ids.splice(index, 1);
                        }

						if ( e.length > 1 ) {
							vm.total_paid = 0;
						} else {
							let paid = accounting.unformat(vm.items.find(element => element.id == value).paid);
							vm.total_paid -= paid;
						}
                    });
                });
            },
			manageActions: function(event) {
                if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] input').val();
                    EventBus.$emit('loading', true);

					let sale = this.items.find( element => element.id == id);
					EventBus.$emit('edit_modal', sale);
                }
            },
			saveCollectionRegister: function() {
                console.log(this.model)
				if ( this.total_paid == 0 ) {
					this.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'El Total Registrado no puede ser 0.'
					});
				}
                // else if ( this.model.amount > this.total_paid ) {
				// 	this.$parent.alertMsg({
				// 		type: 5,
				// 		title: 'Error',
				// 		msg: 'El Total Registrado no puede exceder el Total Cobrado.'
				// 	});
                // } 
             //   else if ( this.model.payment_method_id > 3 && this.to_be_assigned > 0 ) {
			//		this.$parent.alertMsg({
			//			type: 5,
			//			title: 'Error',
			//			msg: 'El Total del Detalle es menor al Total por aplicar o canjear.'
			//		}); 
            // SE USA PARA EVALUAR SI EL TOTAL EXCEDE LO COBRADO
				 else {
					EventBus.$emit('loading', true);
					let filteredItems = this.items.filter(element => this.ids.includes(element.id));
					// console.log(filteredItems);

					axios.post(this.url_store, {
						model: this.model,
						total_paid: this.total_paid,
						to_be_assigned: this.to_be_assigned,
						items: filteredItems
					}).then( response => {
						// console.log(response.data);
						EventBus.$emit('clearFirstStep');
						EventBus.$emit('clearSecondStep');
						this.model = {};
						this.total_paid = 0;
						this.to_be_assigned = 0;
						this.collection_register_datatable.destroy();
						this.collection_register_datatable = undefined;
						this.items = [];
						this.ids = [];
						this.show_table = false;

						this.$parent.alertMsg({
							type: response.data.type,
							title: response.data.title,
							msg: response.data.msg,
						});

						EventBus.$emit('loading', false);
					}).catch( error => {
						console.log(error);
						console.log(error.response);
						EventBus.$emit('loading', false);
					});
				}

				// EventBus.$emit('loading', true);

				// axios.post(this.url, {
				// 	model: this.model,
				// 	items: this.items
				// }).then(response => {
				// 	let vm = this;

				// 	EventBus.$emit('loading', false);
				// 	this.$parent.alertMsg({
				// 		type: 1,
				// 		title: response.data.title,
				// 		msg: response.data.msg
				// 	});

				// 	EventBus.$emit('clearFirstStep');
				// 	EventBus.$emit('clearSecondStep');
				// 	this.model = {};
				// 	this.collection_register_datatable.destroy();
				// 	this.collection_register_datatable = undefined;
				// 	this.items = [];
				// 	this.show_table = false;
				// }).catch(error => {
				// 	EventBus.$emit('loading', false);
				// 	console.log(error.response);
				// });
			}
        }
    };
</script>