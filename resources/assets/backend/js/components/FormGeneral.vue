<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    {{ title_header }}
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <slot name="body_content"></slot>
            </div>
            <div class="kt-portlet__foot">
                <slot name="foot_content"></slot>
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
            title_header: {
                type: String,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
        },
        data() {},
        created() {},
        mounted() {},
        watch: {},
        computed: {},
        methods: {
            formController: function(url, event) {
                const target = $(event.target);
                const fd = new FormData(event.target);

                EventBus.$emit('loading', true);

                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('response_form', response.data);
                }).catch(error => {
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