<template>
	<div>
		<!--begin::Portlet-->
		<div class="kt-portlet" v-if="show_table">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<h3 class="kt-portlet__head-title">
						Resultado
					</h3>
				</div>
				<div class="kt-portlet__head-toolbar">
					<div class="kt-portlet__head-wrapper">
						<div class="dropdown dropdown-inline">
							<a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportExcel()">
								<i class="fa fa-file-excel"></i> Exportar Excel
							</a>
							<a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportPDF()">
								<i class="fa fa-file-pdf"></i> Exportar PDF
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="kt-portlet__body kt-portlet__body--fit">
				<!--begin: Datatable -->
				<div class="kt-datatable"></div>
				<!--end: Datatable -->
			</div>
		</div>
		<!--end::Portlet-->
	</div>
</template>

<script>
	import EventBus from '../event-bus';

	export default {
		props: {
			url: {
				type: String,
				default: ''
			},
			url_export_pdf: {
				type: String,
				default: ''
			},
		},
		data() {
			return {
				price_list_report_table: undefined,
				show_table: false,
				model: {},
				export_excel: false,
				export_pdf: false,
			}
		},
		created() {

		},
		mounted() {
			EventBus.$on('show_table', function(response) {
				let vm = this;
				this.show_table = true;
				this.model = response;

				Vue.nextTick(function() {
					if ( vm.price_list_report_table == undefined ) {
						vm.fillTableX();
					} else {
						vm.price_list_report_table.setDataSourceParam('model', vm.model);
						vm.price_list_report_table.load();
					}
				
					vm.price_list_report_table.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
			}.bind(this));

			EventBus.$on('refresh_table', function() {
				if ( this.price_list_report_table != undefined ) {
					this.price_list_report_table.setDataSourceParam('model', this.model);
					this.price_list_report_table.load();
				}
			}.bind(this));
		},
		watch: {
			
		},
		computed: {
			
		},
		methods: {
			fillTableX: function() {
				let vm = this;
				let token = document.head.querySelector('meta[name="csrf-token"]').content;

				this.price_list_report_table = $('.kt-datatable').KTDatatable({
					// datasource definition
					data: {
						type: 'remote',
						source: {
							read: {
								url: vm.url,
								params: {
									_token: token,
									model: vm.model,
								},
							},
						},
						pageSize: 10,
					},

					// layout definition
					layout: {
						scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
						height: 400,
						footer: false // display/hide footer
					},

					// column sorting
					sortable: false,
					pagination: false,

					search: {
						input: $('#generalSearch'),
					},

					translate: {
						records: {
							processing: 'Espere porfavor...',
							noRecords: 'No hay registros'
						},
						toolbar: {
							pagination: {
								items: {
									default: {
										first: 'Primero',
										prev: 'Anterior',
										next: 'Siguiente',
										last: 'Último',
										more: 'Más páginas',
										input: 'Número de página',
										select: 'Seleccionar tamaño de página'
									},
									info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
								}
							}
						}
					},

					rows: {
						autoHide: false,
					},

					// columns definition
					columns: [
						{
							field: 'business_unit_name',
							title: 'Und. Negocio',
							width: 100,
							textAlign: 'left',
						},
						{
							field: 'company_short_name',
							title: 'Compañía',
							width: 80,
							textAlign: 'left',
						},
						{
							field: 'client_id',
							title: 'ID Cliente',
							width: 80,
							textAlign: 'center',
						},
						{
							field: 'client_code',
							title: 'Cód. Cliente',
							width: 80,
							textAlign: 'center',
						},
						{
							field: 'document_type_name',
							title: 'Doc. Cliente',
							width: 60,
							textAlign: 'center',
						},
						{
							field: 'document_number',
							title: 'Nº Doc.',
							width: 100,
							textAlign: 'center',
						},
						{
							field: 'business_name',
							title: 'Razón Social',
							width: 160,
							textAlign: 'left',
						},
						{
							field: 'article_code',
							title: 'Cód. Artículo',
							width: 80,
							textAlign: 'center',
						},
						{
							field: 'article_name',
							title: 'Descripción',
							width: 160,
							textAlign: 'left',
						},
						{
							field: 'price_igv',
							title: 'Precio venta',
							width: 100,
							textAlign: 'right',
						},
						{
							field: 'initial_effective_date',
							title: 'Vigencia desde',
							width: 120,
							textAlign: 'center',
						},
						{
							field: 'family_name',
							title: 'Marca',
							width: 100,
							textAlign: 'left',
						},
						{
							field: 'subgroup_name',
							title: 'Presentación',
							width: 100,
							textAlign: 'left',
						},
						{
							field: 'client_channel_name',
							title: 'Canal venta',
							width: 120,
							textAlign: 'left',
						},
						{
							field: 'client_zone_name',
							title: 'Zona venta',
							width: 120,
							textAlign: 'left',
						},
						{
							field: 'client_sector_name',
							title: 'Sector econom.',
							width: 120,
							textAlign: 'left',
						},
						{
							field: 'client_route_id',
							title: 'ID Ruta',
							width: 80,
							textAlign: 'center',
						},
						{
							field: 'client_route_name',
							title: 'Ruta',
							width: 280,
							textAlign: 'left',
						},
						// {
						//     field: 'id',
						//     title: 'ID',
						//     width: 0,
						//     overflow: 'hidden',
						//     responsive: {
						//         hidden: 'sm',
						//         hidden: 'md',
						//         hidden: 'lg',
						//         hidden: 'xl'
						//     }
						// },
					]
				});

				// this.price_list_report_table.columns('id').visible(false);
			},
			exportExcel: function() {
				EventBus.$emit('loading', true);
				this.export_excel = true;

				axios.post(this.url, {
					model: this.model,
					export_excel: this.export_excel,
				}, {
					responseType: 'blob',
				}).then(response => {
					// console.log(response);
					EventBus.$emit('loading', false);

					const url = window.URL.createObjectURL(new Blob([response.data]));
					const link = document.createElement('a');
					link.href = url;
					link.setAttribute('download', 'reporte-lista-de-precios-'+Date.now()+'.xls');
					document.body.appendChild(link);
					link.click();

					this.export_excel = false;
				}).catch(error => {
					console.log(error);
					console.log(error.response);
					EventBus.$emit('loading', false);
				});
			},
			exportPDF: function() {
				EventBus.$emit('loading', true);
				this.export_pdf = true;

				axios.post(this.url_export_pdf, {
					model: this.model,
					export_pdf: this.export_pdf,
				}, {
					responseType: 'blob',
				}).then(response => {
					// console.log(response);
					EventBus.$emit('loading', false);

					const url = window.URL.createObjectURL(new Blob([response.data]));
					const link = document.createElement('a');
					link.href = url;
					link.setAttribute('download', 'reporte-lista-de-precios-'+Date.now()+'.pdf');
					document.body.appendChild(link);
					link.click();

					this.export_pdf = false;
				}).catch(error => {
					console.log(error);
					console.log(error.response);
					EventBus.$emit('loading', false);
				});
			},
		}
	};
</script>