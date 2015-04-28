<fieldset data-name="purchase-order" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Number*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reference_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="purchaseorder" data-name="reference" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reference or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Type</label>
            <div class="col-md-8">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_type_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="purchaseorder" data-name="type" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($form->id) }}" data-source='{{ $po_type }}' data-value="{{ $p->type or "OF" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/purchaseorder"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>