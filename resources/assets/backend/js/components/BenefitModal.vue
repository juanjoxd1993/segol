<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Indicar fecha</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="price_ids" id="price_ids" v-model="model.price_ids">
                               
                                <div class="col-lg-3 d-none">
									<div class="form-group">
                                         <label class="form-control-label">Beneficios Sociales</label>
                                            <select class="form-control" name="benefit_id" id="benefit_id" v-model="model.benefit_id" @focus="$parent.clearErrorMsg($event)">
                                             <option value="">Seleccionar</option>
                                             <option v-for="benefit_type in benefit_types" :value="benefit_type.id" v-bind:key="benefit_type.id">{{ benefit_type.name }}</option>
                                             </select>
                                             <div id="benefit_type_id-error" class="error invalid-feedback"></div>
                                     </div>
								</div>

                                <div class="col-lg-3 d-none">
                                    <div class="form-group">
                                        <label class="form-control-label">Días:</label>
                                        <input type="tel" class="form-control" name="amount" id="amount" placeholder="0.00" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
                                        <div id="amount-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Fecha  de inicio:</label>
                                         <datetime
                                            v-model="model.initial_effective_date"
                                            placeholder="Selecciona una Fecha"
                                            :format="'dd-LL-yyyy'"
                                            input-id="initial_effective_date"
                                            name="initial_effective_date"
                                            value-zone="America/Lima"
                                            zone="America/Lima"
                                            class="form-control"
                                            @focus="$parent.clearErrorMsg($event)">
                                        </datetime>
                                  <!--      <input type="text" class="form-control" name="initial_effective_date" id="initial_effective_date" v-model="model.initial_effective_date" @focus="$parent.clearErrorMsg($event)" readonly="readonly"> -->
                                        <div id="initial_effective_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Fecha final:</label>
                                        <datetime
                                            v-model="model.final_effective_date"
                                            placeholder="Selecciona una Fecha"
                                            :format="'dd-LL-yyyy'"
                                            input-id="final_effective_date"
                                            name="final_effective_date"
                                            value-zone="America/Lima"
                                            zone="America/Lima"
                                            class="form-control"
                                            @focus="$parent.clearErrorMsg($event)">
                                        </datetime>
                                        <div id="final_effective_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            Registrar
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
            benefit_types: {
                type: Array,
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
                    benefit_values: [],
                    benefit_id: '',
                    amount: '',
                    initial_effective_date: '',
                    final_effective_date: '',
                },
                min_effective_date: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(receivedData) {
                EventBus.$emit('loading', true);
                this.model.benefit_values = receivedData.benefit_values;
                this.model.price_ids = receivedData.employee_ids;
                this.model.benefit_id = '';
                this.model.amount = '';
                this.model.initial_effective_date = '';
                this.model.final_effective_date = '';
                this.min_effective_date = '';

                axios.post(this.url_get_min_effective_date).then(response => {
                    this.model.initial_effective_date = response.data.initial_effective_date;
                    this.min_effective_date = response.data.min_effective_date;

                    $('#modal').modal('show');
                    EventBus.$emit('loading', false);
                }).catch(error => {
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
                fd.append('benefit_values', JSON.stringify(vm.model.benefit_values));
                fd.append('ciclo_id', $('#ciclo_id').val());

                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea Registrar Beneficio Social?',
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

                            $('#modal').modal('hide');

                            this.model.price_ids = [];
                            this.model.benefit_values = [];
                            this.model.benefit_id = '';
                            this.model.amount = '';
                            this.model.initial_effective_date = '';
                            this.model.final_effective_date = '';
                            this.min_effective_date = '';

                            EventBus.$emit('refresh_table');

                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se Registro  exitosamente.',
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