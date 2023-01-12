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
                            <label class="form-control-label">Categoria:</label>
                            <select class="form-control" name="category_id" id="category_id" v-model="model.category_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="category in categories" :value="category.id" v-bind:key="category.id">{{ category.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary">Buscar</button>
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
            categories: {
                type: Array,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    category_id: '',
                },
            }
        },
        created() {

        },
        mounted() {
            
        },
        watch: {

        },
        computed: {

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
                    EventBus.$emit('show_table', {
                        'data': vm.model.category_id,
                    });
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
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