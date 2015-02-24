<?php $totalCommission = 0; ?>
@foreach($commissions as $c)
    <?php $totalCommission += $c->value; ?>
    <fieldset>
        <legend>Scheme: {{ $c->scheme_info_data->name }}</legend>
        <div class="form-group">
            <label class="col-md-2 control-label">Sales Value</label>
            <div class="col-md-10">
                <p class="form-control-static">Rs. {{ number_format($c->total) }}</p>
            </div>
        </div>
        @if($c->scheme_info_data->type === SwiftSalesCommissionScheme::KEYACCOUNT_FLAT_SALES_PRODUCTCATEGORY)
        <div class="form-group">
            <label class="col-md-2 control-label">Budget Value</label>
            <div class="col-md-10">
                <p class="form-control-static">Rs. {{ number_format($c->budget_info_data->value) }} ({{ round(($c->total/$c->budget_info_data->value)*100,2) }}% Achieved)</p>
            </div>
        </div>
        @endif
        <div class="form-group">
            <label class="col-md-2 control-label">Commission Value</label>
            <div class="col-md-10">
                <p class="form-control-static">Rs. {{ number_format($c->value) }}</p>
            </div>
        </div>     
    </fieldset>
@endforeach
<fieldset>
    <legend>Total</legend>
    <div class="form-group">
        <label class="col-md-2 control-label">Commission Value</label>
        <div class="col-md-10">
            <p class="form-control-static">Rs. {{ number_format($totalCommission) }}</p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-2 control-label">Sales Value</label>
        <div class="col-md-10">
            <p class="form-control-static">Rs. {{ number_format($totalSales) }}</p>
        </div>
    </div>    
</fieldset>