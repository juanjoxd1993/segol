<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Buscar
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipos:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id" v-model="warehouse_type_index" @focus="$parent.clearErrorMsg($event)">
                                <option selected="true" value="0">Todos</option>
                                <option value="1">Cisternas</option>
                                <option value="2">Provedores</option>
                                <option value="3">Planta</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-lg-3">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >Buscar</button>
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
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                warehouse_type_index: null,
                array_warehouse_types_ids: [
                    [2, 3, 4],
                    [4],
                    [2],
                    [3]
                ],
                warehouse_types_array: []
            }
        },
        created() {},
        mounted() {},
        watch: {
            warehouse_type_index(val) {
                const index = parseInt(val);

                const array = this.array_warehouse_types_ids[index];

                this.warehouse_types_array = array;
            }
        },
        computed: {},
        methods: {
            formController: function(url, event) {
                const target = $(event.target);
                const warehouse_types_array = this.warehouse_types_array;

                EventBus.$emit('loading', true);

                axios.post(url, {
                    warehouse_types_array
                }).then(response => {
                    const data = response.data;
                    EventBus.$emit('show_table', data);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                    EventBus.$emit('loading', false);
                    const obj = error.response.data.errors;
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        // console.log(target);
                        let c_target = target.find("#" + i + "-error");
                        let p = c_target.parents('.form-group').find('#' + i);
                        p.addClass('is-invalid');
                        c_target.css('display', 'block');
                        c_target.html(item);
                    });
                });
            },
        }
    }
</script>