<template>
  <!--begin::Modal-->
  <div class="modal fade" id="voucher-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <form class="kt-form" @submit.prevent="formController(url, $event)">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Actualizar Remesa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div class="kt-portlet__body">
              <div class="row">

                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Nro Recibo Inicial :</label>
                    <input type="text" class="form-control" name="detalle" id="detalle" v-model="model.detalle"
                      @focus="$parent.clearErrorMsg($event)">
                    <div id="detalle-error" class="error invalid-feedback"></div>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Nro Recibo Final :</label>
                    <input type="text" class="form-control" name="final" id="final" v-model="model.final"
                      @focus="$parent.clearErrorMsg($event)">
                    <div id="final-error" class="error invalid-feedback"></div>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Monto:</label>
                    <input type="decimal" class="form-control" name="amount" id="amount" v-model="model.amount"
                      @focus="$parent.clearErrorMsg($event)">
                    <div id="amount-error" class="error invalid-feedback"></div>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Fecha Emisión:</label>
                    <datetime v-model="model.date" placeholder="Selecciona una Fecha" :format="'dd-LL-yyyy'"
                      input-id="date" name="date" value-zone="America/Lima" zone="America/Lima" class="form-control"
                      @focus="$parent.clearErrorMsg($event)">
                    </datetime>
                    <div id="date-error" class="error invalid-feedback"></div>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Nro de Operación:</label>
                    <input type="text" class="form-control" name="operation_number" id="operation_number"
                      v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                    <div id="operation_number-error" class="error invalid-feedback"></div>
                  </div>
                </div>

              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">
                Actualizar
              </button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!--end::Modal-->
</template>

<script>
import EventBus from '../event-bus';
export default {
  props: {
    url: {
      type: String,
      default: ''
    },
  },
  data() {
    return {
      model: {
        amount: '',
        operation_number: '',
        detalle: '',
        final: '',
        date: ''
      }
    }
  },
  computed: {
  },
  created() {
    EventBus.$on('edit_modal', function (initial_voucher, final_voucher, sum_total) {
      EventBus.$emit('loading', false);

      this.model.amount = sum_total;
      this.model.detalle = initial_voucher;
      this.model.final = final_voucher;
      this.model.operation_number = '';
      this.model.date = '';

      $('#voucher-modal').modal('show');
    }.bind(this));
  },
  methods: {

    formController: function (url, event) {
      var vm = this;

      var target = $(event.target);
      var url = url;
      var fd = new FormData(event.target);

      EventBus.$emit('loading', true);
      axios.post(url, fd, {
        headers: {
          'Content-type': 'application/x-www-form-urlencoded',
        }
      }).then(response => {

        EventBus.$emit('loading', false);
        EventBus.$emit('refresh_table');

        Swal.fire({
          title: '¡Ok!',
          text: 'Se ha remesado correctamente',
          type: "success",
          heightAuto: false,
        });

        $('#voucher-modal').modal('hide');

      }).catch(error => {
        EventBus.$emit('loading', false);
        console.log(error.response);
        var obj = error.response.data.errors;
        $('.modal').animate({
          scrollTop: 0
        }, 500, 'swing');
        $.each(obj, function (i, item) {
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
  }
};
</script>