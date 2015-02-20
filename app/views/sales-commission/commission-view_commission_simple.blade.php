<?php $total = 0; ?>
@foreach($commissions as $c)
    <?php $total += $c->total ?>
    <fieldset>
        <legend>Scheme: {{ $c->scheme_info_data->name }}</legend>
        <div class="form-group">
            <label class="col-md-2 control-label">Commission Value</label>
            <div class="col-md-10">
                <p class="form-control-static">Rs. {{ number_format($c->total) }}</p>
            </div>
        </div>     
    </fieldset>
@endforeach
<fieldset>
    <legend>Total</legend>
    <div class="form-group">
        <label class="col-md-2 control-label">Commission Value</label>
        <div class="col-md-10">
            <p class="form-control-static">Rs. {{ number_format($total) }}</p>
        </div>
    </div>       
</fieldset>