<fieldset data-name="shipment" class="fieldset-shipment multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Type</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"shipment_type_".Crypt::decrypt($s->id)."\"" }} @endif class="editable shipment-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="shipment" data-type="select" data-name="type" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/shipment/{{ Crypt::encrypt($order->id) }}" data-title="Select type of shipment" data-value="{{ $s->type or "" }}" data-source='{{ $shipment_type }}'></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Volume (m<sup>3</sup>)</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"shipment_volume_".Crypt::decrypt($s->id)."\"" }} @endif class="editable shipment-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="shipment" data-type="text" data-name="volume" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/shipment/{{ Crypt::encrypt($order->id) }}" data-value="{{ $s->volume or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/shipment"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>