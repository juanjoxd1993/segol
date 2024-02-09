<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} artículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Código de Artículo</label>
                                        <input type="text" class="form-control" name="code" id="code" placeholder="123" v-model="model.code" @focus="$parent.clearErrorMsg($event)">
                                        <div id="code-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Descripción</label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Nombre del Artículo" v-model="model.name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="name-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Familia / Marca:</label>
                                        <select class="form-control kt-select2" name="family_id" id="family_id" v-model="model.family_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="family in families" :value="family.id" v-bind:key="family.id">{{ family.name }}</option>
                                        </select>
                                        <div id="family_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Grupo:</label>
                                        <select class="form-control kt-select2" name="group_id" id="group_id" v-model="model.group_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="group in groups" :value="group.id" v-bind:key="group.id">{{ group.name }}</option>
                                        </select>
                                        <div id="group_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Sub-Grupo:</label>
                                        <select class="form-control kt-select2" name="subgroup_id" id="subgroup_id" v-model="model.subgroup_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="subgroup in subgroups" :value="subgroup.id" v-bind:key="subgroup.id">{{ subgroup.name }}</option>
                                        </select>
                                        <div id="subgroup_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Unidad de Medida de Compra:</label>
                                        <select class="form-control kt-select2" name="sale_unit_id" id="sale_unit_id" v-model="model.sale_unit_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="sale_unit in units" :value="sale_unit.id" v-bind:key="sale_unit.id">{{ sale_unit.name }}</option>
                                        </select>
                                        <div id="sale_unit_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Empaque de Compra:</label>
                                        <input type="tel" class="form-control" name="package_sale" id="package_sale" placeholder="0" v-model="model.package_sale" @focus="$parent.clearErrorMsg($event)">
                                        <div id="package_sale-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Operación de Conversión:</label>
                                        <select class="form-control kt-select2" name="operation_type_id" id="operation_type_id" v-model="model.operation_type_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="operation in operations" :value="operation.id" v-bind:key="operation.id">{{ operation.name }}</option>
                                        </select>
                                        <div id="operation_type_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Factor:</label>
                                        <input type="tel" class="form-control" name="factor" id="factor" placeholder="0" v-model="model.factor" @focus="$parent.clearErrorMsg($event)">
                                        <div id="factor-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Unidad de Medida de Almacén:</label>
                                        <select class="form-control kt-select2" name="warehouse_unit_id" id="warehouse_unit_id" v-model="model.warehouse_unit_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="warehouse_unit in units" :value="warehouse_unit.id" v-bind:key="warehouse_unit.id">{{ warehouse_unit.name }}</option>
                                        </select>
                                        <div id="warehouse_unit_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Empaque de Almacén:</label>
                                        <input type="tel" class="form-control" name="package_warehouse" id="package_warehouse" placeholder="0" v-model="model.package_warehouse" @focus="$parent.clearErrorMsg($event)">
                                        <div id="package_warehouse-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Afecto a IGV:</label>
                                        <select class="form-control kt-select2" name="igv" id="igv" v-model="model.igv" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                        <div id="igv-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Afecto a Percepción:</label>
                                        <select class="form-control kt-select2" name="perception" id="perception" v-model="model.perception" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                        <div id="perception-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Stock mínimo:</label>
                                        <input type="tel" class="form-control" name="stock_minimum" id="stock_minimum" placeholder="0" v-model="model.stock_minimum" @focus="$parent.clearErrorMsg($event)">
                                        <div id="stock_minimum-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Ubicación:</label>
                                        <input type="tel" class="form-control" name="ubication" id="ubication" placeholder="Almacén 1" v-model="model.ubication" @focus="$parent.clearErrorMsg($event)">
                                        <div id="ubication-error" class="error invalid-feedback"></div>
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
            families: {
                type: Array,
                default: ''
            },
            groups: {
                type: Array,
                default: ''
            },
            subgroups: {
                type: Array,
                default: ''
            },
            operations: {
                type: Array,
                default: ''
            },
            units: {
                type: Array,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    warehouse_type_id: '',
                    code: '',
                    name: '',
                    family_id: '',
                    group_id: '',
                    subgroup_id: '',
                    sale_unit_id: '',
                    package_sale: '',
                    operation_type_id: '',
                    factor: '',
                    warehouse_unit_id: '',
                    package_warehouse: '',
                    igv: '',
                    perception: '',
                    stock_minimum: '',
                    ubication: '',
                },
                button_text: ''
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(warehouse_type_id) {
                this.model.warehouse_type_id = warehouse_type_id;
                this.button_text = 'Crear';

                this.model.code = '';
                this.model.name = '';
                this.model.family_id = '';
                this.model.group_id = '';
                this.model.subgroup_id = '';
                this.model.sale_unit_id = '';
                this.model.package_sale = '';
                this.model.operation_type_id = '';
                this.model.factor = '';
                this.model.warehouse_unit_id = '';
                this.model.package_warehouse = '';
                this.model.igv = '';
                this.model.perception = '';
                this.model.stock_minimum = '';
                this.model.ubication = '';

                $('#modal').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
                this.button_text = 'Actualizar';
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

                EventBus.$emit('loading', true);

                // EventBus.$emit('loading', true);
                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    EventBus.$emit('loading', false);
                    $('#modal').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.warehouse_type_id = '';
                    this.model.id = '';
                    this.model.code = '';
                    this.model.name = '';
                    this.model.family_id = '';
                    this.model.group_id = '';
                    this.model.subgroup_id = '';
                    this.model.sale_unit_id = '';
                    this.model.package_sale = '';
                    this.model.operation_type_id = '';
                    this.model.factor = '';
                    this.model.warehouse_unit_id = '';
                    this.model.package_warehouse = '';
                    this.model.igv = '';
                    this.model.perception = '';
                    this.model.stock_minimum = '';
                    this.model.ubication = '';

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