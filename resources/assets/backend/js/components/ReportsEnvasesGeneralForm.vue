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
                                <option v-for="planta in plantas" v-bind:value="planta.id">{{ planta.name }}</option>
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
            plantas: {
                type: Array,
                default: []
            }
        },
        data() {
            return {
                warehouse_type_index: 0,
            }
        },
        created() {},
        mounted() {},
        watch: {},
        computed: {},
        methods: {
            formController: function(url, event) {
                const target = $(event.target);
                const warehouse_type_index = this.warehouse_type_index;

                EventBus.$emit('loading', true);

                axios.post(url, {
                    warehouse_type_index
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