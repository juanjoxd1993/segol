<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Registro
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)" id="first-step-form">
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
                            <label class="form-control-label">Tipo de Documento:</label>
                            <select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id" v-model="model.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
                            </select>
                            <div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº de Serie:</label>
							<input type="text" class="form-control" name="serie_number" id="serie_number" v-model="model.serie_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="serie_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Documento:</label>
							<input type="text" class="form-control" name="voucher_number" id="voucher_number" v-model="model.voucher_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="voucher_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <!-- <button type="submit" class="btn btn-primary" v-if="model.voucher_number">Modificar</button> -->
							<button type="submit" class="btn btn-primary">Agregar</button>
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
			companies: {
				type: Array,
				default: ''
			},
			warehouse_document_types: {
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
                    company_id: '',
					warehouse_document_type_id: '',
					serie_number: '',
					voucher_number: '',
                },
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('clearFirstStep', function() {
				$('#first-step-form').find('input').prop('disabled', false);
				$('#first-step-form').find('select').prop('disabled', false);
				$('#first-step-form').find('button').prop('disabled', false);

				this.model = {
                    company_id: '',
					warehouse_document_type_id: '',
					serie_number: '',
					voucher_number: '',
                };
			}.bind(this));
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

                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
					$('#first-step-form').find('input').prop('disabled', true);
					$('#first-step-form').find('select').prop('disabled', true);
					$('#first-step-form').find('button').prop('disabled', true);

					EventBus.$emit('loading', false);
					EventBus.$emit('firstStateUpdate', response.data);
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