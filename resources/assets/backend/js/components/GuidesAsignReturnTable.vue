<template>
  <div>
      <!--begin::Portlet-->
      <div class="kt-portlet" v-if="show_table">
          <div class="kt-portlet__head">
              <div class="kt-portlet__head-label">
                  <h3 class="kt-portlet__head-title">
                      Guías
                  </h3>
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
  import Swal from 'sweetalert2';
  import EventBus from '../event-bus';

  export default {
      props: {
          url: {
              type: String,
              default: ''
          },
          url_view_detail: {
              type: String,
              default: ''
          },
      },
      data() {
          return {
              show_table: false,
              data: [],
              guide: {},
              account_type_id: 0,
              ids: []
          }
      },
      created() {
          let context = this;
          $(document).on('click', '.view', function () {
              EventBus.$emit('loading', true);

              const index = $(this).parents('tr').index();

              const data = context.data[index];

              const id = data.id;
              axios.post(context.url_view_detail, { id })
                  .then(response => {
                      const data = response.data;
                      EventBus.$emit('loading', false);
                      EventBus.$emit('create_modal', data);
                  })
                  .catch(error => {
                      console.log(error);
                      console.log(error.response);
                      EventBus.$emit('loading', false);
                  });
          })
      },
      mounted() {
          EventBus.$on('show_table', function (response) {
              EventBus.$emit('loading', true);
              this.show_table = true;
              this.$store.commit('addModel', response);

              axios.post(this.url, {
                  company_id: parseInt(this.$store.state.model.company_id),
              }).then(response => {
                  const data = response.data;

                  this.data = data;

                  this.fillTableX(data);

                  EventBus.$emit('loading', false);
              }).catch(error => {
                  console.log(error);
                  console.log(error.response);
                  EventBus.$emit('loading', false);
              });
          }.bind(this));
      },
      watch: {},
      computed: {},
      methods: {
          fillTableX(data) {
              let vm = this;
              let token = document.head.querySelector('meta[name="csrf-token"]').content;

              this.datatable = $('.kt-datatable').KTDatatable({
                  // datasource definition
                  data: {
                      type: 'local',
                      source: data,
                      pageSize: 10,
                  },

                  // layout definition
                  layout: {
                      scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                      height: 400,
                      footer: false // display/hide footer
                  },

                  // column sorting
                  sortable: true,
                  pagination: false,

                  search: {
                      input: $('#generalSearch'),
                  },

                  extensions: {
                      checkbox: {
                          vars: {
                              selectedAllRows: 'selectedAllRows',
                              requestIds: 'requestIds',
                              rowIds: 'meta.rowIds',
                          },
                      },
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
                      autoHide: true,
                  },

                  // columns definition
                  columns: [
                      {
                          field: 'id',
                          title: '#',
                          sortable: false,
                          width: 30,
                          textAlign: 'center',
                      },
                      {
                          field: 'creation_date',
                          title: 'Fecha de Creacion',
                          width: 100,
                      },
                      {
                          field: 'referral_guide_series',
                          title: 'Numero de Serie',
                          width: 60,
                      },
                      {
                          field: 'referral_guide_number',
                          title: 'Numero de Guia',
                          width: 60,
                      },
                      {
                          field: 'account_name',
                          title: 'Cliente o Trabajador',
                          width: 300,
                      },
                      {
                          field: 'options',
                          title: 'Opciones',
                          sortable: false,
                          width: 60,
                          overflow: 'visible',
                          autoHide: false,
                          textAlign: 'right',
                          class: 'td-sticky',
                          template: function (row) {

                              let actions = '<div class="actions">';
                              actions += '<a style="cursor:pointer" class="view btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                              actions += '<i class="la la-eye"></i>';
                              actions += '</a>';
                              actions += '</div>';

                              return actions;

                          },
                      },
                  ]
              });
          },
      }
  }
</script>