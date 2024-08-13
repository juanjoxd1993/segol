<template>
  <div>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-sale" tabindex="-1" role="dialog" aria-labelledby="modalSaleLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalSaleLabel">
              {{ button_text }} venta
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="kt-portlet__body">
              <div class="row">
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Cliente:</label>
                    <select class="form-control" name="client_id" id="client_id" v-model="sale.client_id"
                      @focus="$parent.clearErrorMsg($event)">
                      <option value="0">Seleccionar</option>
                      <option v-for="client in clients" v-bind:key="client.id" :value="client.id">
                        {{ client.business_name }}
                      </option>
                    </select>
                    <div id="client_id-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Tipo Referencia:</label>
                    <select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id"
                      v-model="sale.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                      <option value="">Seleccionar</option>
                      <option v-for="warehouse_document_type in warehouse_document_types"
                        :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">
                        {{ warehouse_document_type.name }}
                      </option>
                    </select>
                    <div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Serie de Usuario:</label>
                    <select class="form-control" name="sale_serie_id" id="sale_serie_id" v-model="sale.sale_serie_id"
                      @focus="$parent.clearErrorMsg($event)">
                      <option value="0">Seleccionar</option>
                      <option v-for="sale_serie in sale_series" :value="sale_serie.id" v-bind:key="sale_serie.id">
                        {{ sale_serie.num_serie }}
                      </option>
                    </select>
                    <div id="sale_serie_id-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <!-- <div class="col-lg-3" v-if="this.sale.warehouse_document_type_id >= 4 && this.sale.warehouse_document_type_id <= 9"> -->
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Correlativo:</label>
                    <input type="text" readonly class="form-control" name="referral_serie_number"
                      id="referral_serie_number" v-model="sale.referral_serie_number"
                      @focus="$parent.clearErrorMsg($event)" />
                    <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="
                  this.sale.warehouse_document_type_id == 4 ||
                  this.sale.warehouse_document_type_id == 6 ||
                  this.sale.warehouse_document_type_id == 8
                ">
                  <div class="form-group">
                    <label class="form-control-label">Número de Referencia:</label>
                    <input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number"
                      v-model="sale.referral_voucher_number" @focus="$parent.clearErrorMsg($event)" />
                    <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="
                  this.sale.warehouse_document_type_id == 5 ||
                  this.sale.warehouse_document_type_id == 7 ||
                  this.sale.warehouse_document_type_id == 9 ||
                  this.sale.warehouse_document_type_id == 17
                ">
                  <div class="form-group">
                    <label class="form-control-label">Serie de Guía:</label>
                    <input type="text" class="form-control" name="referral_guide_series" id="referral_guide_series"
                      v-model="sale.referral_guide_series" @focus="$parent.clearErrorMsg($event)" />
                    <div id="referral_guide_series-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="
                  this.sale.warehouse_document_type_id == 5 ||
                  this.sale.warehouse_document_type_id == 7 ||
                  this.sale.warehouse_document_type_id == 9 ||
                  this.sale.warehouse_document_type_id == 17
                ">
                  <div class="form-group">
                    <label class="form-control-label">Número de Guía:</label>
                    <input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number"
                      v-model="sale.referral_guide_number" @focus="$parent.clearErrorMsg($event)"
                      @input="manageNumberGuide" />
                    <div id="referral_guide_number-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3" v-if="
                  this.sale.warehouse_document_type_id === 4 ||
                  this.sale.warehouse_document_type_id === 5 ||
                  this.sale.warehouse_document_type_id === 18
                ">
                  <div class="form-group">
                    <label class="form-control-label">Nº SCOP:</label>
                    <input type="text" class="form-control" v-model="sale.scop_number" name="scop_number"
                      id="scop_number" @focus="$parent.clearErrorMsg($event)" @input="manageScopNumber" />
                    <div id="scop_number-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Moneda:</label>
                    <select class="form-control" name="currency_id" id="currency_id" v-model="sale.currency_id"
                      @focus="$parent.clearErrorMsg($event)">
                      <option value="">Seleccionar</option>
                      <option v-for="currency in currencies" :value="currency.id" v-bind:key="currency.id">
                        {{ currency.name }}
                      </option>
                    </select>
                    <div id="currency_id-error" class="error invalid-feedback"></div>
                  </div>
                </div>
              </div>
              <div class="row align-items-end">
                <div class="col-12">
                  <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                </div>
                <div class="col-12">
                  <div class="kt-section">
                    <div class="kt-section__title">Artículos</div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Artículo:</label>
                    <select class="form-control" name="article_id" id="article_id" v-model="model.article_id"
                      @change="getArticlePrice()" @focus="$parent.clearErrorMsg($event)">
                      <option value="">Seleccionar</option>
                      <option v-for="article in filterArticles" :value="article.id" v-bind:key="article.id">
                        {{ article.name }}
                      </option>
                    </select>
                    <div id="article_id-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Precio:</label>
                    <input type="text" readonly class="form-control" name="price_igv" id="price_igv"
                      v-model="model.price_igv" @focus="$parent.clearErrorMsg($event)" />
                    <div id="price_igv-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label class="form-control-label">Cantidad:</label>
                    <input type="number" class="form-control" name="quantity" id="quantity" v-model="model.quantity"
                      @focus="$parent.clearErrorMsg($event)" />
                    <div id="quantity-error" class="error invalid-feedback"></div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <button type="submit" class="btn btn-success" @click.prevent="addArticle()">
                      Agregar
                    </button>
                    <button type="button" class="btn btn-secondary">
                      Cancelar
                    </button>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                  <table class="table table-vertical-middle">
                    <thead>
                      <tr>
                        <th>Artículo</th>
                        <th style="text-align: right; width: 110px">Precio</th>
                        <th style="text-align: right; width: 110px">
                          Cantidad
                        </th>
                        <th style="text-align: right; width: 110px">
                          Valor Venta
                        </th>
                        <th style="text-align: right; width: 110px">
                          Percepción
                        </th>
                        <th style="text-align: right; width: 110px">Total</th>
                        <th style="text-align: right; width: 80px"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in setDetails" v-bind:key="index">
                        <td>{{ item.article_name }}</td>
                        <td style="text-align: right; width: 110px">
                          {{ item.price_igv }}
                        </td>
                        <td style="text-align: right; width: 110px">
                          {{ item.quantity }}
                        </td>
                        <td style="text-align: right; width: 110px">
                          {{ item.sale_value }}
                        </td>
                        <td style="text-align: right; width: 110px">
                          {{ item.igv_perception }}
                        </td>
                        <td style="text-align: right; width: 110px">
                          {{ item.total_perception }}
                        </td>
                        <td style="text-align: right; width: 80px">
                          <a href="#" class="btn-sm btn btn-label-danger btn-bold"
                            @click.prevent="removeArticle(index)">
                            <i class="la la-trash-o pr-0"></i>
                          </a>
                        </td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td></td>
                        <td style="
                            text-align: right;
                            width: 110px;
                            font-weight: 600;
                          "></td>
                        <td style="
                            text-align: right;
                            width: 110px;
                            font-weight: 600;
                          "></td>
                        <td style="
                            text-align: right;
                            width: 110px;
                            font-weight: 600;
                          ">
                          {{ sale.total }}
                        </td>
                        <td style="
                            text-align: right;
                            width: 110px;
                            font-weight: 600;
                          ">
                          {{ sale.perception }}
                        </td>
                        <td style="
                            text-align: right;
                            width: 110px;
                            font-weight: 600;
                          ">
                          {{ sale.total_perception }}
                        </td>
                        <td style="
                            text-align: right;
                            width: 80px;
                            font-weight: 600;
                          "></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success" @click.prevent="liquidationModal()">
              Liquidar
            </button>
            <button type="button" class="btn btn-secondary" @click.prevent="closeModal()">
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
import EventBus from "../event-bus";
export default {
  props: {
    warehouse_document_types: {
      type: Array,
      default: "",
    },
    currencies: {
      type: Array,
      default: "",
    },
    url_get_clients: {
      type: String,
      default: "",
    },
    url_get_article_price: {
      type: String,
      default: "",
    },
    url_get_sale_series: {
      type: String,
      default: "",
    },
    url_get_articles_clients: {
      type: String,
      default: "",
    },
    url_verify_document_type: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      button_text: "",
      model: {
        article_id: "",
        article_name: "",
        price_igv: "",
        quantity: "",
        igv: "",
        perception: "",
        sale_value: "",
        igv_perception: "",
        total_perception: "",
      },
      sale: {
        client_id: 0,
        client_name: "",
        document_type_id: "",
        warehouse_document_type_id: "",
        warehouse_document_type_name: "",
        referral_serie_number: "",
        referral_voucher_number: "",
        referral_guide_series: "",
        referral_guide_number: "",
        details: [],
        perception_percentage: "",
        total: "",
        perception: "",
        total_perception: "",
        payment_id: "",
        currency_id: 1,
        credit_limit: "",
        sale_serie_id: "",
        serie_num: "",
        correlative: "",
        scop_number: "",
      },
      articles: {},
      filterArticles: [],
      edit_flag: false,
      sale_series: [],
      clients: [],
    };
  },
  created() { },
  mounted() {
    // this.newSelect2();
    EventBus.$on("paymentMethodChanged", (paymentMethodId) => {
      if (paymentMethodId === 12) {
        // Aquí ajustas los valores como necesites
        this.adjustValuesForPaymentMethod12();
      }
    });

    EventBus.$on(
      "create_modal",
      async function () {
        document.getElementById("client_id").disabled = false;
        if (!this.clients.length) {
        }
        this.clients = this.$store.state.clients;
        const warehouse_movement_id =
          this.$store.state.model.warehouse_movement_id;

        let vm = this;
        const clients = [];

        const promises = await this.clients.map(async (item) => {
          const { id } = item;

          await axios
            .post(this.url_get_articles_clients, {
              client_id: id,
              warehouse_movement_id,
            })
            .then((response) => {
              let data = response.data;
              let articles = [];
              let filterArticles = [];

              data.map((item) => {
                const art = articles.find((i) => i.id == item.id);

                if (art) {
                  const artindex = articles.findIndex((i) => i.id == item.id);

                  articles[artindex].quantity =
                    articles[artindex].quantity + item.quantity;
                } else {
                  articles.push(item);
                }
              });

              const sales = this.$store.state.sales;
              const articlesRepitedFilter = [];

              if (sales.length) {
                sales.map((item) => {
                  item.details.map((i) => {
                    const obj = {
                      client_id: item.client_id,
                      article_id: i.article_id,
                      quantity: parseInt(i.quantity),
                    };

                    filterArticles.push(obj);
                  });
                });
              }

              if (filterArticles.length) {
                filterArticles.map((item) => {
                  const art = articlesRepitedFilter.find(
                    (i) =>
                      i.article_id == item.article_id &&
                      i.client_id == item.client_id
                  );

                  if (art) {
                    const artIndex = articlesRepitedFilter.findIndex(
                      (i) =>
                        i.article_id == item.article_id &&
                        i.client_id == item.client_id
                    );

                    articlesRepitedFilter[artIndex].quantity =
                      articlesRepitedFilter[artIndex].quantity + item.quantity;
                  } else {
                    articlesRepitedFilter.push(item);
                  }
                });

                filterArticles = articlesRepitedFilter;
                console.log(filterArticles);

                articles = articles.map((item) => {
                  const art = filterArticles.find(
                    (i) => i.article_id == item.id && i.client_id == id
                  );

                  if (art) {
                    item.quantity = item.quantity - art.quantity;
                  }

                  if (item.quantity > 0) {
                    return item;
                  }

                  return undefined;
                });
              }

              articles = articles.filter((item) => item !== undefined);

              if (articles.length) {
                clients.push(item);
              }

              vm.articles[id] = articles;
            })
            .catch((error) => {
              console.log(error);
              console.log(error.response);
            });
        });
        console.log(this.articles);

        Promise.all(promises);

        this.clients = clients;

        this.button_text = "Crear";
        this.sale.client_id = 0;
        this.sale.client_name = "";
        this.sale.document_type_id = "";
        this.sale.warehouse_document_type_id = "";
        this.sale.warehouse_document_type_name = "";
        this.sale.referral_serie_number = "";
        this.sale.referral_voucher_number = "";
        this.sale.referral_guide_series = "";
        this.sale.referral_guide_number = "";
        this.sale.details = [];
        this.sale.perception_percentage = "";
        this.sale.total = "";
        this.sale.perception = "";
        this.sale.total_perception = "";
        this.sale.payment_id = "";
        this.sale.currency_id = 1;
        this.sale.credit_limit = "";
        this.sale.sale_serie_id = "";
        this.sale.serie_num = "";
        this.sale.correlative = "";

        this.model = {
          article_id: "",
          article_name: "",
          price_igv: "",
          quantity: "",
          igv: "",
          perception: "",
          sale_value: "",
          igv_perception: "",
          total_perception: "",
        };

        $("#client_id").val(null).trigger("change");

        $("#modal-sale").modal("show");
      }.bind(this)
    );

    EventBus.$on("edit_modal", (data) => {
      this.edit_flag = true;
      this.button_text = "Actualizar";

      axios
        .post(this.url_get_clients, {
          client_id: this.sale.client_id,
        })
        .then((response) => {
          let option = new Option(
            response.data.text,
            response.data.id,
            true,
            true
          );
          $("#client_id").append(option).trigger("change");
          $("#client_id").trigger({
            type: "select2:select",
            params: {
              data: response.data,
            },
          });
        })
        .catch((error) => {
          console.log(error);
          console.log(error.response);
        });

      $("#modal-sale").modal("show");
    });
  },
  watch: {
    "sale.warehouse_document_type_id": function (val) {
      this.sale_series = [];

      const data = this.$store.state.sale_series.filter(
        (item) => item.warehouse_document_type_id === val
      );

      this.sale_series = data;

      let warehouse_document_type = this.warehouse_document_types.find(
        (element) => element.id == val
      );

      this.sale.warehouse_document_type_name = warehouse_document_type
        ? warehouse_document_type.name
        : "";

      this.$store.commit("SET_WAREHOUSE_DOCUMENT_TYPE_ID", val);
    },
    "sale.sale_serie_id": function (val) {
      let sale_serie = this.sale_series.find((element) => element.id == val);

      this.sale.referral_serie_number = sale_serie ? sale_serie.correlative : 0;
      this.sale.serie_num = sale_serie.num_serie;
      this.sale.correlative = sale_serie.correlative;
    },
    "sale.client_id": function (val) {
      const client_id = val;
      const warehouse_movement_id =
        this.$store.state.model.warehouse_movement_id;
      const client = this.clients.find((item) => item.id === val);
      this.$store.state.articles_filter = [];

      this.sale.client_id = client_id;
      this.sale.document_type_id = client.document_type_id;
      this.sale.client_name = client.business_name;
      this.sale.payment_id = client.payment_id;
      this.sale.credit_limit = client.credit_limit;

      const articles = this.articles[client_id];
      document.getElementById("client_id").disabled = true;

      this.filterArticles = articles;
      this.$store.state.articles_filter = articles;
    },
    "model.article_id": function (val) {
      const article = this.filterArticles.find((item) => item.id === val);

      if (article) {
        alert(this.model.quantity);
        alert(article.quantity);
        this.model.quantity = article.quantity;
      }
    },
  },
  computed: {
    setDetails() {
      let articles = this.$store.state.articles;
      let sale_article_ids = this.sale.details.map(
        (element) => element.article_id
      );
      // this.filterArticles = articles.filter(element => !sale_article_ids.includes(element.article_id));

      return this.sale.details;
    },
  },
  methods: {
    async getPerceptionData(clientId) {
      try {
        const response = await axios.get(
          `/facturacion/liquidaciones-final/${clientId}/calcular-percepcion`
        );
        return response.data.perception_percentage;
      } catch (error) {
        console.error("Error al obtener los datos de percepción", error);
        return 0;
      }
    },

    adjustValuesForPaymentMethod12() {
      this.sale.details.forEach((detail) => {
        detail.sale_value = 0.1;
        detail.igv_perception = 0;
        detail.total_perception = 0.1;
      });
      // Luego actualiza los totales si es necesario
      this.addTotals();
    },
    getArticlePrice: function () {
      if (this.model.article_id != "") {
        EventBus.$emit("loading", true);

        axios.post(this.url_get_article_price, {
          article_id: this.model.article_id,
          client_id: this.sale.client_id,
          warehouse_movement_id:
            this.$store.state.model.warehouse_movement_id,
        })
          .then((response) => {
            EventBus.$emit("loading", false);
            // console.log(response);

            this.model.price_igv = response.data.price_igv;
            this.model.igv = response.data.article.igv;
            this.model.perception = response.data.article.perception;
          })
          .catch((error) => {
            EventBus.$emit("loading", false);
            // console.log(error);
            // console.log(error.response);

            Swal.fire({
              title: "¡Error!",
              text: "El Artículo no cuenta con un precio para este Cliente.",
              type: "error",
              heightAuto: false,
              showCancelButton: false,
              confirmButtonText: "Ok",
            });
          });
      }
    },
    addArticle: async function () {
      const articleQuantity = this.filterArticles.find(
        (item) => item.id === this.model.article_id
      );
      let errorQuantity = false;
      const articlesFilter = [];
      const quantity = parseInt(this.model.quantity);

      this.filterArticles.map((item) => {
        if (item.id == this.model.article_id) {
          if (quantity <= 0) errorQuantity = true;
          if (quantity > item.quantity) errorQuantity = true;

          const newQuantity = item.quantity - quantity;

          if (newQuantity > 0)
            articlesFilter.push({
              ...item,
              quantity: newQuantity,
            });
        } else {
          articlesFilter.push(item);
        }
      });

      if (errorQuantity) {
        Swal.fire({
          title: "¡Error!",
          text: "Debe indicar una cantidad valida del producto.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (this.sale.client_id == "") {
        Swal.fire({
          title: "¡Error!",
          text: "Debe seleccionar un Cliente.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (this.model.article_id == "") {
        Swal.fire({
          title: "¡Error!",
          text: "Debe seleccionar un Artículo.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (
        this.model.price_igv == "" ||
        this.model.price_igv == undefined
      ) {
        Swal.fire({
          title: "¡Error!",
          text: "El Artículo no cuenta con un precio para este Cliente.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (quantity <= 0) {
        Swal.fire({
          title: "¡Error!",
          text: "La Cantidad no puede estar vacía o ser igual 0.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (quantity > articleQuantity.quantity) {
        Swal.fire({
          title: "¡Error!",
          text: `La Cantidad supera el Saldo del Artículo ( ${articleQuantity.quantity} ).`,
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else {
        let model = this.model;

        let price_igv = accounting.toFixed(model.price_igv, 4);
        let quantity = accounting.toFixed(model.quantity, 4);
        console.log(this.sale.client_id);
        const perception_percentage = await this.getPerceptionData(
          this.sale.client_id
        );
        console.log(perception_percentage);
        let sale_value = 0;
        let igv_perception = 0;
        let total_perception = 0;
        // Verificar si el tipo de referencia es 32 y ajustar los valores
        if (this.sale.warehouse_document_type_id == 32) {
          sale_value = 0.1;
          igv_perception = 0;
          total_perception = 0.1;
        } else {
          sale_value = accounting.toFixed(quantity * price_igv, 2);
          igv_perception = accounting.toFixed(
            sale_value * perception_percentage,
            4
          );
          total_perception = accounting.toFixed(
            Number(sale_value) + Number(igv_perception),
            4
          );
        }
        model.sale_value = sale_value;
        model.igv_perception = igv_perception;
        model.total_perception = total_perception;

        model.article_name = articleQuantity.name;
        model.price_igv = price_igv;
        model.quantity = quantity;

        const sale_serie_id = this.sale.sale_serie_id;
        const sale_serie_index = this.$store.state.sale_series.findIndex(
          (item) => item.id == sale_serie_id
        );
        if (sale_serie_index !== -1) {
          this.$store.state.sale_series[sale_serie_index].correlative += 1;
        } else {
          // Manejar el error o situación cuando no se encuentra el índice
        }

        this.sale.details.push(model);

        this.model = {
          article_id: "",
          article_name: "",
          price_igv: "",
          quantity: "",
          igv: "",
          perception: "",
          sale_value: "",
          igv_perception: "",
          total_perception: "",
        };

        this.filterArticles = articlesFilter;

        this.addTotals();
      }
    },

    removeArticle: function (index) {
      this.sale.details.splice(index, 1);
      this.addTotals();
    },
    addTotals: function () {
      if (this.sale.warehouse_document_type_id == 32) {
        // Si el tipo de referencia es 32, establece los totales a 0.1
        this.sale.total = 0.1;
        this.sale.perception = 0;
        this.sale.total_perception = 0.1;
      } else {
        // Cálculo normal de los totales
        this.sale.total = this.sale.details
          .reduce((a, { sale_value }) => Number(a) + Number(sale_value), 0)
          .toFixed(4);
        this.sale.perception = this.sale.details
          .reduce(
            (a, { igv_perception }) => Number(a) + Number(igv_perception),
            0
          )
          .toFixed(4);
        this.sale.total_perception = this.sale.details
          .reduce(
            (a, { total_perception }) => Number(a) + Number(total_perception),
            0
          )
          .toFixed(4);
      }
    },

    liquidationModal: function () {
      if (this.sale.warehouse_document_type_id == "") {
        Swal.fire({
          title: "¡Error!",
          text: "Debe seleccionar un Tipo de Referencia.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (
        (this.sale.warehouse_document_type_id == 4 ||
          this.sale.warehouse_document_type_id == 5) &&
        this.sale.document_type_id != 1
      ) {
        Swal.fire({
          title: "¡Error!",
          text: "El Cliente no cuenta con un Nº de RUC registrado.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (
        this.sale.warehouse_document_type_id != 4 &&
        this.sale.warehouse_document_type_id != 5 &&
        accounting.unformat(this.sale.perception) > 0
      ) {
        Swal.fire({
          title: "¡Error!",
          text: "La Percepción no puede ser mayor a 0 para este Tipo de Referencia.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (
        this.sale.warehouse_document_type_id >= 4 &&
        this.sale.warehouse_document_type_id <= 9 &&
        this.sale.referral_serie_number == ""
      ) {
        Swal.fire({
          title: "¡Error!",
          text: "La Serie de Referencia es obligatoria.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (
        (this.sale.warehouse_document_type_id == 4 ||
          this.sale.warehouse_document_type_id == 6 ||
          this.sale.warehouse_document_type_id == 8) &&
        this.sale.referral_voucher_number == ""
      ) {
        Swal.fire({
          title: "¡Error!",
          text: "El Número de Referencia es obligatorio.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else if (this.sale.details.length < 1) {
        Swal.fire({
          title: "¡Error!",
          text: "Debe agregar al menos 1 Artículo.",
          type: "error",
          heightAuto: false,
          showCancelButton: false,
          confirmButtonText: "Ok",
        });
      } else {
        EventBus.$emit("loading", true);

        this.$store.commit("addGuideNumber", {
          serie_number: this.sale.referral_guide_series,
          guide_number: this.sale.referral_guide_number,
        });

        this.$store.commit("addScop", this.sale.scop_number);

        axios
          .post(this.url_verify_document_type, {
            model: this.$store.state.model,
            warehouse_document_type_id: this.sale.warehouse_document_type_id,
            referral_serie_number: this.sale.referral_serie_number,
            referral_voucher_number: this.sale.referral_voucher_number,
          })
          .then((response) => {
            // console.log(response);
            if (response.data.verify == false) {
              EventBus.$emit("loading", false);

              Swal.fire({
                title: "¡Error!",
                text: response.data.msg,
                type: "error",
                heightAuto: false,
                showCancelButton: false,
                confirmButtonText: "Ok",
              });
            } else {
              EventBus.$emit("loading", false);

              let sale = this.sale;

              this.$store.commit("addSale", sale);
              $("#modal-sale").modal("hide");
              $("#modal-sale").on("hidden.bs.modal", function (e) {
                if (sale.details.length > 0) {
                  EventBus.$emit("liquidation_modal");
                }
              });

              this.model = {
                article_id: "",
                article_name: "",
                price_igv: "",
                quantity: "",
                igv: "",
                perception: "",
                sale_value: "",
                igv_perception: "",
                total_perception: "",
              };
            }
          })
          .catch((error) => {
            console.log(error);
            console.log(error.response);
          });
      }
    },
    closeModal: function () {
      this.model = {
        article_id: "",
        article_name: "",
        price_igv: "",
        quantity: "",
        igv: "",
        perception: "",
        sale_value: "",
        igv_perception: "",
        total_perception: "",
      };

      this.sale = {
        client_id: "",
        client_name: "",
        warehouse_document_type_id: "",
        warehouse_document_type_name: "",
        referral_serie_number: "",
        referral_voucher_number: "",
        details: [],
        perception_percentage: "",
        total: "",
        perception: "",
        total_perception: "",
        payment_id: "",
        currency_id: 1,
        credit_limit: "",
        sale_serie_id: "",
      };

      $("#modal-sale").modal("hide");
    },
    manageNumberGuide() {
      if (
        this.sale.warehouse_document_type_id == 5 ||
        this.sale.warehouse_document_type_id == 7 ||
        this.sale.warehouse_document_type_id == 9 ||
        this.sale.warehouse_document_type_id == 17
      ) {
        // EventBus.$emit('loading', true);
        $("#liquidar").prop("disabled", true);
        $("#referral_guide_number-error").hide();
        $("#referral_guide_number-error").text("");

        let find = false;

        if (this.$store.state.guide_numbers.length) {
          this.$store.state.guide_numbers.map((item) => {
            if (
              item.serie_number == this.sale.referral_guide_series &&
              item.guide_number == this.sale.referral_guide_number
            ) {
              EventBus.$emit("loading", false);
              $("#liquidar").prop("disabled", true);
              $("#referral_guide_number-error").text(
                "El Nro. de Guía ya fue usado anteriormente"
              );
              $("#referral_guide_number-error").show();

              find = true;
            }
          });
        }

        if (!find) {
          axios
            .post("/facturacion/liquidaciones-glp/get-guide-number", {
              params: {
                serie_number: this.sale.referral_guide_series,
                guide_number: this.sale.referral_guide_number,
              },
            })
            .then((response) => {
              // EventBus.$emit('loading', false);
              $("#liquidar").prop("disabled", false);
              $("#referral_guide_number-error").text("");
              $("#referral_guide_number-error").hide();
            })
            .catch((error) => {
              // EventBus.$emit('loading', false);
              $("#liquidar").prop("disabled", true);
              $("#referral_guide_number-error").text(
                "El Nro. de Guía ya fue usado anteriormente"
              );
              $("#referral_guide_number-error").show();
            });
        }
      }
    },
    manageScopNumber() {
      if (this.sale.scop_number.length > 11) {
        this.sale.scop_number = this.sale.scop_number.slice(0, 11);
      }
      if (
        !(this.sale.scop_number.length === 11 || this.sale.scop_number == "")
      ) {
        $("#liquidar").prop("disabled", true);
        $("#scop_number-error").text(
          "El Nro. de Scop tiene que ser de 11 digitos u omitirlo"
        );
        $("#scop_number-error").show();
        return;
      }
      if (
        this.sale.warehouse_document_type_id === 4 ||
        this.sale.warehouse_document_type_id === 5 ||
        this.sale.warehouse_document_type_id === 18
      ) {
        // EventBus.$emit('loading', true);
        $("#liquidar").prop("disabled", true);
        $("#scop_number-error").hide();
        $("#scop_number-error").text("");

        let find = false;

        if (this.$store.state.scops.length) {
          const exists = this.$store.state.scops.find(
            (item) => item == this.sale.scop_number
          );

          if (exists) {
            EventBus.$emit("loading", false);
            $("#liquidar").prop("disabled", true);
            $("#scop_number-error").text(
              "El Nro. de Scop ya fue usado anteriormente"
            );
            $("#scop_number-error").show();
            find = true;
          }
        }

        if (!find) {
          axios
            .post("/facturacion/liquidaciones-glp/get-scop-number", {
              params: {
                scop_number: this.sale.scop_number,
              },
            })
            .then((response) => {
              console.log("bien");
              // EventBus.$emit('loading', false);
              $("#liquidar").prop("disabled", false);
              $("#scop_number-error").text("");
              $("#scop_number-error").hide();
            })
            .catch((error) => {
              // EventBus.$emit('loading', false);
              $("#liquidar").prop("disabled", true);
              $("#scop_number-error").text(
                "El Nro. de Scop ya fue usado anteriormente"
              );
              $("#scop_number-error").show();
            });
        }
      }
    },
  },
};
</script>