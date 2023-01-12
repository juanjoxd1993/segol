/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import EventBus from './event-bus';
import Swal from 'sweetalert2';

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vuex from 'vuex'
Vue.use(Vuex)
import storeLiquidation from './store-liquidation'

const store = new Vuex.Store(
    storeLiquidation
)

const app = new Vue({
    store,
    el: '#app',
    methods: {
    	formController: function(url, event) {
            var target = $(event.target);
            var url = url;
            var fd = new FormData(event.target);

            EventBus.$emit('loading', true);
            axios.post(url, fd, { headers: {
					'Content-type': 'application/x-www-form-urlencoded',
				}
			}).then(response => {
                // console.log(response);
                EventBus.$emit('loading', false);
                if (response.data.type == 100) {
                    console.log(response.data);
                    return;
                } else {
                    this.alertMsg( response.data );
                    // EventBus.$emit('refresh_table');
                    EventBus.$emit('show_table', {
                        'data': response.data,
                    });
                }
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
        clearErrorMsg: function(event) {
            var elem = $(event.target);
            if ( elem.hasClass('is-invalid') ) {
                elem.removeClass('is-invalid');
                elem.next().html('');
            } else if ( elem.parent().hasClass('is-invalid') ) {
                elem.parent().removeClass('is-invalid');
                elem.parent().next().html('');
            } else {
                let id = elem.attr('id');
                let error_elem = elem.parents('.form-group').find('#' + id + '-error');
                if ( error_elem.is(':visible') ) {
                    error_elem.html('');
                }
            }
        },
        alertMsg: function(data) {
            if (data.type == 0) {
                return;
            } else if (data.type == 1) {
                Swal.fire({
                    title: data.title,
                    text: data.msg,
                    type: "success",
                    // timer: 2000,
                    heightAuto: false,
                })
            } else if (data.type == 2) {
                Swal.fire({
                    title: data.title,
                    text: data.msg,
                    type: "warning",
                    heightAuto: false,
                })
            } else if (data.type == 3) {
                Swal.fire({
                    title: data.title,
                    text: data.msg,
                    type: "success",
                    timer: 2000,
                    heightAuto: false
                }).then((confirmed) => {
                    window.location = data.url;
                })
            } else if (data.type == 4) {
                Swal.fire({
                    title: data.title,
                    text: data.msg,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar!',
                    heightAuto: false
                }).then(function(result) {
                    if (result.value) {
                        axios.post(data.url,{id: data.id}).then(response => {
                            Swal.fire({
                                title: response.data.title,
                                text: response.data.msg,
                                type: "success",
                                confirmButtonText: "OK",
                                heightAuto: false,
                            });
                            EventBus.$emit('refresh_table');
                        }).catch(error => {
                            console.log(error.response.data);
                        });
                    }
                });
            }  else if (data.type == 5) {
                Swal.fire({
                    title: data.title,
                    text: data.msg,
                    type: "error",
                    heightAuto: false,
                })
            }
        },
        hideForm() {
            EventBus.$emit('show_form', false);
        },
        isNumber: function(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;

            if ( charCode == 8 || (charCode >= 46 && charCode <= 57) || (charCode >= 96 && charCode <= 105)) {
                return true;
            } else {
                evt.preventDefault();
            }

            // if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
            //     evt.preventDefault();
            // } else {
            //     return true;
            // }
        },
        numberFormat: function(method, number, decimals, currency) {
            // method: 1 - from | 2 - to
            // currency: 1 - S/ | 2 - $ | 3 - €
            let prefix = '';

            switch ( currency ) {
                case 1:
                    prefix = 'S/ ';
                    break;
                case 2:
                    prefix = '$ ';
                    break;
                case 3:
                    prefix = '€ ';
                    break;
                default:
                    prefix = '';
                    break;
            }
            
            let numberFormat = wNumb({
                mark: '.',
                thousand: ',',
                prefix: prefix,
                decimals: decimals,
            });

            if ( method == 1 ) {
                return numberFormat.from(number);
            } else if ( method == 2 ) {
                return numberFormat.to(number);
            }
        }
    }
});
