<template>
  <!--begin::Modal-->
  <div class="modal fade" id="voucher-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Detalle de Remesa</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <div class="kt-portlet__body">
            <div class="row">

              <div class="col-lg-3">
              <div class="form-group">
                <label class="form-control-label">Monto:</label>
                <input type="decimal" class="form-control" name="amount" id="amount" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
                <div id="amount-error" class="error invalid-feedback"></div>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                  <label class="form-control-label">Fecha Emisión:</label>
                  <datetime
                      v-model="model.date"
                      placeholder="Selecciona una Fecha"
                      :format="'dd-LL-yyyy'"
                      input-id="date"
                      name="date"
                      value-zone="America/Lima"
                      zone="America/Lima"
                      class="form-control"
                      @focus="$parent.clearErrorMsg($event)">
                  </datetime>
                  <div id="date-error" class="error invalid-feedback"></div>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label class="form-control-label">Nro de Operación:</label>
                <input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                <div id="operation_number-error" class="error invalid-feedback"></div>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label class="form-control-label">Nro Recibo Inicial :</label>
                <input type="text" class="form-control" name="detalle" id="detalle" v-model="model.detalle" @focus="$parent.clearErrorMsg($event)">
                <div id="detalle-error" class="error invalid-feedback"></div>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group">
                <label class="form-control-label">Nro Recibo Final :</label>
                <input type="text" class="form-control" name="final" id="final" v-model="model.final" @focus="$parent.clearErrorMsg($event)">
                <div id="final-error" class="error invalid-feedback"></div>
              </div>
            </div>

            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="update_voucher" @click.prevent="update_voucher()">Crear</button>
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
          amount: '',
          operation_number:'',
          detalle: '',
          final: '',
          date: ''
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
            text: 'Se ha remesado correctamente',
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