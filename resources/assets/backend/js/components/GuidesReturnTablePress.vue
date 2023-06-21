<template>
  <div>
      <!--begin::Modal-->
      <div class="modal fade" id="modalPress" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
              <div class="modal-content">
                  <!--begin::Form-->
                  <div class="kt-form">
                      <div class="modal-header">
                          <h5 class="modal-title" id="modalLabelClients">Agregar Cliente</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          </button>
                      </div>
                      <div class="modal-body">
                          <div class="kt-portlet__body">
                              <div class="row">

                                  <div class="col-lg-3">
                                      <div class="form-group">
                                          <label class="form-control-label">Cliente:</label>
                                          <select class="form-control kt-select2" name="client_id_press" id="client_id_press" v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
                                              <option value="">Seleccionar</option>
                                          </select>
                                          <div id="client_id_press-error" class="error invalid-feedback"></div>
                                      </div>
                                  </div>

                                  <div class="col-lg-3">
                                      <div class="form-group">
                                          <label class="form-control-label">Articulo:</label>
                                          <select class="form-control" name="article_id" id="article_id" v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                              <option value="0" selected>Seleccionar</option>
                                              <option v-for="balon in balones" v-bind:key="balon.article_id" :value="balon.article_id">{{ balon.article_name }}</option>
                                          </select>
                                          <div id="balon_id-error" class="error invalid-feedback"></div>
                                      </div>
                                  </div>

                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label class="form-control-label">{{ title }}:</label>
                                          <input type="number" class="form-control" v-model="model.press">
                                      </div>
                                  </div>

                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label class="form-control-label">Stock Pendiente:</label>
                                          <input type="number" class="form-control" v-model="stock_press" readonly>
                                      </div>
                                  </div>

                              </div>
                          </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-success" @click="agree">
                              Agregar
                          </button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="close">Cerrar</button>
                      </div>
                  </div>
                  <!--end::Form-->
              </div>
          </div>
      </div>
      <div>
          <!--begin::Portlet-->
          <div class="kt-portlet" v-if="show_table">
              <div class="kt-portlet__head">
                  <div class="kt-portlet__head-label">
                      <h3 class="kt-portlet__head-title">
                          Prestamo a Clientes
                      </h3>
                  </div>
                  <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper" style="margin-right: 16px;">
                      <div class="dropdown dropdown-inline">
                        <a href="#" class="btn btn-success btn-bold btn-sm" @click.prevent="openModal('devl')">
                          <i class="la la-plus"></i> Devolucion
                        </a>
                      </div>
                    </div>
                    <div class="kt-portlet__head-wrapper">
                      <div class="dropdown dropdown-inline">
                        <a href="#" class="btn btn-primary btn-bold btn-sm" @click.prevent="openModal()">
                          <i class="la la-minus"></i> Prestamo
                        </a>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
                  <!--begin: Datatable -->
                  <div class="kt-datatable-press"></div>
                  <!--end: Datatable -->
              </div>
          </div>
          <!--end::Portlet-->
      </div>
  </div>
</template>

<script>
  import EventBus from '../event-bus';

  export default {
    props: {
      url_get_clients: {
        type: String,
        default: ''
      },
    },
    data() {
      return {
        model: {
          client_id: '',
          client_name: '',
          article_id: '',
          article_name: '',
          press: '',
          if_devol: false,
        },
        balones: [],
        title: 'Monto a Prestar',
        stock_press: 0,
        show_table: false,
        data: [],
        datatable: null,
      }
    },
    mounted() {
      this.newSelect2();
      EventBus.$on('show_table', () => {
          this.show_table = true;

          // axios.post(this.url_list, {
          //     model: this.$store.state.model,
          // }).then(response => {
          //     const data = response.data;

          //     this.articles = data;

          //     EventBus.$emit('loading', false);
          // }).catch(error => {
          //     console.log(error);
          //     console.log(error.response);
          // });

          this.$nextTick(function() {
              if ( this.datatable == undefined ) {
                  this.fillTableX();
              } else {
                  // this.datatable.originalDataSet = this.$store.state.sales;
                  this.datatable.load();
              }
          });
      });
      EventBus.$on('refresh_table_sale', () => {
          if ( this.sale_datatable != undefined ) {
              this.sale_datatable.originalDataSet = this.$store.state.sales;
              this.sale_datatable.load();
          }
      });
    },
    watch: {
      'model.article_id': function(val) {
        this.changeArticleId(val);
      }
    },
    methods: {
      newSelect2: function() {
        let vm = this;
        let token = document.head.querySelector('meta[name="csrf-token"]').content;
        $("#client_id_press").select2({
          placeholder: "Buscar",
          allowClear: true,
          language: {
              noResults: function() {
                  return 'No hay resultados';
              },
              searching: function() {
                  return 'Buscando...';
              },
              inputTooShort: function() {
                  return 'Ingresa 1 o más caracteres';
              },
              errorLoading: function() {
                  return 'No se pudo cargar la información'
              }
          },
          ajax: {
              url: this.url_get_clients,
              dataType: 'json',
              delay: 250,
              type: 'POST',
              data: function (params) {
                  var queryParameters = {
                      q: params.term,
                      company_id: vm.$store.state.model.company_id,
                      _token: token,
                  }

                  return queryParameters;
              },
              processResults: function(data, params) {
                  params.page = params.page || 1;

                  return {
                      results: data,
                      pagination: {
                          more: (params.page * 30) < data.total_count
                      }
                  };
              },
              cache: true
          },
          minimumInputLength: 1,
        }).on('select2:select', function(e) {
            vm.model.client_id = e.params.data.id;
            vm.model.client_name = e.params.data.text;
        }).on('select2:unselect', function(e) {
            vm.model.client_id = '';
            vm.model.client_name = '';
        });
      },
      fillTableX: function() {
          let vm = this;
          let token = document.head.querySelector('meta[name="csrf-token"]').content;

          this.datatable = $('.kt-datatable-press').KTDatatable({
              // datasource definition
              data: {
                  type: 'local',
                  source: this.data,
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
                      field: 'id',
                      title: '#',
                      width: 60,
                      textAlign: 'left',
                  },
                  {
                      field: 'client_name',
                      title: 'Cliente',
                      width: 200,
                      textAlign: 'left',
                  },
                  {
                      field: 'article_name',
                      title: 'Tipo',
                      width: 120,
                      textAlign: 'left',
                  },
                  {
                      field: 'press',
                      title: 'Monto Prestamo',
                      width: 60,
                      textAlign: 'right',
                  },
                  {
                      field: 'options',
                      title: 'Opciones',
                      sortable: false,
                      width: 120,
                      overflow: 'visible',
                      autoHide: false,
                      textAlign: 'right',
                      template: function(row) {
                          let actions = '<div class="actions">';
                          actions += '<a title="Eliminar" class="delete-press btn btn-danger btn-sm btn-icon btn-icon-md" href="#">';
                          actions += '<i class="la la-trash"></i>';
                          actions += '</a>';
                          actions += '</div>';

                          return actions;
                      },
                  }
              ]
          });
      },
      openModal: function(type) {
        this.model = {
          client_id: '',
          client_name: '',
          article_id: '',
          article_name: '',
          press: '',
          if_devol: false,
        };
        this.stock_press = 0;

        if (type === 'devl') {
          this.title = 'Monto a Devolver'
          this.model.if_devol = true;
        } else {
          this.title = 'Monto a Prestar'
          this.model.if_devol = false;
        };

        this.balones = this.$store.state.balones;

        this.newSelect2();
        $("#modalPress").modal('show');
      },
      agree() {
        const model = this.model;

        if (!model.client_id) {
          Swal.fire({
            title: '¡Error!',
            text: 'Tiene que asignar un cliente',
            type: "error",
            heightAuto: false,
          })
        }
        else if (!model.article_id) {
          Swal.fire({
            title: '¡Error!',
            text: 'Tiene agregar un articulo',
            type: "error",
            heightAuto: false,
          })
        }
        else if (!model.press) {
          Swal.fire({
            title: '¡Error!',
            text: 'Tiene que asignar un monto',
            type: "error",
            heightAuto: false,
          })
        }
        else if (model.press > this.stock_press) {
          Swal.fire({
            title: '¡Error!',
            text: 'El monto asignado no puede ser mayor al stock actual',
            type: "error",
            heightAuto: false,
          })
        }
        else {
        this.$store.commit('changeStockBalon', {
          article_id: this.model.article_id,
          amount: this.model.press,
          if_devol: this.model.if_devol,
          operation: -1
        });

        this.datatable.destroy();

        this.data.push({
          id: this.data.length + 1,
          ...this.model
        });
        this.$store.state.prestamos.push(this.model);

        this.fillTableX();
        $("#modalPress").modal('hide');

        }
      },
      close() {},
      changeArticleId(val) {
        const item = this.balones.find(element => element.article_id == val);

        if (item) {
          this.model.article_name = item.article_name;

          if (this.title === 'Monto a Prestar') {
            this.stock_press = item.prestamo;
          } else {
            this.stock_press = item.retorno_press;
          };
        };
      },
			manageActions: function(event) {
        if ( $(event.target).hasClass('delete-press') ) {
          event.preventDefault();
          let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

          const press = this.$store.state.prestamos.find((item, index) => index == parseInt(id - 1));
          const filter_press = this.$store.state.prestamos.filter((item, index) => index != parseInt(id - 1));

          this.$store.commit('changeStockBalon', {
            article_id: press.article_id,
            amount: press.press,
            if_devol: press.if_devol,
            operation: 1
          });

          const data = [];

          filter_press.map(item => {
            data.push({
              id: data.length + 1,
              ...item
            });
          });

          this.$store.state.prestamos = filter_press;

          this.datatable.destroy();

          this.data = data;

          this.fillTableX();
        }
      },
    }
  }
</script>