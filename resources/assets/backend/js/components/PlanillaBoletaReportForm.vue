<template>
  <div class="kt-portlet">
    <div class="kt-portlet__head">
      <div class="kt-portlet__head-label">
        <h3 class="kt-portlet__head-title">Buscar Boletas</h3>
      </div>
    </div>
    <form class="kt-form" @submit.prevent="formController(url, $event)">
      <div class="kt-portlet__body">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label class="form-control-label">Ciclo:</label>
              <select class="form-control" v-model="model.ciclo_id" @focus="$parent.clearErrorMsg($event)">
                <option value="">Seleccionar</option>
                <option v-for="ciclo in ciclos" :value="ciclo.id" v-bind:key="ciclo.id">
                  {{ ciclo.año + " - " + ciclo.mes }}
                </option>
              </select>
              <div id="ciclo_id-error" class="error invalid-feedback"></div>
            </div>
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label class="form-control-label">Empleado:</label>
              <select class="form-control" v-model="model.empleado_id" @focus="$parent.clearErrorMsg($event)">
                <option value="">Seleccionar</option>
                <option v-for="empleado in empleados" :value="empleado.id" v-bind:key="empleado.id">
                  {{ empleado.first_name }}
                </option>
              </select>
              <div id="empleado_id-error" class="error invalid-feedback"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="kt-portlet__foot">
        <div class="kt-form__actions">
          <div class="row">
            <div class="col-lg-6">
              <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import EventBus from "../event-bus";

export default {
  props: {
    ciclos: Array,
    empleados: Array,
    url: String,
  },
  data() {
    return {
      model: {
        ciclo_id: '',
        empleado_id: '',
      },
    };
  },
  methods: {
    formController(url, event) {
      EventBus.$emit("loading", true);

      // Encuentra el ciclo y el empleado seleccionado basado en los ID actuales.
      let cicloSeleccionado = this.ciclos.find(ciclo => ciclo.id === this.model.ciclo_id);
      let empleadoSeleccionado = this.empleados.find(empleado => empleado.id === this.model.empleado_id);

      // Prepara los nombres para enviar. Si no se encuentra, se envía un valor vacío o predeterminado.
      let nombreCiclo = cicloSeleccionado ? `${cicloSeleccionado.año} - ${cicloSeleccionado.mes}` : '';
      let nombreEmpleado = empleadoSeleccionado ? empleadoSeleccionado.first_name : '';

      axios.post(url, {
        ciclo_nombre: nombreCiclo,
        empleado_nombre: nombreEmpleado
      })
      .then(response => {
        EventBus.$emit("show_table", response.data); // Emite un evento con los datos de respuesta para mostrar la tabla
        EventBus.$emit("loading", false);
      })
      .catch(error => {
        console.error("Error en la búsqueda: ", error);
        EventBus.$emit("loading", false);
      });
    },
  },
};
</script>
