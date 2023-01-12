<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Envío OSE'); ?>

<?php $__env->startSection('content'); ?>
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
		<form class="kt-form kt-form--label-right">
			<div class="kt-portlet__body">
				<div class="form-group form-group-last row">
					<div class="col-lg-4 form-group-sub">
						<label class="form-control-label">Compañía:</label>
						<select class="form-control" name="companies">
							<option value="">Select</option>
							<option value="01">01</option>
							<option value="02">02</option>
							<option value="03">03</option>
							<option value="04">04</option>
							<option value="05">05</option>
							<option value="06">06</option>
							<option value="07">07</option>
							<option value="08">08</option>
							<option value="09">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
						<div id="companies-error" class="error invalid-feedback"></div>
					</div>
					<div class="col-lg-4 form-group-sub">
						<label class="form-control-label">Tipo de Documento:</label>
						<select class="form-control" name="document_types">
							<option value="">Select</option>
							<option value="2018">2018</option>
							<option value="2019">2019</option>
							<option value="2020">2020</option>
							<option value="2021">2021</option>
							<option value="2022">2022</option>
							<option value="2023">2023</option>
							<option value="2024">2024</option>
						</select>
						<div id="document_types-error" class="error invalid-feedback"></div>
					</div>
					<div class="col-lg-4 form-group-sub">
						<label class="form-control-label">Rango:</label>
						<select class="form-control" name="range">
							<option value="">Select</option>
							<option value="2018">2018</option>
							<option value="2019">2019</option>
							<option value="2020">2020</option>
							<option value="2021">2021</option>
							<option value="2022">2022</option>
							<option value="2023">2023</option>
							<option value="2024">2024</option>
						</select>
						<div id="document_types-error" class="error invalid-feedback"></div>
					</div>
				</div>
			</div>
			<div class="kt-portlet__foot">
				<div class="kt-form__actions">
					<div class="row">
						<div class="col-lg-6">
							<button type="reset" class="btn btn-primary">Buscar</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!--end::Form-->
	</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\www\pdd\resources\views/backend/billingOse.blade.php ENDPATH**/ ?>