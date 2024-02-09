<template>
  <div>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table">
      <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
          <h3 class="kt-portlet__head-title">Resultado</h3>
        </div>
        <div class="kt-portlet__head-toolbar">
          <div class="kt-portlet__head-wrapper">
            <div class="dropdown dropdown-inline">
              <a
                href="#"
                class="btn btn-outline-brand btn-bold btn-sm"
                @click.prevent="exportExcel()"
              >
                <i class="fa fa-file-excel"></i> Exportar Excel
              </a>
            </div>
          </div>
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
import EventBus from "../event-bus";

export default {
  props: {
    url: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      planilla_total_report_datatable: undefined,
      show_table: false,
      model: {},
      export: "",
    };
  },
  created() {},
  mounted() {
    EventBus.$on(
      "show_table",
      function (response) {
        let vm = this;
        this.show_table = true;
        this.model = response;

        Vue.nextTick(function () {
          if (vm.planilla_total_report_datatable == undefined) {
            vm.fillTableX();
          } else {
            vm.planilla_total_report_datatable.setDataSourceParam(
              "model",
              vm.model
            );
            vm.planilla_total_report_datatable.load();
          }

          vm.planilla_total_report_datatable.on(
            "kt-datatable--on-ajax-done",
            function () {
              EventBus.$emit("loading", false);
            }
          );
        });
      }.bind(this)
    );

    EventBus.$on(
      "refresh_table",
      function () {
        if (this.planilla_total_report_datatable != undefined) {
          this.planilla_total_report_datatable.setDataSourceParam(
            "model",
            this.model
          );
          this.planilla_total_report_datatable.load();
        }
      }.bind(this)
    );
  },
  watch: {},
  computed: {},
  methods: {
    getMonthName: function (dateString) {
      // Asumiendo que la fecha viene en el formato "dd-mm-yyyy"
      const parts = dateString.split("-");
      const monthIndex = parseInt(parts[1], 10) - 1; // El índice del mes (0-11)
      const monthNames = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre",
      ];
      return monthNames[monthIndex];
    },

    fillTableX: function () {
      let vm = this;
      let token = document.head.querySelector(
        'meta[name="csrf-token"]'
      ).content;

      this.planilla_total_report_datatable = $(".kt-datatable").KTDatatable({
        // datasource definition
        data: {
          type: "remote",
          source: {
            read: {
              url: vm.url,
              params: {
                _token: token,
                model: vm.model,
                export: vm.export,
              },

              map: function (raw) {
                var dataSet = raw;
                if (typeof raw.data !== "undefined") {
                  dataSet = raw.data;
                }
                dataSet.map((element) => {
                  element.total = accounting.toFixed(element.total, 2);
                });

                return dataSet;
              },
            },
          },
          pageSize: 10,
        },

        // layout definition
        layout: {
          scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
          height: 600,
          footer: false, // display/hide footer
        },

        // column sorting
        sortable: true,
        pagination: false,

        search: {
          input: $("#generalSearch"),
        },

        translate: {
          records: {
            processing: "Espere porfavor...",
            noRecords: "No hay registros",
          },
          toolbar: {
            pagination: {
              items: {
                default: {
                  first: "Primero",
                  prev: "Anterior",
                  next: "Siguiente",
                  last: "Último",
                  more: "Más páginas",
                  input: "Número de página",
                  select: "Seleccionar tamaño de página",
                },
                info: "Mostrando {{start}} - {{end}} de {{total}} registros",
              },
            },
          },
        },

        rows: {
          autoHide: false,
        },

        // columns definition
        columns: [
          {
            field: "employ_name",
            title: "Empleados",
            width: 120,
            textAlign: "left",
          },

          {
            field: "sueldo",
            title: "Total a Pagar",
            width: 120,
            textAlign: "center",
          },

          {
            field: "id",
            title: "ID",
            width: 0,
            // overflow: 'hidden',
            // responsive: {
            //     hidden: 'sm',
            //     hidden: 'md',
            //     hidden: 'lg',
            //     hidden: 'xl'
            // }
          },
        ],
      });

      this.planilla_total_report_datatable.columns("id").visible(false);
    },
    exportExcel: function () {
      EventBus.$emit("loading", true);
      this.export = 1;

      axios
        .post(
          this.url,
          {
            model: this.model,
            export: this.export,
          },
          {
            responseType: "blob",
          }
        )
        .then((response) => {
          EventBus.$emit("loading", false);

          // const monthName = this.getMonthName(this.model.initial_date);
          const fileName = `planilla-${Date.now()}.xls`;

          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", fileName);
          document.body.appendChild(link);
          link.click();

          this.export = "";
        })
        .catch((error) => {
          console.log(error);
          EventBus.$emit("loading", false);
        });
    },
  },
};
</script>