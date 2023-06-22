<template>
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
      <form class="kt-form" @submit.prevent="formController(url, $event)">
          <div class="kt-portlet__body">
              <div class="row">

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label class="form-control-label">Fecha Inicial:</label>
                        <datetime
                          v-model="model.date_init"
                          placeholder="Selecciona una Fecha"
                          :format="'dd-LL-yyyy'"
                          input-id="sale_date"
                          name="sale_date"
                          value-zone="America/Lima"
                          zone="America/Lima"
                          class="form-control"
                          :max_date_time="max_date_time"
                          @focus="$parent.clearErrorMsg($event)">
                        </datetime>
                      <div id="sale_date-error" class="error invalid-feedback"></div>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label class="form-control-label">Fecha Final:</label>
                        <datetime
                          v-model="model.date_end"
                          placeholder="Selecciona una Fecha"
                          :format="'dd-LL-yyyy'"
                          input-id="sale_date"
                          name="sale_date"
                          value-zone="America/Lima"
                          zone="America/Lima"
                          class="form-control"
                          :max_date_time="max_date_time"
                          @focus="$parent.clearErrorMsg($event)">
                        </datetime>
                      <div id="sale_date-error" class="error invalid-feedback"></div>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-group">
                      <label class="form-control-label">Estado:</label>
                      <select class="form-control" name="movement_type_id" id="movement_type_id" v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                        <option selected="true" value="0">Prestados</option>
                        <option value="1">Devueltos</option>
                      </select>
                      <div id="movement_type_id-error" class="error invalid-feedback"></div>
                    </div>
                  </div>

              </div>
          </div>
          <div class="kt-portlet__foot">
              <div class="kt-form__actions">
                  <div class="row">
                      <div class="col-lg-3">
                          <button
                              type="submit"
                              class="btn btn-primary"
                          >Buscar</button>
                      </div>
                  </div>
              </div>
          </div>
      </form>
      <!--end::Form-->
  </div>
  <!--end::Portlet-->
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
                date_init: '',
                date_end: '',
                movement_type_id: 0
              },
              max_date_time: new Date(),
          }
      },
      created() {},
      mounted() {},
      watch: {},
      computed: {},
      methods: {
          formController: function(url, event) {
              const target = $(event.target);
              const model = this.model;

              EventBus.$emit('loading', true);

              axios.post(url, {
                  ...model
              }).then(response => {
                  const data = response.data;
                  EventBus.$emit('show_table', data);
              }).catch(error => {
                  console.log(error);
                  console.log(error.response);
                  EventBus.$emit('loading', false);
                  const obj = error.response.data.errors;
                  $('html, body').animate({
                      scrollTop: 0
                  }, 500, 'swing');
                  $.each(obj, function(i, item) {
                      // console.log(target);
                      let c_target = target.find("#" + i + "-error");
                      let p = c_target.parents('.form-group').find('#' + i);
                      p.addClass('is-invalid');
                      c_target.css('display', 'block');
                      c_target.html(item);
                  });
              });
          },
      }
  }
</script>