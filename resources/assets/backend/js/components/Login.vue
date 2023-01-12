<template>
    <form class="kt-form" @submit.prevent="$parent.formController(url,$event)">
        <div class="form-group">
            <input
                class="form-control"
                type="text"
                placeholder="Usuario"
                name="user"
                autocomplete="off"
                v-on="{ click: $parent.clearErrorMsg, focusin: $parent.clearErrorMsg }"
            >
            <div class="invalid-feedback user-error"></div>
        </div>
        <div class="form-group">
            <input
                class="form-control form-control-last"
                type="password"
                placeholder="Contraseña"
                name="password"
                v-on="{ click: $parent.clearErrorMsg, focusin: $parent.clearErrorMsg }"
            >
            <div class="invalid-feedback password-error"></div>
        </div>
        <div class="kt-login__actions">
            <button id="kt_login_signin_submit" class="btn btn-brand btn-pill btn-elevate">Ingresar</button>
        </div>
    </form>
</template>

<script>
    export default {
        props: {
            url : ""
        },
        data(){
            return {
            }
        }
    };
</script>
