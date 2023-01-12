<template>
    <!--begin::Portlet-->
    <div class="kt-portlet kt-portlet--mobile" v-show="show_table">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand la la-file-text"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        
                    </div>
                </div>
            </div>
        </div>
		<form class="kt-form kt-form--label-right" @submit.prevent="formController(url, $event)">
			<input type="hidden" name="business_unit_id" v-model="business_unit_id">
			<input type="hidden" name="year" v-model="year">
			<input type="hidden" name="month" v-model="month">
			<input type="hidden" name="days" v-model="days">
			<div class="kt-portlet__body">
				<div class="form-group row">
					<label class="col-md-2 col-form-label"></label>
					<div class="col-md-3">
						<h6>Toneladas métricas</h6>
					</div>
					<div class="col-md-3">
						<h6>Total</h6>
					</div>
				</div>
				<div class="form-group row" v-for="(element, index) in elements" v-bind:key="element.id">
					<label for="" class="col-md-2 col-form-label">
						{{ element.name }}
					</label>
					<div class="col-md-3">
						<input type="hidden" name="ids[]" v-model="element.id">
						<input type="number" :readonly="element.id == ''" class="form-control" name="metric_tons[]" placeholder="Toneladas métricas" step="0.001" v-model="element.metric_tons" @focus="$parent.clearErrorMsg($event)">
					</div>
					<div class="col-md-3">
						<input type="number" :readonly="element.id == ''" class="form-control" name="totals[]" placeholder="Total" step="0.001" v-model="element.total" @focus="$parent.clearErrorMsg($event)">
						<input type="hidden" name="percentages[]" v-model="element.percentage">
					</div>
				</div>
				<div class="form-group row">
					<label for="" class="col-md-2 col-form-label">
						<strong>TOTAL</strong>
					</label>
					<div class="col-md-3">
						<input type="number" readonly class="form-control" name="elements_total_mt" min="0.0001" v-model="elements_total.metric_tons">
					</div>
					<div class="col-md-3">
						<input type="number" readonly class="form-control" name="elements_total" placeholder="Total" step="0.001" v-model="elements_total.total">
						<input type="hidden" name="elements_total_percentage" v-model="elements_total.percentage">
					</div>
				</div>
			</div>
			<div class="kt-portlet__foot">
                <div class="kt-form__actions kt-form__actions--right">
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
		</form>
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
            }
        },
        data() {
            return {
				show_table: false,
                elements: [],
				elements_total: {
					metric_tons: 0,
					total: 0,
					percentage: 0,
				},
				business_unit_id: '',
				year: '',
				month: '',
				days: '',
            }
        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.show_table = true;
				this.elements = response.elements;
				this.business_unit_id = response.business_unit_id;
				this.year = response.year;
				this.month = response.month;
				this.days = response.days;
				this.elements.map( element => {
					Vue.set(element, 'metric_tons', 0);
					Vue.set(element, 'total', 0);
					Vue.set(element, 'percentage', 0);
				});
				EventBus.$emit('loading', false);
            }.bind(this));
        },
		computed: {
			computedElements() {
				return JSON.stringify(this.elements);
			}
		},
		watch: {
			computedElements: {
				handler: function(after, before) {
					let oldValue = JSON.parse(before);
					let newValue = JSON.parse(after);

					if ( oldValue != '' ) {
						let total_mt = newValue.reduce((a, {metric_tons}) => Number(a) + Number(metric_tons), 0);
						let total = newValue.reduce((a, {total}) => Number(a) + Number(total), 0);
						let total_percentage = newValue.reduce((a, {percentage}) => Number(a) + Number(percentage), 0);

						this.elements.map(element => {
							element.percentage = accounting.toFixed((element.metric_tons * 100) / total_mt, 2);
						});

						this.elements_total.metric_tons = accounting.toFixed(total_mt, 2);
						this.elements_total.total = accounting.toFixed(total, 2);
						this.elements_total.percentage = accounting.toFixed(total_percentage, 2);
					}
				},
				deep: true
			}
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
                    console.log(response);

					this.show_table = false;
					this.elements = [];
					this.elements_total = {
						metric_tons: 0,
						total: 0,
						percentage: '',
					};
					this.business_unit_id = '';
					this.year = '';
					this.month = '';
					this.days = '';

					EventBus.$emit('loading', false);
					this.$parent.alertMsg(response.data);
                    EventBus.$emit('reset_form');
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
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
            },
        }
    };
</script>