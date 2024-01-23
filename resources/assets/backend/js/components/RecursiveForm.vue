<template>
  <!--begin::Portlet-->
  <div class="kt-portlet">
    <div class="kt-portlet__head">
      <div class="kt-portlet__head-label">
        <h3 class="kt-portlet__head-title">Buscar</h3>
      </div>
    </div>

    <!--begin::Form-->
    <form class="kt-form" @submit.prevent="formController(url, $event)">
      <div class="kt-portlet__body">
        <div class="row">
          <input
            type="hidden"
            name="warehouse_type_id"
            id="warehouse_type_id"
            v-model="model.warehouse_type_id"
          />
          <div class="col-lg-3">
            <div class="form-group">
              <label class="form-control-label">Compañía:</label>
              <select
                class="form-control"
                name="company_id"
                id="company_id"
                v-model="model.company_id"
                @focus="$parent.clearErrorMsg($event)"
              >
                <option value="">Seleccionar</option>
                <option
                  v-for="company in companies"
                  :value="company.id"
                  v-bind:key="company.id"
                >
                  {{ company.name }}
                </option>
              </select>
              <div id="company_id-error" class="error invalid-feedback"></div>
            </div>
          </div>

          <div class="col-lg-3">
            <div class="form-group">
              <label class="form-control-label">Área:</label>
              <select
                class="form-control kt-select2"
                name="area_id"
                id="area_id"
                v-model="model.area_id"
                @focus="$parent.clearErrorMsg($event)"
              >
                <option disabled value="">Seleccionar</option>
                <option
                  v-for="area in areas"
                  :value="area.id"
                  v-bind:key="area.id"
                >
                  {{ area.name }}
                </option>
              </select>
              <div id="article_id-error" class="error invalid-feedback"></div>
            </div>
          </div>
          <!-- Ciclo -->
          <div class="col-lg-3">
            <div class="form-group">
              <label class="form-control-label">Ciclo:</label>
              <select
                class="form-control"
                name="ciclo_id"
                id="ciclo_id"
                v-model="model.ciclo_id"
                @focus="$parent.clearErrorMsg($event)"
              >
                <option value="">Seleccionar</option>
                <option
                  v-for="ciclo in ciclos"
                  :value="ciclo.id"
                  v-bind:key="ciclo.id"
                >
                  {{ ciclo.año + " - " + ciclo.mes }}
                </option>
              </select>
              <div id="ciclo_id-error" class="error invalid-feedback"></div>
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
    <!--end::Form-->
  </div>
  <!--end::Portlet-->
</template>

<script>
import EventBus from "../event-bus";

export default {
  props: {
    companies: {
      type: Array,
      default: "",
    },

    areas: {
      type: Array,
      default: "",
    },
    ciclos: {
      // Añadido
      type: Array,
      default: "",
    },
    url: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      model: {
        company_id: "",
        area_id: "",
        ciclo_id: ''
      },
    };
  },
  created() {},
  mounted() {
    this.newSelect2();
  },
  watch: {},
  computed: {},
  methods: {
    newSelect2: function () {
      let vm = this;
      let token = document.head.querySelector(
        'meta[name="csrf-token"]'
      ).content;
      $("#article_id")
        .select2({
          placeholder: "Buscar",
          allowClear: true,
          language: {
            noResults: function () {
              return "No hay resultados";
            },
            searching: function () {
              return "Buscando...";
            },
            inputTooShort: function () {
              return "Ingresa 1 o más caracteres";
            },
            errorLoading: function () {
              return "No se pudo cargar la información";
            },
          },
          minimumInputLength: 0,
        })
        .on("select2:select", function (e) {
          var selected_element = $(e.currentTarget);
          vm.model.article_id = parseInt(selected_element.val());
        })
        .on("select2:unselect", function (e) {
          vm.model.article_id = "";
        });
    },
    formController: function (url, event) {
      var vm = this;

      var target = $(event.target);
      var url = url;
      var fd = new FormData(event.target);

      EventBus.$emit("loading", true);

      axios
        .post(url, fd, {
          headers: {
            "Content-type": "application/x-www-form-urlencoded",
          },
        })
        .then((response) => {
          EventBus.$emit("show_table", response.data);
        })
        .catch((error) => {
          EventBus.$emit("loading", false);
          console.log(error.response);
          var obj = error.response.data.errors;
          $("html, body").animate(
            {
              scrollTop: 0,
            },
            500,
            "swing"
          );
          $.each(obj, function (i, item) {
            // console.log(target);
            let c_target = target.find("#" + i + "-error");
            let p = c_target.parents(".form-group").find("#" + i);
            p.addClass("is-invalid");
            c_target.css("display", "block");
            c_target.html(item);
          });
        });
    },
  },
};
</script>