<template>
  <!--begin::Modal-->
  <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
          <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
              <h3 class="kt-portlet__head-title">
                Lista de Balones
              </h3>
            </div>
          </div>

          <div class="kt-portlet__body kt-portlet__body--fit">
            <!--begin: Datatable -->
              <div class="row">
                <div class="col-12">
                  <table class="table table-vertical-middle table-layout-fixed">
                    <thead>
                      <tr>
                        <th>Articulo</th>
                        <th>Cliente</th>
                        <th>Cantidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in data" v-bind:key="index">
                        <td>{{ item.article_name }}</td>
                        <td>{{ item.client_name }}</td>
                        <td>{{ item.devol }}</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--end: Datatable -->
          </div>
        </div>
        <!--end::Portlet-->
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
  <!--end::Modal-->
</template>

<script>
  import EventBus from '../event-bus';
  import Datetime from 'vue-datetime';
  // You need a specific loader for CSS files
  import 'vue-datetime/dist/vue-datetime.css';

  export default {
      props: {
      },
      data() {
          return {
            show_table: false,
            data: []
          }
      },
      watch: {
      },
      computed: {
      },
      created() {
      },
      mounted() {
        EventBus.$on('create_modal', function (response) {
          this.show_table = true;
          this.data = response;

          $('#modal').modal('show');
        }.bind(this));
      },
      methods: {
      }
  };
</script>