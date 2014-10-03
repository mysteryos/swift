<fieldset data-name="product" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Name*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_ref_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($canModifyProduct) && !$isAdmin) editable-disabled @endif" data-type="select2" data-context="product" data-name="jde_itm" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->jde_itm or "" }}">{{ $p->jdeproduct->DSC1 or "" }}@if(isset($p->jdeproduct->ITM)){{ " - ".$p->jdeproduct->ITM }}@endif</a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Quantity*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_quantity_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($canModifyProduct) && !$isAdmin) editable-disabled @endif" data-type="text" data-context="product" data-name="quantity" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->quantity or 0 }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reason_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($canModifyProduct) && !$isAdmin) editable-disabled @endif" data-type="select" data-context="product" data-name="reason_code" data-pk="{{ $p->id or 0 }}" data-source='{{ $product_reason_code }}' data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_code or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Reason Comment*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reasoncomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($canModifyProduct) && !$isAdmin) editable-disabled @endif" data-type="textarea" data-context="product" data-name="reason_others" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_others or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    @if(!isset($dummy))
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
    @if($edit && (isset($canModifyProduct) || $isAdmin))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/product"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>