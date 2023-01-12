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
                            <label class="form-control-label">Compañía:</label>
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{ company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
				
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de SCOP:</label>
                            <input type="text" class="form-control" name="scop_number" id="scop_number" v-model="model.scop_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="scop_number-error" class="error invalid-feedback"></div>
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
    import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    Vue.use(Datetime);

    export default {
        props: {
          
            companies: {
                type: Array,
                default: ''
            },
            
            url: {
                type: String,
                default: ''
            },
           
            url_get_warehouse_movements: {
				type: String,
				default: '' 
			}  
        },
        data() {
            return {
                model: {
                    company_id: '',
                    scop_number: '',
                    
                    
                },
            }
        },
        created() {
            
        },
        mounted() {
            this.newSelect2();
        },
        watch: {
            
        },
        computed: {
          
        },
        methods: {
           
         
            formController: function(url, event) {
                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);
                axios.post(url, fd, {
                    headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    console.log(response);
                    EventBus.$emit('show_table', response.data);
                    // this.alertMsg( response.data );
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