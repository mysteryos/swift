<fieldset data-name="product" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Name*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_ref_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select2" data-context="product" data-name="jde_id" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-source="/ajaxsearch/productplain" data-value="{{ $p->jde_id or "" }}">{{ $p->jdeproduct->DSC1 or "" }}</a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Quantity*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_quantity_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="product" data-name="quantity" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->quantity or 0 }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reason_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="product" data-name="reason_code" data-pk="{{ $p->id or 0 }}" data-source='{{ $product_reason_code }}' data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_code or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason Comment*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reasoncomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="textarea" data-context="product" data-name="reason_others" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_others or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/product"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>