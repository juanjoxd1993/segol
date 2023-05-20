<template>
    <div class="kt-form__actions">
        <div class="row">
            <div class="col-lg-3" v-for="button in buttons">
                <button
                    v-bind:type="button.type"
                    v-bind:class="button.class"
                >
                    {{ button.text }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            buttons: {
                type: Array,
                default: []
            },
        },
        data() {},
        created() {},
        mounted() {},
        watch: {},
        computed: {},
        methods: {}
    }
</script>