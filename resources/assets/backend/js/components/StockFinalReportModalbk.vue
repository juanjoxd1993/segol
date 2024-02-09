<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                             
                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Serie de Guía de Remisión:</label>
										<input type="text" class="form-control" name="referral_guide_series" id="referral_guide_series" v-model="model.referral_guide_series" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_guide_series-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Número de Guía de Remisión:</label>
										<input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_guide_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Serie de Referencia:</label>
										<input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_serie_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Número de Referencia:</label>
										<input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_voucher_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Número de SCOP:</label>
										<input type="text" class="form-control" name="scop_number" id="scop_number" v-model="model.scop_number" @focus="$parent.clearErrorMsg($event)">
										<div id="scop_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Placa:</label>
										<input type="text" class="form-control" name="license_plate" id="license_plate" v-model="model.license_plate" @focus="$parent.clearErrorMsg($event)">
										<div id="license_plate-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Fecha de Movimiento:</label>
										<datetime
											v-model="model.date"
											placeholder="Selecciona una Fecha"
											:format="'dd-LL-yyyy'"
											input-id="date"
											name="date"
											value-zone="America/Lima"
											zone="America/Lima"
											class="form-control"
											:min-datetime="model.min_datetime"
											:max-datetime="model.max_datetime"
											@focus="$parent.clearErrorMsg($event)">
										</datetime>
										<div id="date-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Cambiar tipo de Movimiento:</label>
										<label class="kt-checkbox kt-checkbox--brand mt-3">
											<input type="checkbox" v-model="model.typing_error" name="typing_error" id="typing_error"> Error de Digitación
											<span></span>
										</label>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
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
	import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    account_id: '',
                    referral_guide_series: '',
                    referral_guide_number: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    scop_number: '',
                    license_plate: '',
                    date: '',
                    min_datetime: '',
                    max_datetime: '',
                    typing_error: '',
                },
                button_text: ''
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function() {
                this.button_text = 'Crear';
				this.model = {
                    id: '',
					account_id: '',
                    referral_guide_series: '',
                    referral_guide_number: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    scop_number: '',
                    license_plate: '',
					date: '',
					min_datetime: '',
                    max_datetime: '',
					typing_error: '',
                };

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

                    this.model = {
						id: '',
						account_id: '',
						referral_guide_series: '',
						referral_guide_number: '',
						referral_serie_number: '',
						referral_voucher_number: '',
						scop_number: '',
						license_plate: '',
						date: '',
						min_datetime: '',
                    	max_datetime: '',
						typing_error: '',
					};

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