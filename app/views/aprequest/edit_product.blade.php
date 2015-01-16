<fieldset data-name="product" class="fieldset-product multi @if(isset($dummy) && $dummy == true) dummy hide @endif" @if(!isset($dummy) && isset($p)) {{ "data-approvalstatus='".$p->approvalstatus."'" }} @endif>
    <div class="product-bg <?php if(!isset($dummy) && isset($p)){
        switch($p->approvalstatus)
        {
            case SwiftApproval::PENDING:
                echo " bg-color-orange";
                break;
            case SwiftApproval::APPROVED:
                echo " bg-color-green";
                break;
            case SwiftApproval::REJECTED:
                echo " bg-color-red";
                break;
        }
        
    } ?>"></div>
    <div class="row">
        <div class="form-group col-lg-12 col-xs-12">
            <label class="col-md-1 control-label">Name*</label>
            <div class="col-md-11 editable-select2">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_ref_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="select2" data-context="product" data-name="jde_itm" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->jde_itm or "" }}">{{ $p->jdeproduct->DSC1 or "" }}@if(isset($p->jdeproduct->ITM)){{ " - ".$p->jdeproduct->ITM }}@endif</a>
            </div>                                                                                        
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Quantity*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_quantity_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="quantity" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->quantity or 0 }}"></a>
            </div>
        </div>        
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Total Price</label>
            <div class="col-md-10">
                <p class="form-control-static totalprice" data-price="@if(!isset($dummy) && (isset($p) && $p->price > 0)) {{ $p->price }} @else {{ "0" }} @endif">
                    @if(!isset($dummy) && (isset($p) && $p->totalprice() > 0)) {{ "Rs ".$p->totalprice() }} @else {{ "N/A" }} @endif
                </p>
            </div>
        </div>              
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reason_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="select" data-context="product" data-name="reason_code" data-pk="{{ $p->id or 0 }}" data-source='{{ $product_reason_code }}' data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_code or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason Comment*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reasoncomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="textarea" data-context="product" data-name="reason_others" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_others or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    @if(!isset($dummy) && isset($p))
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Cat Man Approval</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_catmanapproval_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productcatman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCatMan) editable-disabled @endif" data-type="select" data-context="product" data-name="approval_approved" data-pk="@if(isset($p->approvalcatman->id)){{ Crypt::encrypt($p->approvalcatman->id) }}@else{{ "0" }}@endif" data-source='{{ $approval_code }}' data-url="/{{ $rootURL }}/productapproval/{{ SwiftApproval::APR_CATMAN }}/{{ $p->id }}" data-value="{{ $p->approvalcatman->approved or 0 }}"></a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Cat Man Approval Comment</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_catmanapprovalcomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productcatman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCatMan) editable-disabled @endif" data-type="textarea" data-context="product" data-name="approval_comment" data-pk="@if(isset($p->approvalcatman->id)){{ Crypt::encrypt($p->approvalcatman->id) }}@else{{ "0" }}@endif" data-url="/{{ $rootURL }}/productapprovalcomment/{{ SwiftApproval::APR_CATMAN }}/{{ $p->id }}" data-value="{{ $p->approvalcatman->comment->comment or "" }}"></a>
            </div>
        </div>        
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Exec Approval</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_execapproval_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productexec-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isExec) editable-disabled @endif" data-type="select" data-context="product" data-name="approval_approved" data-pk="@if(isset($p->approvalexec->id)){{ Crypt::encrypt($p->approvalexec->id) }}@else{{ "0" }}@endif" data-source='{{ $approval_code }}' data-url="/{{ $rootURL }}/productapproval/{{ SwiftApproval::APR_EXEC }}/{{ $p->id }}" data-value="{{ $p->approvalexec->approved or 0 }}"></a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Exec Approval Comment</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_execapprovalcomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productexec-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isExec) editable-disabled @endif" data-type="textarea" data-context="product" data-name="approval_comment" data-pk="@if(isset($p->approvalexec->id)){{ Crypt::encrypt($p->approvalexec->id) }}@else{{ "0" }}@endif" data-url="/{{ $rootURL }}/productapprovalcomment/{{ SwiftApproval::APR_EXEC }}/{{ $p->id }}" data-value="{{ $p->approvalexec->comment->comment or "" }}"></a>
            </div>
        </div>        
    </div>    
    @endif    
    <legend class="top"></legend>
    @if($edit && (isset($canModifyProduct) || $isAdmin))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/product" title="Delete Product"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($p))<span class="float-id">ID: {{ Crypt::decrypt($p->id) }}</span> @endif
</fieldset>