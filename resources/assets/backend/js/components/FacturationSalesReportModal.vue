<template>
  <!--begin::Modal-->
  <div class="modal fade" id="voucher-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle de Voucher</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <div class="kt-portlet__body">
            <div class="row">

              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">SCOP:</label>
                  <input type="text" class="form-control" name="scop" id="scop" v-model="voucher.scop" @focus="$parent.clearErrorMsg($event)">
                  <div id="scop-error" class="error invalid-feedback"></div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Nombre del Cliente:</label>
                  <input type="text" class="form-control" name="client_name" id="client_name" v-model="voucher.client_name" @focus="$parent.clearErrorMsg($event)">
                  <div id="client_name-error" class="error invalid-feedback"></div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="form-group">
                  <label class="form-control-label">Direccion del Cliente:</label>
                  <input type="text" class="form-control" name="client_address" id="client_address" v-model="voucher.client_address" @focus="$parent.clearErrorMsg($event)">
                  <div id="client_address-error" class="error invalid-feedback"></div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="update_voucher" @click.prevent="update_voucher()">Actualizar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!--end::Modal-->
</template>

<script>
  import EventBus from '../event-bus';
  export default {
    props: {
      url_update: {
        type: String,
        default: ''
      },
    },
    data() {
      return {
        voucher: {
          id: 0,
          scop: '',
          client_address: '',
          client_name: ''
        }
      }
    },
    computed: {
    },
    created() {
      EventBus.$on('edit_modal', function(data) {
        EventBus.$emit('loading', false);

        this.voucher = data;

        $('#voucher-modal').modal('show');
      }.bind(this));
    },
    methods: {
      update_voucher() {
        EventBus.$emit('loading', true);
        axios.post(this.url_update, this.voucher).then(response => {
          EventBus.$emit('loading', false);
          Swal.fire({
            title: '¡Ok!',
            text: 'Se ha actualizado correctamente',
            type: "success",
            heightAuto: false,
          });
          $('#voucher-modal').modal('hide');
        }).catch(error => {
          EventBus.$emit('loading', false);
          Swal.fire({
            title: '¡Error!',
            text: 'Ha ocurrido un error',
            type: "error",
            heightAuto: false,
          });
          console.log(error);
          console.log(error.response);
        });
      }
    }
  };
</script>