<template>
	<!--begin::Modal-->
	<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<!--begin::Form-->
				<form class="kt-form" @submit.prevent="formController($event)">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Editar {{ model.referral_serie_number }}-{{ model.referral_voucher_number }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						</button>
					</div>
					<div class="modal-body">
						<div class="kt-portlet__body">
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Saldo:</label>
										<input type="number" class="form-control" name="balance" id="balance" placeholder="0" step="any" min="0.00" readonly v-model="model.balance" @focus="$parent.clearErrorMsg($event)">
										<div id="balance-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Monto:</label>
										<input type="number" class="form-control" name="paid" id="paid" placeholder="0" step="any" min="0.00" v-model="model.paid" @focus="$parent.clearErrorMsg($event)">
										<div id="paid-error" class="error invalid-feedback"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">
							Guardar
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
			
		},
		data() {
			return {
				model: {},
			}
		},
		watch: {
			
		},
		computed: {
			
		},
		created() {
			EventBus.$on('edit_modal', function(sale) {
				let data = JSON.parse(JSON.stringify(sale));
				this.model = data;

				$('#modal').modal('show');
				EventBus.$emit('loading', false);
			}.bind(this));
		},
		mounted() {
			$('#modal').on('hide.bs.modal', function(e) {
				this.model = {};
			}.bind(this));
		},
		methods: {
			formController: function(event) {
				var vm = this;
				EventBus.$emit('loading', true);
				if ( accounting.unformat(vm.model.paid) > accounting.unformat(vm.model.balance) ) {
					EventBus.$emit('loading', false);
					this.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'El Monto no puede exceder el Saldo.'
					});
				} else {
					EventBus.$emit('update_table', this.model);

					$('#modal').modal('hide');
					$('#modal').on('hide.bs.modal', function(e) {
						this.model = {};
					}.bind(this));
				}
			},
		}
	};
</script>