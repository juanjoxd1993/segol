<template>
    <!--begin::Modal-->
    <div class="modal fade" id="guides-register-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="sendForm()">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar artículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control kt-select2" name="article_id" id="article_id"
                                            v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                            <option v-for="article in articles" :value="article.id"
                                                v-bind:key="article.id">
                                                {{ article.code }} - {{ article.name }}
                                            </option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad: (Stock: {{ Number(stock) }})</label>
                                        <input type="tel" class="form-control" name="quantity" id="quantity"
                                            placeholder="0" v-model="model.quantity"
                                            @focus="$parent.clearErrorMsg($event)">
                                        <div id="quantity-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="add_article_2" disabled>
                            <i class="fa fa-plus"></i> Agregar artículo
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>
    <!--end::Modal-->
</template>

<script>
import EventBus from '../event-bus';

export default {
    props: {
        articles: {
            type: Array,
            default: ''
        }
    },
    data() {
        return {
            model: {
                article_id: '',
                article_code: '',
                article_name: '',
                quantity: '',
                stock_good: '',
                convertion: '',
            },
            stock: ''
        }
    },
    watch: {
        'model.quantity': function (val) {
            if (this.model.article_id != '' && (Number(val) != '' && Number(val) > 0)) {
                $('input#quantity').removeClass('is-invalid');
                $('#quantity-error').html('');
                document.getElementById('add_article_2').disabled = false;
            } else {
                document.getElementById('add_article_2').disabled = true;
            }
        },
        'model.article_id': function (val) {
            if (val != '' && (this.model.quantity != '' || Number(this.model.quantity) > 0)) {
                document.getElementById('add_article_2').disabled = false;
            } else {
                document.getElementById('add_article_2').disabled = true;
            }

            const current_article = this.articles.find(article => article.id === val);

            if (current_article) {
                this.stock = current_article.stock_good;
            }
        }
    },
    computed: {
    },
    created() {
        EventBus.$on('guides_register_modal', function () {
            this.model.article_id = '';
            this.model.article_code = '';
            this.model.article_name = '';
            this.model.quantity = '';
            this.stock = 0;
            $('#guides-register-modal').modal('show');
        }.bind(this));

        EventBus.$on('reset_stock_register', function () {
            this.model.article_id = '';
            this.model.article_code = '';
            this.model.article_name = '';
            this.model.quantity = '';
            this.stock = 0;
            $('#guides-register-modal').modal('hide');
        }.bind(this));
    },
    mounted() {
    },
    methods: {
        sendForm: function () {

            if (Number(this.model.quantity) < 1 || this.model.quantity == '') {
                Swal.fire({
                    title: 'Error',
                    text: 'Cantidad inválida',
                    type: "error",
                    heightAuto: false,
                })
                return;
            }

            const current_article = this.articles.find(article => article.id === this.model.article_id);
            this.model.article_code = current_article.code;
            this.model.article_name = current_article.name;
            this.model.stock_good = current_article.stock_good;
            this.model.convertion = current_article.convertion;

            document.getElementById('add_article_2').disabled = true;
            EventBus.$emit('sendForm', this.model);
        }
    }
};
</script>