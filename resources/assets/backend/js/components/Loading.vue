<template>
    <div class="loading" v-if="show">
        <div class="loader"></div>
    </div>
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
        },
        data() {
            return {
                show: false
            }
        },
        computed: {
        },
        created() {
            EventBus.$on('loading', function(data) {
                this.show = data;
            }.bind(this));
        },
    };
</script>
<style>
    .loading {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: rgba(0,0,0,0.8);
        z-index: 9999;
    }
    .loader {
        left: 50%;
        margin-left: -4em;
        font-size: 10px;
        border: .8em solid rgba(218, 219, 223, 1);
        border-left: .8em solid rgba(15, 179, 200, 1);
        animation: spin 1.1s infinite linear;
    }
    .loader, .loader:after {
        border-radius: 50%;
        width: 8em;
        height: 8em;
        display: block;
        position: absolute;
        top: 50%;
        margin-top: -4.05em;
    }

    @keyframes spin {
        0% {
            transform: rotate(360deg);
        }
        100% {
            transform: rotate(0deg);
      }
    }
</style>