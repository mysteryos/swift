<fieldset data-name="purchase order" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group">
            <label class="col-md-2 control-label">Purchase Order number*</label>
            <div class="col-md-10">
                <a href="#" class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-name="reference" data-pk="{{ $p->id or 0 }}" data-url="/order-tracking/purchaseorder/{{ Crypt::encrypt($order->id) }}" data-value="{{ $p->reference or "" }}"></a>
            </div>                                                                                        
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/purchaseorder"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>