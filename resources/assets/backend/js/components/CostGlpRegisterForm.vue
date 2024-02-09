<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Crear
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                 
                 
                   
                   
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha a Costear:</label>
                            <datetime
                                v-model="model.since_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="since_date"
                                name="since_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                this.current_date
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                     

                    <div class="col-lg-3">
                        <div class="form-group">
                         <label class="form-control-label">Costo Mes:</label>
                            <select class="form-control" name="price_mes" id="price_mes" v-model="model.price_mes" @focus="$parent.clearErrorMsg($event)">
                               <option disabled value="">Seleccionar</option>
                                            <option value="05">MAYO</option>
                                            <option value="06">JUNIO</option>
                                            <option value="07">JULIO</option>                                            
                            </select>
                            <div id="price_mes-error" class="error invalid-feedback"></div>
                      </div>
                    </div>

                  
                    

                       


                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Siguiente</button>
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
          
            current_date: {
                type: String,
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
                  
                    since_date: this.current_date,
                    
                },
            }
        },
        created() {
            EventBus.$on('reset_stock_register', function() {
        
                this.model.since_date = this.current_date;
                this.model.price_mes = '';

                $('.kt-form').find('input').prop('disabled', false);
                $('.kt-form').find('select').prop('disabled', false);
                $('.kt-form').find('button').prop('disabled', false);
            }.bind(this));
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
                console.log(url);
                axios.post(url,this.url_store,  {
                  'model': this.model,
				 
                }).then(response => {

                    EventBus.$emit('loading', false);
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
        
    };
</script>