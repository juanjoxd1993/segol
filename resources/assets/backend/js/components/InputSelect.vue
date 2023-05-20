<template>
    <div class="col-lg-3">
        <div class="form-group">
            <label class="form-control-label">Tipo Movimiento:</label>
            <select class="form-control" name="movement_type_id" id="movement_type_id" @focus="$parent.clearErrorMsg($event)">
                <option disabled value="">Seleccionar</option>
                            <option value="11">Pre-Venta</option>
                            <option value="12">Venta Planta</option>
            </select>
            <div id="movement_type_id-error" class="error invalid-feedback"></div>
        </div>
    </div>
</template>

<script>
    export default {
        
    }
</script>