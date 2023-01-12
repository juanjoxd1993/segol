<template>
    <!--begin::Modal-->
    <div class="modal fade" id="voucher-detail-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Detalle</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Valor venta</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(element, index) in element_details">
                                    <th class="text-center" scope="row">{{ ++index }}</th>
                                    <td>{{ element.detail_name }}</td>
                                    <td>{{ element.detail_price }}</td>
                                    <td>{{ element.detail_quantity }}</td>
                                    <td>{{ element.detail_subtotal }}</td>
                                    <td>{{ element.detail_igv }}</td>
                                    <td>{{ element.detail_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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
                title: '',
                element_details: []
            }
        },
        computed: {
        },
        created() {
            EventBus.$on('show_voucher_detail_modal', function(data) {
                console.log(data[0]);
                this.title = data[0].title;
                this.element_details = data[0].element_details;
                $('#voucher-detail-modal').modal('show');
            }.bind(this));
        },
    };
</script>