<template>
  <div>
    <div class="kt-portlet" v-if="show_table">
      <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
          <h3 class="kt-portlet__head-title">Resultado de Boletas</h3>
        </div>
        <div class="kt-portlet__head-toolbar">
          <div class="kt-portlet__head-wrapper">
            <div class="dropdown dropdown-inline">
              <button type="button" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportPDF">
                <i class="fa fa-file-pdf"></i> Exportar a PDF
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="kt-portlet__body kt-portlet__body--fit">
        <div class="kt-datatable" id="kt_datatable"></div>
      </div>
    </div>
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
      show_table: false,
      model: {
        ciclo_id: '',
        empleado_id: '',
      },
      planilla_total_report_datatable: null,
    };
  },
  mounted() {
    this.initDataTable();
    EventBus.$on("show_table", (data) => {
      this.show_table = true;
      this.model = data;
      this.updateDataTable(data);
    });
  },
  methods: {
    initDataTable() {
      let vm = this;
      let token = document.head.querySelector('meta[name="csrf-token"]').content;

      this.planilla_total_report_datatable = $('#kt_datatable').KTDatatable({
        data: {
          type: 'remote',
          source: {
            read: {
              url: vm.url,
              params: {
                _token: token,
                model: vm.model,
                export: vm.export,
              },
            },
          },
          pageSize: 10,
        },
        layout: {
          scroll: true,
          height: 600,
          footer: false,
        },
        sortable: true,
        pagination: true,
        search: {
          input: $('#generalSearch'),
        },
        columns: [
          {
            field: 'empleado_nombre',
            title: 'Nombre del Empleado',
          },
          {
            field: 'ciclo',
            title: 'Ciclo',
          },
          {
            field: 'total_pagado',
            title: 'Total Pagado',
          },
        ],
      });
    },
    updateDataTable(data) {
      if (this.planilla_total_report_datatable) {
        this.planilla_total_report_datatable.setDataSource({
          type: 'local',
          data: data,
        });
        this.planilla_total_report_datatable.reload();
      }
    },
    exportPDF() {
      // Implementar la lógica para exportar las boletas a PDF
    },
  },
};
</script>
