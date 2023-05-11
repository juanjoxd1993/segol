<template>
    <!--begin::Modal-->
    <div class="modal fade" id="guides-register-modal-article" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="sendForm()">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Editar artículo: {{ article.name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cesion de Uso</label>
                                        <input type="tel" class="form-control" name="cesion" id="cesion" placeholder="0" v-model="cesion" @focus="$parent.clearErrorMsg($event)">
                                        <div id="cesion-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Prestamo</label>
                                        <input type="tel" class="form-control" name="press" id="press" placeholder="0" v-model="press" @focus="$parent.clearErrorMsg($event)">
                                        <div id="press-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="edit_article_2">
                            <i class="fa fa-check"></i> Guardar
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
        },
        data() {
            return {
                cesion: 0,
                press: 0,
                article: {},
                index: 0,
                id: 0,
                item_number: 0,
            }
        },
        watch: {

        },
        computed: {

        },
        created() {
            EventBus.$on('guides_register_modal_article', function(item_number, id, index, article) {
                this.article = article;
                this.index = index;
                this.id = id;
                this.item_number = item_number;

                $('#guides-register-modal-article').modal('show');
            }.bind(this));
        },
        mounted() {

        },
        methods: {
            sendForm: function() {
                const index = this.index;
                const id = this.id;
                const cesion = this.cesion;
                const press = this.press;

                $('#guides-register-modal-article').modal('hide');

                this.index = 0;
                this.id = 0;
                this.cesion = 0;
                this.press = 0;
                this.article = {};

                EventBus.$emit('sendEditArticle', index, id, cesion, press);
            }
        }
    };
</script>