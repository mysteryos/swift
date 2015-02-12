<fieldset data-name="product" class="fieldset-product multi @if(isset($dummy) && $dummy == true) dummy hide @endif">
    <div class="row">
        <div class="form-group col-lg-12 col-xs-12">
            <label class="col-md-1 control-label">Name*</label>
            <div class="col-md-11 editable-select2">
                <a href="#" @if(isset($p->id)) {{ "id=\"product_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($p) && isset($p->id)) editable-disabled @endif" data-type="select2" data-context="product" data-name="jde_itm" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/scheme-product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->jde_itm or "" }}">{{ $p->jdeproduct->DSC1 or "" }}@if(isset($p->jdeproduct->AITM)){{ " - ".$p->jdeproduct->AITM }}@endif</a>
            </div>                                                                                        
        </div>
    </div>
    @if($edit || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/scheme-product" title="Delete Product"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($p))<span class="float-id">ID: {{ Crypt::decrypt($p->id) }}</span> @endif    
</fieldset>