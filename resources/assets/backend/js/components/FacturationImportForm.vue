<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Importar
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Archivo:</label>
                            <input type="file" id="archivo" name="archivo">
                            <div class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary">Importar</button>
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
    // You need a specific loader for CSS files
	import { Settings } from 'luxon'
	Settings.defaultLocale = 'es'

    export default {
        props: {

            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
            }
        },
		watch: {
		},
		methods: {
			formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);
                $('.invalid-feedback').empty();
                $('.invalid-feedback').hide();

                axios.post(url, fd, { headers: {
                        'Content-type': 'multipart/form-data;',
                    }
                }).then(response => {
                    EventBus.$emit('loading', false);
                    alert('Importado con éxito');
                }).catch(error => {
                    EventBus.$emit('loading', false);

                    let errors = error.response.data.errors;

                    for (const field in errors) {
                        for (var i = 0; i < errors[field].length; i++) {
                            console.log(errors[field][i]);
                            if ($('#' + field).parents('.form-group').find('.invalid-feedback').length > 0) {
                                $('#' + field).parents('.form-group').find('.invalid-feedback').show().append(errors[field][i] + '<br>');
                            } else {
                                $('#archivo').parents('.form-group').find('.invalid-feedback').show().append(errors[field][i] + '<br>');
                            }
                        }
                    }
                });
            },
		}
    };
</script>