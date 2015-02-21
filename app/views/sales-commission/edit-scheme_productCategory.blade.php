<fieldset data-name="product" class="fieldset-product-category multi @if(isset($dummy) && $dummy == true) dummy hide @endif">
    <div class="row">
        <div class="form-group col-lg-12 col-xs-12">
            <label class="col-md-2 control-label">Category*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($pc->id)){{ "id=\"product_category_".Crypt::decrypt($pc->id)."\"" }}@endif class="editable product-category-editable" data-type="select" data-name="category" data-pk="{{ $pc->id or 0 }}" data-context="productcategory" data-url="/{{ $rootURL }}/scheme-product-category/{{ Crypt::encrypt($form->id) }}" data-title="Select category of product" data-value="{{ $pc->category or "" }}" data-source='{{ $product_category_list }}'></a>
            </div>                                                                                        
        </div>
    </div>
    @if($edit || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/scheme-product-category" title="Delete Product Category"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($p))<span class="float-id">ID: {{ Crypt::decrypt($pc->id) }}</span> @endif    
</fieldset>