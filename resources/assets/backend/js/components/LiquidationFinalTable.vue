<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Artículos por Liquidar
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="saveLiquidation()">
								<i class="fa fa-check"></i> Cerrar liquidación
							</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable-articles"></div>
                <!--end: Datatable -->
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
			}
        },
        data() {
            return {
                liquidation_final_datatable: undefined,
                show_table: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.show_table = true;
                this.$store.commit('addModel', response);

                axios.post(this.url, {
                    model: this.$store.state.model,
                }).then(response => {
                    const data = response.data;

                    const movement_details = data.movement_details;
                    const clients = data.clients;

                    this.$store.commit('addClients', clients);
                    this.$store.commit('addArticles', movement_details)
                    
                    if ( this.liquidation_final_datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        this.liquidation_final_datatable.originalDataSet = this.articlesState;
                        this.liquidation_final_datatable.load();
                    }

                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            }.bind(this));

            EventBus.$on('refresh_table_liquidation', function() {
                if ( this.liquidation_final_datatable != undefined ) {
					// console.log(this.articlesState);
                    this.liquidation_final_datatable.originalDataSet = this.articlesState;
                    this.liquidation_final_datatable.load();
                }
            }.bind(this));
        },
        watch: {
            
        },
        computed: {
			articlesState: function() {
				let articles = this.$store.state.articles;
				articles.map(element => {
					element.presale_converted_amount = accounting.toFixed(element.presale_converted_amount, 2);
					element.sale_converted_amount = accounting.toFixed(element.sale_converted_amount, 2);
					element.return_converted_amount = accounting.toFixed(element.return_converted_amount, 2);
					element.new_balance_converted_amount = accounting.toFixed(element.new_balance_converted_amount, 2);
				});
				
				return articles;
			}
        },
        methods: {
			saveLiquidation: function() {
				// let zeroBalance = this.$store.state.articles.filter(element => element.new_balance_converted_amount > 0);
				let zeroBalance = 0;
                const salesRegister = this.$store.state.sales.length;
                if ( zeroBalance.length > 0 ) {
					Swal.fire({
						title: '¡Error!',
						text: 'Tiene Artículos pendientes por liquidar.',
						type: "error",
						heightAuto: false,
					});
				} else if ( !salesRegister ) {
					Swal.fire({
						title: '¡Error!',
						text: 'No ha registrado ninguna liquidacion.',
						type: "error",
						heightAuto: false,
					});
				} else {
					EventBus.$emit('loading', true);

					let unformatSales = JSON.parse(JSON.stringify(this.$store.state.sales));
					unformatSales.map(element => {
						if ( Array.isArray(element.details) && element.details.length ) {
							element.details.map(detailElement => {
								detailElement.igv_perception = accounting.unformat(detailElement.igv_perception);
								detailElement.perception = accounting.unformat(detailElement.perception);
								detailElement.price_igv = accounting.unformat(detailElement.price_igv);
								detailElement.quantity = accounting.unformat(detailElement.quantity);
								detailElement.sale_value = accounting.unformat(detailElement.sale_value);
								detailElement.total_perception = accounting.unformat(detailElement.total_perception);
							});
						}
						if ( Array.isArray(element.liquidations) && element.liquidations.length ) {
							element.liquidations.map(liquidationElement => {
								liquidationElement.amount = accounting.unformat(liquidationElement.amount);
							});
						}
						element.perception = accounting.unformat(element.perception);
						element.perception_percentage = accounting.unformat(element.perception_percentage);
						element.total = accounting.unformat(element.total);
						element.total_perception = accounting.unformat(element.total_perception);
					});

                    const boletas = [
                        ...unformatSales
                    ];

                    const correlatives = {};

                    unformatSales.map((item) => {
                        const {
                            warehouse_document_type_id,
                            details,
                            correlative,
                            serie_num,
                            sale_serie_id,
                            liquidations
                        } = item;

                        const bolLiquidations = JSON.parse(JSON.stringify(liquidations));

                        item.if_bol = 0;
                        if (!correlatives[serie_num]) {
                            const sale_serie_index = this.$store.state.sale_series.findIndex(item => item.id == sale_serie_id);

                            correlatives[serie_num] = this.$store.state.sale_series[sale_serie_index].correlative - 1;
                        }

                        if (warehouse_document_type_id == 7) {
                            item.warehouse_document_type_id = 31;
                            item.if_bol = 1;

                            let serie = 'RBV';

                            serie_num.split('').map(e => {
                                if(!isNaN(e)) serie = serie + e;
                            })

                            item.serie_num = serie + `-${correlative}`
                            details.map((i) => {
                                const {
                                    quantity,
                                    sale_value,
                                    total_perception,
                                    price_igv
                                } = i;

                                const rest = quantity % 2;

                                const div = (quantity / 2);

                                const price = (sale_value / quantity).toFixed(4);

                                for (let e = 1; e <= div; e++) {
                                    const newLiquidations = [];
                                    let amount = price * 2;

                                    bolLiquidations.map(l => {
                                        if ((amount > 0) && (l.amount > 0)) {
                                            if (l.amount > amount) {
                                                l.amount = l.amount - amount;

                                                newLiquidations.push({
                                                    ...l,
                                                    amount
                                                });

                                                amount = 0;
                                            } else if (l.amount <= amount) {
                                                newLiquidations.push({
                                                    ...l,
                                                });

                                                amount = amount - l.amount;
                                                l.amount = 0;
                                            };
                                        }
                                    })

                                    const element = {
                                        ...item,
                                        warehouse_document_type_id: 7,
                                        correlative: correlatives[serie_num],
                                        serie_num,
                                        total: price * 2,
                                        total_perception: price * 2,
                                        if_bol: 0,
                                        details: [
                                            {
                                                ...i,
                                                sale_value: price * 2,
                                                total_perception: price * 2,
                                                quantity: 2
                                            }
                                        ],
                                        liquidations: newLiquidations
                                    };

                                    item.correlative = correlatives[serie_num];

                                    correlatives[serie_num] = correlatives[serie_num] + 1;

                                    boletas.push(element);
                                };

                                if (rest) {
                                    const newLiquidations = [];
                                    let amount = price;

                                    bolLiquidations.map(l => {
                                        if ((amount > 0) && (l.amount > 0)) {
                                            if (l.amount > amount) {
                                                l.amount = l.amount - amount;

                                                newLiquidations.push({
                                                    ...l,
                                                    amount
                                                });

                                                amount = 0;
                                            } else if (l.amount <= amount) {
                                                newLiquidations.push({
                                                    ...l,
                                                });

                                                amount = amount - l.amount;
                                                l.amount = 0;
                                            };
                                        }
                                    })

                                    const element = {
                                        ...item,
                                        warehouse_document_type_id: 7,
                                        correlative: correlatives[serie_num],
                                        serie_num,
                                        total: price,
                                        total_perception: price,
                                        if_bol: 0,
                                        details: [
                                            {
                                                ...i,
                                                sale_value: price,
                                                total_perception: price,
                                                quantity: 1
                                            }
                                        ],
                                        liquidations: newLiquidations
                                    };

                                    item.correlative = correlatives[serie_num];

                                    correlatives[serie_num] = correlatives[serie_num] + 1;

                                    boletas.push(element);
                                };
                            })
                        };
                    });

                    // console.log(boletas)

                    EventBus.$emit('loading', false);

					axios.post(this.url_store, {
						'model': this.$store.state.model,
						'sales': boletas
					}).then(response => {
						// console.log(response);
						this.$store.commit('resetState');

						EventBus.$emit('loading', false);
						EventBus.$emit('clear_form_sale');
						EventBus.$emit('refresh_table_sale');
						EventBus.$emit('refresh_table_liquidation');

						Swal.fire({
							title: '¡Ok!',
							text: 'Se creo el registro correctamente.',
							type: "success",
							heightAuto: false,
						});
					}).catch(error => {
						EventBus.$emit('loading', false);
						console.log(error);
						console.log(error.response);

						Swal.fire({
							title: '¡Error!',
							text: error,
							type: "error",
							heightAuto: false,
						});
					});
				}
			},
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.liquidation_final_datatable = $('.kt-datatable-articles').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.articlesState,
                        pageSize: 10,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
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
                        autoHide: true,
                    },

                    // columns definition
				    // Esta podria ser solo una solucion de vista ya que en la tabla de warehouse_movement_detail en la columna new_stock_return la informacion que se muestra es la que trae cada article en su propiedad return_converted_amount antes de ser convertido si al guardar la informacion es correcta entonces al pedir la informacion se devuelve erroneamente
                    columns: [
                        {
                            field: 'article_code',
                            title: 'Código',
                            width: 60,
                            textAlign: 'center',
                        },
                        {
                            field: 'article_name',
                            title: 'Artículo',
                            width: 300,
                        },
                        {
                            field: 'presale_converted_amount',
                            title: 'Pre-Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'return_converted_amount',
                            title: 'Retorno',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'cesion',
                            title: 'Cesión de Uso',
                            width: 120,
                            textAlign: 'right',
                        },
                        // {
                        //     field: 'new_balance_converted_amount',
                        //     title: 'Retorno',
                        //     width: 120,
                        //     textAlign: 'right',
                        // },
                        {
                            field: 'sale_converted_amount',
                            title: 'Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'new_balance_converted_amount',
                            title: 'Saldo',
                            width: 120,
                            textAlign: 'right',
                        },
                        // {
                        //     field: 'return_converted_amount',
                        //     title: 'Saldo',
                        //     width: 120,
                        //     textAlign: 'right',
                        // },
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
                        // {
                        //     field: 'options',
                        //     title: 'Opciones',
                        //     sortable: false,
                        //     width: 80,
                        //     overflow: 'visible',
                        //     autoHide: false,
                        //     textAlign: 'right',
                        //     template: function(row) {
                        //         let actions = '<div class="actions">';
                        //             actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                        //                 actions += '<i class="la la-edit"></i>';
                        //             actions += '</a>';
                        //             actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                        //                 actions += '<i class="la la-trash"></i>';
                        //             actions += '</a>';
                        //         actions += '</div>';

                        //         return actions;
                        //     },
                        // },
                    ]
                });

                this.liquidation_final_datatable.columns('id').visible(false);
            },
        }
    };
</script>