<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Registrar Recurso</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="price_ids" id="price_ids" v-model="model.price_ids">
                                <div class="col-lg-3">
									<div class="form-group">
                                        <label class="form-control-label">Operación:</label>
                                        <select class="form-control" name="operation_id" id="operation_id" v-model="model.operation_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option value="1">BONO POR CUMPLIMIENTO</option>
                                            <option value="2">BONO DE PRODUCTIVIDAD</option>
                                            <option value="3">MOVILIDAD SUPEDITADA A LA ASISTENCIA</option>
                                            <option value="4">DESCUENTO AUTORIZADO</option>
                                            <option value="5">PRESTAMOS </option>
                                        </select>
                                        <div id="operation_id-error" class="error invalid-feedback"></div>
                                    </div>
								</div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Dias:</label>
                                        <input type="tel" class="form-control" name="amount" id="amount" placeholder="1" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
                                        <div id="amount-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Monto Total:</label>
                                        <input type="tel" class="form-control" name="amount2" id="amount2" placeholder="1" v-model="model.amount2" @focus="$parent.clearErrorMsg($event)">
                                        <div id="amount-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            Actualizar
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
            url_get_min_effective_date: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    price_ids: [],
                    operation_id: '',
                    amount: '',
                    amount2: '',
                   
                },
                min_effective_date: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(price_ids) {
                EventBus.$emit('loading', true);

                this.model.price_ids = price_ids;
                this.model.operation_id = '';
                this.model.amount = '';
                this.model.amount2 = '';

                this.min_effective_date = '';

                axios.post(this.url_get_min_effective_date).then(response => {
                   
                    this.min_effective_date = response.data.min_effective_date;

                    $('#modal').modal('show');
                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
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

                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea Actualizar la Asistencia?',
                    type: "warning",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    if ( result.value ) {
                        EventBus.$emit('loading', true);

                        axios.post(url, fd, { headers: {
                                'Content-type': 'application/x-www-form-urlencoded',
                            }
                        }).then(response => {
                            EventBus.$emit('loading', false);
                            console.log(response);

                            $('#modal').modal('hide');

                            this.model.price_ids = [];
                            this.model.operation_id = '';
                            this.model.amount = '';
                            this.model.amount2 = '';
                            this.min_effective_date = '';

                            EventBus.$emit('refresh_table');

                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se actualizó la Asistencia exitosamente.',
                                type: "success",
                                heightAuto: false,
                            });

                        }).catch(error => {
                            EventBus.$emit('loading', false);
                            console.log(error.response);
                            var obj = error.response.data.errors;
                            $('.modal').animate({
                                scrollTop: 0
                            }, 500, 'swing');
                            $.each(obj, function(i, item) {
                                let c_target = target.find("#" + i + "-error");
                                let p = c_target.parents('.form-group').find('#' + i);
                                p.addClass('is-invalid');
                                c_target.css('display', 'block');
                                c_target.html(item);
                            });
                        });
                    } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                        EventBus.$emit('loading', false);
                    }
                });
            },
        }
    };
</script>