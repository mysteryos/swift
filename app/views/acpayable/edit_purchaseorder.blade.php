<fieldset data-name="purchase order" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-12">
            <label class="col-md-2 control-label">Purchase Order number*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reference_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="purchaseorder" data-name="reference" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($order->id) }}" data-value="{{ $p->reference or "" }}"></a>
            </div>                                                                                        
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/purchaseorder"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>