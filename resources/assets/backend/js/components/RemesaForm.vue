<template>
  <div class="kt-portlet">
    <div class="kt-portlet__head">
      <div class="kt-portlet__head-label">
        <h3 class="kt-portlet__head-title">
          Buscar
        </h3>
      </div>
    </div>

    <form class="kt-form" @submit.prevent="formController(url, $event)">

      <div class="kt-portlet__body">
        <div class="row">

            <div class="col-lg-3">
              <div class="form-group">
                <label class="form-control-label">Monto:</label>
                <input type="number" class="form-control" name="amount" id="amount" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
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

        </div>
      </div>

      <div class="kt-portlet__foot">
        <div class="kt-form__actions">
          <div class="row">
            <div class="col-lg-6">
              <button type="submit" class="btn btn-primary">Crear</button>
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>
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
        model: {
          amount: '',
          date: ''
        }
      }
    },
    created() {},
    mounted() {},
    watch: {},
    computed: {},
    methods: {
      formController(url, e) {
        e.preventDefault();

        EventBus.$emit('loading', true);

        let target = $(e.target);

        axios.post(
          url,
          this.model
        ).then(res => {

          EventBus.$emit('loading', false);
          Swal.fire({
            title: '¡Creado!',
            text: 'Remesa registrada.',
            type: "success",
            heightAuto: false,
          });
          console.log(res);

        }).catch(err => {

          EventBus.$emit('loading', false);
          Swal.fire({
            title: '¡Error!',
            text: 'No se pudo registrar la remesa.',
            type: "error",
            heightAuto: false,
          });
          console.log(err);
          console.log(err.response);
          let obj = err.response.data.errors;
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

        })
      }
    }
  };
</script>