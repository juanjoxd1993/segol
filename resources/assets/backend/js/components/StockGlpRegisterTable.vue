<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table == 1">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Artículos
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-actions">
                    <a href="#" @click.prevent="openModal()" class="btn btn-success">
                        <i class="fa fa-plus"></i> Agregar artículo
                    </a>
                </div>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-12" @click="manageActions">
                        <!--begin: Datatable -->
                        <div class="kt-datatable"></div>
                        <!--end: Datatable -->
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Portlet-->
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            igv: {
                type: Object,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
            url_get_article: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                show_table: 0,
                datatable: undefined,
                article_list: [],
                model: '',
                perception_percentage: '',
            //    currency: '',
                articles: [],
                item_number: 0,
            }
        },
        created() {
            EventBus.$on('show_table', function(data) {
                // console.log(data);
                this.show_table = 1;
                this.model = data.model;
                this.perception_percentage = data.perception_percentage;
            //    this.currency = data.currency;
                this.articles = data.articles;

                this.$nextTick(function() {
                    this.fillTableX();
                });
            }.bind(this));

            EventBus.$on('sendForm', function(model) {
                EventBus.$emit('loading', true);
                this.addArticle(model, this.igv.value, this.perception_percentage);
            }.bind(this));

            EventBus.$on('reset_stock_glp_register', function() {
                this.show_table = 0;
                this.datatable = undefined;
                this.article_list = [];
                this.model = '';
                this.perception_percentage = '';
            //    this.currency = '';
                this.articles = [];
                this.item_number = 0;
            }.bind(this));
        },
        mounted() {

        },
        watch: {
            
        },
        computed: {
            
        },
        methods: {
            openModal: function() {
                EventBus.$emit('stock-glp-register-modal', this.articles, this.igv, this.perception_percentage, this.currency, this.model.movement_class_id, this.model.movement_type_id);
            },
            addArticle: function(model, igv_percentage, perception_percentage) {
                axios.post(this.url_get_article, {
                    model: model,
                    igv_percentage: igv_percentage,
                    perception_percentage: perception_percentage,
                 //   currency: this.currency.id,
                    item_number: this.article_list.length,
					movement_type_id: this.model.movement_type_id,
                }).then(response => {
                    $("#stock-glp-register-modal").modal('hide')
                    
                    this.article_list.push(response.data);
                    this.datatable.destroy();
                    this.fillTableX();
                    EventBus.$emit('loading', false);

                    EventBus.$emit('stock_glp_register_modal_hide');
                    EventBus.$emit('add_article_id', response.data.id);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
            fillTableX: function() {
                let vm = this;
                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.article_list,
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
                        // {
                        //     field: 'voucher_id',
                        //     title: '#',
                        //     sortable: false,
                        //     width: 0,
                        //     selector: {class: 'kt-checkbox--solid'},
                        //     textAlign: 'center',
                        //     responsive: {
                        //         hidden: 'sm',
                        //         hidden: 'md',
                        //         hidden: 'lg',
                        //         hidden: 'xl'
                        //     }
                        // },
                        {
                            field: 'item_number',
                            title: '#',
                            width: 20,
                            textAlign: 'center',
                        },
                        {
                            field: "code",
                            title: "Código",
                            width: 50,
                            textAlign: 'center',
                        },
                        {
                            field: "name",
                            title: "Descripción",
                            width: 120,
                        },
                        {
                            field: "sale_unit_id",
                            title: "Unidad de Medida",
                            width: 100,
                            textAlign: 'center',
                        },
                        {
                            field: "package_sale",
                            title: "# de Empaque",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "digit_amount",
                            title: "Cantidad digitada",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: 'converted_amount',
                            title: 'Cantidad convertida',
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: 'old_stock_return',
                            title: 'Peso Neto',
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: 'old_stock_damaged',
                            title: 'Peso Bruto',
                            width: 80,
                            textAlign: 'right',
                        },
                    
                    
                      
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
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 120,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'center',
                            template: function() {
                                return '\
                                <div class="actions">\
                                    <a href="#" class="delete btn btn-danger btn-sm btn-icon btn-icon-md" title="Eliminar">\
                                        <i class="la la-trash"></i>\
                                    </a>\
                                </div>\
                            ';
                            },
                        },
                    ]
                });

                this.datatable.columns('id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let item_number = $(event.target).parents('tr').find('td[data-field="item_number"] span').html();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    let index = this.article_list.findIndex((element) => element.item_number == item_number);

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el artículo?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        if ( result.value ) {
                            Swal.fire({
                                title: 'Ok!',
                                text: 'Se ha eliminado el artículo',
                                type: "success",
                                heightAuto: false,
                            });

                            this.article_list.splice(index, 1);
                            let new_item_number = 0;
                            this.article_list.map(function(item, index) {
                                item.item_number = ++new_item_number;
                            });
                            this.datatable.destroy();
                            this.fillTableX();

                            EventBus.$emit('remove_article_id', id);
                        }
                    });
                }
            },
            formController: function(url, event) {
                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                this.article_list.map(function(article) {
                    const regex = /,/gi;
                    article.digit_amount = accounting.unformat(article.digit_amount).toFixed(4);
                    article.converted_amount = accounting.unformat(article.converted_amount).toFixed(4);
                    article.old_stock_return = accounting.unformat(article.old_stock_return).toFixed(4);
                    article.old_stock_damaged = accounting.unformat(article.old_stock_damaged).toFixed(4);
                    article.price = accounting.unformat(article.price).toFixed(4);
                    article.sale_value = accounting.unformat(article.sale_value).toFixed(4);
                    article.inaccurate_value = accounting.unformat(article.inaccurate_value).toFixed(4);
                    article.igv = accounting.unformat(article.igv).toFixed(4);
                    article.total = accounting.unformat(article.total).toFixed(4);
                    article.perception = accounting.unformat(article.perception).toFixed(4);
                });

                if ( this.article_list != '' && this.article_list != [] ) {
                    EventBus.$emit('loading', true);
                    axios.post(this.url, {
                        model: this.model,
                        article_list: this.article_list,
                    }).then(response => {
                        EventBus.$emit('loading', false);
                        console.log(response);
                        
                        this.model.movement_class_id = '';
                        this.model.warehouse_type_id = '';
                        this.model.company_id = '';
                     //   this.model.currency = 1;
                        this.model.since_date = this.current_date;
                        this.model.warehouse_account_id = '';
                        this.model.referral_guide_series = '';
                        this.model.referral_guide_number = '';
                        this.model.warehouse_document_type_id = '';
                        this.model.referral_serie_number = '';
                        this.model.referral_voucher_number = '';
                        this.model.scop_number = '';
                        this.model.license_plate = '';
                        this.model.license_plate_2 = '';
                        this.model.mezcla = '';
                        this.model.isla = '';
                        this.model.price_mes = '';
                        this.article_list = [];
                        $('#warehouse_account_id').val(null).trigger('change');
                        this.datatable.destroy();
                        EventBus.$emit('reset_stock_glp_register');

                        Swal.fire({
                            title: '¡Bien!',
                            text: 'Movimiento creado correctamente',
                            type: "success",
                            heightAuto: false,
                        });

                        this.$nextTick(function() {
                            EventBus.$emit('reset_stock_glp_register');

                            this.show_table = 0;
                            this.datatable = undefined;
                            this.article_list = [];
                            //this.model = '';
                            this.perception_percentage = '';
                         //  this.currency = '';
                            this.articles = [];
                            this.item_number = 0;
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
                } else {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debes agregar al menos 1 Artículo.',
                        type: "error",
                        heightAuto: false,
                    });
                }
            },
        }
    };
</script>