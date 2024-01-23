<template>
  <div>
    <!--begin::Liquidation Modal-->
    <div
      class="modal fade"
      id="modal-liquidation"
      tabindex="-1"
      role="dialog"
      aria-labelledby="liquidationModal"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="liquidationModal">{{ title_text }}</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="kt-portlet__body">
              <div class="row">
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Condición de Pago:</label>
                    <select
                      class="form-control"
                      name="payment_id"
                      id="payment_id"
                      v-model="model.payment_id"
                      @focus="$parent.clearErrorMsg($event)"
                      v-on:change="checkPayment"
                    >
                      <option value="">Seleccionar</option>
                      <option
                        v-for="payment in payments"
                        :value="payment.id"
                        v-bind:key="payment.id"
                      >
                        {{ payment.name }}
                      </option>
                    </select>
                    <div
                      id="payment_id-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Forma de Pago:</label>
                    <select
                      class="form-control"
                      name="payment_method_id"
                      id="payment_method_id"
                      v-model="model.payment_method"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                      <option value="">Seleccionar</option>
                      <option
                        v-for="payment_method in filteredPaymentMethods"
                        :value="payment_method.id"
                        v-bind:key="payment_method.id"
                      >
                        {{ payment_method.name }}
                      </option>
                    </select>

                    <div
                      id="payment_method_id-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="model.payment_method == 9">
                  <div class="form-group">
                    <label class="form-control-label">Sede:</label>
                    <select
                      class="form-control"
                      name="payment_sede"
                      id="payment_sede"
                      v-model="model.payment_sede"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                      <option value="ATE">ATE</option>
                      <option value="CALLAO">CALLAO</option>
                      <option value="COLONIAL">COLONIAL</option>
                    </select>
                    <div
                      id="payment_sede-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="model.payment_method == 9">
                  <div class="form-group">
                    <label class="form-control-label">Nº de Hermeticase:</label>
                    <input
                      type="text"
                      class="form-control"
                      name="operation_number"
                      id="operation_number"
                      v-model="model.operation_number"
                      @focus="$parent.clearErrorMsg($event)"
                      v-on:change="manageOperationNumber"
                    />
                    <div
                      id="operation_number-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="model.payment_method == 10">
                  <div class="form-group">
                    <label class="form-control-label">Saldo a Favor:</label>
                    <select
                      class="form-control"
                      name="saldo_favor_id"
                      id="saldo_favor_id"
                      v-model="model.saldo_favor_id"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                      <option value="">Seleccionar</option>
                      <option
                        v-for="saldo_favor in saldos_favor"
                        :value="saldo_favor.id"
                        v-bind:key="saldo_favor.id"
                      >
                        {{ saldo_favor.name }}
                      </option>
                    </select>
                    <div
                      id="saldo_favor_id-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Moneda:</label>
                    <select
                      class="form-control"
                      name="currency_id"
                      id="currency_id"
                      v-model="model.currency"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                      <option value="">Seleccionar</option>
                      <option
                        v-for="currency in currencies"
                        :value="currency.id"
                        v-bind:key="currency.id"
                      >
                        {{ currency.name }}
                      </option>
                    </select>
                    <div
                      id="currency_id-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div
                  class="col-lg-3"
                  v-if="model.currency != '' && model.currency != 1"
                >
                  <div class="form-group">
                    <label class="form-control-label">Tipo de Cambio:</label>
                    <input
                      type="number"
                      class="form-control"
                      name="exchange_rate"
                      id="exchange_rate"
                      min="0"
                      v-model="model.exchange_rate"
                      @focus="$parent.clearErrorMsg($event)"
                    />
                    <div
                      id="exchange_rate-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div
                  class="col-lg-3"
                  v-if="model.payment_method == 2 || model.payment_method == 3"
                >
                  <div class="form-group">
                    <label class="form-control-label">Banco:</label>
                    <select
                      class="form-control"
                      name="bank_account_id"
                      id="bank_account_id"
                      v-model="model.bank_account"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                      <option value="">Seleccionar</option>
                      <option
                        v-for="bank_account in bank_accounts"
                        :value="bank_account.id"
                        v-bind:key="bank_account.id"
                      >
                        {{ bank_account.name }}
                      </option>
                    </select>
                    <div
                      id="bank_account_id-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div
                  class="col-lg-3"
                  v-if="
                    model.payment_method == 2 ||
                    model.payment_method == 3 ||
                    model.payment_method == 11
                  "
                >
                  <div class="form-group">
                    <label class="form-control-label">Nº de Operación:</label>
                    <input
                      type="text"
                      class="form-control"
                      name="operation_number"
                      id="operation_number"
                      v-model="model.operation_number"
                      @focus="$parent.clearErrorMsg($event)"
                      v-on:change="manageOperationNumber"
                    />
                    <div
                      id="operation_number-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div
                  class="col-lg-3"
                  v-if="
                    model.payment_method == 9 ||
                    model.payment_method == 3 ||
                    model.payment_method == 2 ||
                    model.payment_method == 11
                  "
                >
                  <div class="form-group">
                    <label class="form-control-label">Fecha de Pago:</label>
                    <datetime
                      v-model="model.payment_date"
                      placeholder="Selecciona una Fecha"
                      :format="'dd-LL-yyyy'"
                      input-id="since_date"
                      name="since_date"
                      value-zone="America/Lima"
                      zone="America/Lima"
                      class="form-control"
                      @focus="$parent.clearErrorMsg($event)"
                    >
                    </datetime>
                    <div
                      id="payment_date-error"
                      class="error invalid-feedback"
                    ></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Monto:</label>
                    <input
                      type="number"
                      class="form-control"
                      name="amount"
                      id="amount"
                      min="0"
                      v-model="model.amount"
                      @focus="$parent.clearErrorMsg($event)"
                    />
                    <div id="amount-error" class="error invalid-feedback"></div>
                  </div>
                </div>
              </div>
              <!-- <div class="row">
                <div class="col-12 text-right">
                  <button
                    id="add_payment"
                    type="submit"
                    class="btn btn-success"
                    @click.prevent="addLiquidation(model)"
                    :disabled="model.payment_method == 2"
                  >
                    Agregar
                  </button>
                  <button
                    type="button"
                    class="btn btn-secondary"
                    @click.prevent="resetLiquidation()"
                  >
                    Cancelar
                  </button>
                  <div
                    class="kt-separator kt-separator--space kt-separator--dashed"
                  ></div>
                </div>
              </div> -->
              <!-- <div class="row">
                <div class="col-12">
                  <table class="table table-vertical-middle table-layout-fixed">
                    <thead>
                      <tr>
                        <th>Forma Pago</th>
                        <th>Moneda</th>
                        <th>Tipo Cambio</th>
                        <th>Banco</th>
                        <th>Nº Ope.</th>
                        <th style="text-align: right; width: 120px">Monto</th>
                        <th style="text-align: right">Opciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="(item, index) in liquidations"
                        v-bind:key="index"
                      >
                        <td>{{ item.payment_method.name }}</td>
                        <td>{{ item.currency.name }}</td>
                        <td>{{ item.exchange_rate }}</td>
                        <td>{{ item.bank_account.name }}</td>
                        <td>{{ item.operation_number }}</td>
                        <td style="text-align: right; width: 120px">
                          {{ item.amount }}
                        </td>
                        <td style="text-align: right">
                          <a
                            href="#"
                            class="btn-sm btn btn-label-danger btn-bold"
                            @click.prevent="removeLiquidation(index)"
                          >
                            <i class="la la-trash-o pr-0"></i>
                          </a>
                        </td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td
                          style="
                            text-align: right;
                            width: 120px;
                            font-weight: 600;
                          "
                        >
                          {{ addTotals }}
                        </td>
                        <td style="text-align: right"></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div> -->
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="submit"
              class="btn btn-success"
              @click.prevent="addLiquidations()"
            >
              Crear
            </button>
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!--end::Modal-->
  </div>
</template>

<script>
import Swal from "sweetalert2";
import EventBus from "../event-bus";
export default {
  props: {
    payment_methods: {
      type: Array,
      default: "",
    },
    currencies: {
      type: Array,
      default: "",
    },
    url_get_bank_accounts: {
      type: String,
      default: "",
    },
    url_get_saldo_favor: {
      type: String,
      default: "",
    },
    payments: {
      type: Array,
      default: [],
    },
    payment_cash: {
      type: Number,
      default: "",
    },
    payment_credit: {
      type: Number,
      default: "",
    },
  },
  data() {
    return {
      liquidation_datatable: undefined,
      model: {
        payment_method: "",
        currency: "",
        exchange_rate: "",
        bank_account: "",
        operation_number: "",
        amount: "",
        payment_date: "",
        saldo_favor_id: 0,
        payment_sede: "",
        initialAmount: "",
        previousPaymentMethod: null,
      },
      liquidations: [],
      bank_accounts: [],
      saldos_favor: [],
      total: "",
    };
  },
  created() {
    if (this.payments && this.payments.length > 0) {
      this.model.payment_id = this.payments[0].id;
    }
  },

  mounted() {
    document.getElementById("amount").disabled = false;

    EventBus.$on(
      "liquidation_modal",
      function () {
        let vm = this;

        this.model.amount = this.$store.state.sale.total_perception;
        this.model.payment_method = "";
        this.model.currency = "";
        this.model.exchange_rate = "";
        this.model.bank_account = "";
        this.model.operation_number = "";

        this.initialAmount = this.model.amount;

        if (this.payment_methods && this.payment_methods.length > 0) {
          this.model.payment_method = this.payment_methods[0].id;
        }
        if (this.currencies && this.currencies.length > 0) {
          this.model.currency = this.currencies[0].id;
        }
        $("#modal-liquidation").modal("show");
      }.bind(this)
    );
  },
  watch: {
    "model.currency": function (val) {
      if (this.model.payment_method == 2 || this.model.payment_method == 3) {
        EventBus.$emit("loading", true);

        axios
          .post(this.url_get_bank_accounts, {
            company_id: this.$store.state.model.company_id,
            currency_id: val,
          })
          .then((response) => {
            // console.log(response);
            this.model.bank_account = "";
            this.bank_accounts = response.data;

            EventBus.$emit("loading", false);
          })
          .catch((error) => {
            console.log(error);
            console.log(error.response);
          });
      }
    },
    // "model.payment_method": function (val) {
    //   document.getElementById("amount").disabled = false;
    //   if (val.id == 2 || val.id == 3) {
    //     EventBus.$emit("loading", true);

    //     axios
    //       .post(this.url_get_bank_accounts, {
    //         company_id: this.$store.state.model.company_id,
    //         currency_id: this.model.currency.id,
    //       })
    //       .then((response) => {
    //         // console.log(response);
    //         this.model.bank_account = "";
    //         this.bank_accounts = response.data;

    //         EventBus.$emit("loading", false);
    //       })
    //       .catch((error) => {
    //         console.log(error);
    //         console.log(error.response);
    //       });
    //   }
    // },
    "model.payment_method": function (val, oldVal) {
      this.previousPaymentMethod = oldVal;

      this.handleChangePaymentMethod();
      document.getElementById("amount").disabled = false;
      document.getElementById("currency_id").disabled = false;

      this.model.saldo_favor_id = 0;
      if (val == 10) {
        axios
          .post(this.url_get_saldo_favor, {
            client_id: this.$store.state.sale.client_id,
          })
          .then((res) => {
            const { data } = res;

            this.saldos_favor = data;
          })
          .catch((err) => {
            Swal.fire({
              title: "¡Error!",
              text: "El cliente no cuenta con saldo a favor.",
              type: "error",
              heightAuto: false,
            });

            console.log(err);
            console.log(err.response);
          });
      }
    },
    "model.saldo_favor_id": function (val) {
      const saldo = this.saldos_favor.find((item) => item.id == val);

      if (saldo) {
        this.model.amount = saldo.total_perception;
        this.model.currency = saldo.currency_id;
        document.getElementById("amount").disabled = true;
        document.getElementById("currency_id").disabled = true;
      }
    },
  },
  computed: {
    filteredPaymentMethods() {
      // Acceder a warehouse_document_type_id desde el store
      const warehouseDocumentTypeId =
        this.$store.state.warehouse_document_type_id;
      let methods = this.payment_methods;

      if (warehouseDocumentTypeId === 32) {
        methods = this.payment_methods.filter((method) => method.id === 12);
      }

      
      this.model.payment_method = methods[0].id;


      return methods;
    },

    title_text() {
      if (this.model && this.model.amount) {
        return `${this.$store.state.sale.client_name} - Total: ${this.model.amount}`;
      }
      return "";
    },

    addTotals() {
      return this.liquidations.reduce(
        (a, { amount }) => Number(a) + Number(amount),
        0
      );
    },
  },
  methods: {
    handleChangePaymentMethod() {
      const bonificacionDonaId = 12; // ID de "Bonificación - Dona"
      if (this.model.payment_method == bonificacionDonaId) {
        this.model.amount = 0.1;
        EventBus.$emit('paymentMethodChanged', bonificacionDonaId);
      } else if (this.previousPaymentMethod == bonificacionDonaId) {
        // Restablece solo si el anterior era 12
        this.model.amount = this.initialAmount;
      }
    },

    addLiquidation: function () {
      let liquidation = JSON.parse(JSON.stringify(this.model));
      let text = "";

      if (
        liquidation.payment_method == "" &&
        liquidation.payment_id == this.payment_cash
      ) {
        text = "Debe seleccionar una Forma de Pago.";
      } else if (
        liquidation.currency == "" &&
        liquidation.payment_id == this.payment_cash
      ) {
        text = "Debe seleccionar una Moneda.";
      } else if (
        (liquidation.payment_method == 2 || liquidation.payment_method == 3) &&
        liquidation.bank_account == ""
      ) {
        text = "Debe seleccionar un Banco.";
      } else if (
        (liquidation.payment_method == 2 || liquidation.payment_method == 3) &&
        liquidation.operation_number == ""
      ) {
        text = "El Nº de Operación es obligatorio.";
      } else if (
        (liquidation.currency == 2 || liquidation.currency == 3) &&
        liquidation.exchange_rate == ""
      ) {
        text = "El Tipo de Cambio es obligatorio.";
      } else if (
        (liquidation.amount == "" || liquidation.amount <= 0) &&
        liquidation.payment_id == this.payment_cash
      ) {
        text = "El Monto es obligatorio y debe ser mayor a 0.";
      } else if (
        this.$store.state.sale.payment_id != this.payment_credit &&
        liquidation.payment_id == this.payment_credit
      ) {
        text = "El cliente no cuenta con crédito disponible";
      }

      if (text != "") {
        Swal.fire({
          title: "¡Error!",
          text: text,
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else {
        liquidation.exchange_rate =
          liquidation.exchange_rate != ""
            ? accounting.toFixed(liquidation.exchange_rate, 4)
            : "";
        liquidation.amount = accounting.toFixed(liquidation.amount, 4);

        const payment_method = this.payment_methods.filter(
          (item) => item.id === liquidation.payment_method
        )[0];
        const currency = this.currencies.filter(
          (item) => item.id === liquidation.currency
        )[0];
        const bank_account = this.bank_accounts.filter(
          (item) => item.id === liquidation.bank_account
        )[0];

        if (payment_method) {
          liquidation.payment_method = payment_method;
        }

        if (currency) {
          liquidation.currency = currency;
        }

        if (bank_account) {
          liquidation.bank_account = bank_account;
        }

        if (liquidation.payment_id != 2) {
          this.liquidations.push(liquidation);
        }

        this.model.payment_method = "";
        this.model.currency = "";
        this.model.exchange_rate = "";
        this.model.bank_account = "";
        this.model.operation_number = "";
        this.model.amount = "";
        this.model.payment_date = "";
      }
    },
    resetLiquidation: function () {
      this.model.payment_method = "";
      this.model.currency = "";
      this.model.exchange_rate = "";
      this.model.bank_account = "";
      this.model.operation_number = "";
      this.model.amount = "";
    },
    removeLiquidation: function (index) {
      this.liquidations.splice(index, 1);
    },
    addLiquidations: function () {
      // fix
      // if ( this.$store.state.sale.payment_id !== 2 && accounting.unformat(this.addTotals) !== accounting.unformat(this.$store.state.sale.total_perception)) {
      // 	error = 1;
      // 	Swal.fire({
      //         title: '¡Error!',
      //         text: 'Debe liquidar por el total de la Venta.',
      //         type: "error",
      //         heightAuto: false,
      //         showCancelButton: false,
      //         confirmButtonText: 'Ok',
      //     });
      // }

      // if ( this.$store.state.sale.payment_id == 2 && accounting.unformat(this.addTotals) > accounting.unformat(this.$store.state.sale.total_perception)) {
      // 	error = 1;
      // 	Swal.fire({
      //         title: '¡Error!',
      //         text: 'El Pago a cuenta no puede exceder el Total de la Venta.',
      //         type: "error",
      //         heightAuto: false,
      //         showCancelButton: false,
      //         confirmButtonText: 'Ok',
      //     });
      // }

      // if ( this.$store.state.sale.payment_id !== 2 && this.liquidations < 1 ) {
      // 	error = 1;
      //     Swal.fire({
      //         title: '¡Error!',
      //         text: 'Debe agregar al menos 1 Forma de Pago.',
      //         type: "error",
      //         heightAuto: false,
      //         showCancelButton: false,
      //         confirmButtonText: 'Ok',
      //     });
      // }

      // if ( this.model.payment_id == this.payment_credit && (this.$store.state.sale.total_perception - accounting.unformat(this.addTotals))> this.$store.state.sale.credit_limit ) {
      //     error = 1;
      //     Swal.fire({
      //         title: '¡Error!',
      //         text: 'La línea del crédito del cliente es insuficiente.',
      //         type: "error",
      //         heightAuto: false,
      //         showCancelButton: false,
      //         confirmButtonText: 'Ok',
      //     });
      // }  else if ((this.$store.state.sale.total_perception - accounting.unformat(this.addTotals)) > this.$store.state.sale.credit_limit) {
      //     error = 1;
      //     Swal.fire({
      //         title: '¡Error!',
      //         text: 'La línea del crédito del cliente es insuficiente.',
      //         type: "error",
      //         heightAuto: false,
      //         showCancelButton: false,
      //         confirmButtonText: 'Ok',
      //     });
      // }
      let error = 0;
      if (error == 0) {
        let liquidations = this.liquidations;
        let sale = this.$store.state.sale;

        sale.details.forEach((element) => {
          let article_id = element.article_id;
          let quantity = element.quantity;

          this.$store.commit("changeBalanceValue", {
            article_id,
            quantity,
          });
        });

        this.$store.commit("addLiquidations", liquidations);
        this.$store.commit("addSales");
        console.log(liquidations);
        EventBus.$emit("refresh_table_sale");

        this.$store.state.article_id = 0;

        this.liquidations = [];
        this.model.payment_method = "";
        this.model.currency = "";
        this.model.exchange_rate = "";
        this.model.bank_account = "";
        this.model.operation_number = "";
        this.model.amount = "";

        $("#modal-liquidation").modal("hide");

        EventBus.$emit("refresh_table_liquidation");
      }
    },
    checkPayment() {
      switch (this.model.payment_id) {
        case this.payment_cash:
          $("#modal-liquidation")
            .find("#payment_method_id")
            .prop("disabled", false);
          $("#modal-liquidation").find("#currency_id").prop("disabled", false);
          $("#modal-liquidation").find("#amount").prop("disabled", false);
          break;
        case this.payment_credit:
          $("#modal-liquidation")
            .find("#payment_method_id")
            .prop("disabled", true);
          $("#modal-liquidation").find("#currency_id").prop("disabled", true);
          $("#modal-liquidation").find("#amount").prop("disabled", true);
          break;
        default:
          $("#modal-liquidation")
            .find("#payment_method_id")
            .prop("disabled", false);
          $("#modal-liquidation").find("#currency_id").prop("disabled", false);
          $("#modal-liquidation").find("#amount").prop("disabled", false);
          break;
      }
    },
    manageOperationNumber() {
      if (
        this.model.payment_method === 1 ||
        this.model.payment_method === 2 ||
        this.model.payment_method === 3
      ) {
        EventBus.$emit("loading", true);
        $("#add_payment").prop("disabled", true);
        $("#operation_number-error").hide();
        $("#operation_number-error").text("");

        // El metodo no existe por eso devuelve un error y devuelve que nro de operacion ya ha sido usado aunque eso no deberia ser asi incluso con un error hay que crear el metodo en el controller de liquidacion final
        axios
          .get("/facturacion/liquidaciones-final/get-op-number", {
            params: {
              operation_number: this.model.operation_number,
              payment_method: this.model.payment_method,
              bank_account: this.model.bank_account,
            },
          })
          .then((response) => {
            console.log("bien");
            EventBus.$emit("loading", false);
            $("#add_payment").prop("disabled", false);
            $("#operation_number-error").text("");
            $("#operation_number-error").hide();
          })
          .catch((error) => {
            EventBus.$emit("loading", false);
            // $('#add_payment').prop('disabled', true);
            // $('#operation_number-error').text('El Nro. de Operación del Deposito ya fue usado anteriormente');
            // $('#operation_number-error').show();
          });
        // Esto solo se debe activar al validar que el nro de operacion sea unico
        $("#add_payment").prop("disabled", false);
        $("#operation_number-error").text("");
        $("#operation_number-error").hide();
      }
    },
  },
};
</script>