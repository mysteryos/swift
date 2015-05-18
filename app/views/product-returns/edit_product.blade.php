<tr data-name="product" class="fieldset-product multi @if(isset($dummy) && $dummy == true) dummy hide @endif <?php if(!isset($dummy) && isset($p)){
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

    } ?>">" @if(!isset($dummy) && isset($p)) {{ "data-approvalstatus='".$p->approvalstatus."'" }} @endif>
    <td>
        {{$p->id or ""}}
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_ref_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="select2" data-context="product" data-name="jde_itm" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->jde_itm or "" }}">{{ $p->jdeproduct->DSC1 or "" }}@if(isset($p->jdeproduct->AITM)){{ " - ".$p->jdeproduct->AITM }}@endif</a>
    </td>
    <td>
        @if(!isset($dummy) && isset($p))
            <a href="#" @if(isset($p->id)) {{ "id=\"product_approval_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productcatman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCatMan || !$edit) editable-disabled @endif" data-type="select" data-context="product" data-name="approval_approved" data-pk="@if(isset($p->approvalcatman->id)){{ Crypt::encrypt($p->approvalcatman->id) }}@else{{ "0" }}@endif" data-source='{{ $approval_code }}' data-url="/{{ $rootURL }}/productapproval/{{ SwiftApproval::APR_CATMAN }}/{{ $p->id }}" data-value="{{ $p->approvalcatman->approved or 0 }}"></a>
        @endif
    </td>
    <td>
        @if(!isset($dummy) && isset($p))
            <a href="#" @if(isset($p->id)) {{ "id=\"product_approvalcomment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable productcatman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCatMan || !$edit) editable-disabled @endif" data-type="textarea" data-context="product" data-name="approval_comment" data-pk="@if(isset($p->approvalcatman->id)){{ Crypt::encrypt($p->approvalcatman->id) }}@else{{ "0" }}@endif" data-url="/{{ $rootURL }}/productapprovalcomment/{{ SwiftApproval::APR_CATMAN }}/{{ $p->id }}" data-value="{{ $p->approvalcatman->comment->comment or "" }}"></a>
        @endif
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_pickup_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCatMan || !$edit) editable-disabled @endif" data-type="select" data-context="product" data-name="pickup" data-pk="{{ $p->id or 0 }}" data-source='[{'1':'Yes'},{'0':'No'}]' data-url="/{{ $rootURL }}/productapproval/{{ SwiftApproval::APR_CATMAN }}/{{ $p->id }}" data-value="{{ $p->pickup or 1 }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_reason_code_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="select" data-context="product" data-name="reason_id" data-pk="{{ $p->id or 0 }}" data-source='{{$product_reason_codes}}' data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_id or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_reason_comment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="textarea" data-context="product" data-name="reason_others" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->reason_others or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_client_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_client" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_client or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_pickup_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_pickup" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_pickup or "" }}"></a>
    </td>
    <td>;
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_store_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_store" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_store or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_triage_picking_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_triage_picking" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_triage_picking or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_triage_disposal_".Crypt::decrypt($p->id)."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$canModifyProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_triage_disposal" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_triage_disposal or "" }}"></a>
    </td>
    <td>
        @if(($edit && isset($canModifyProduct) && $isOwner) || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/product" title="Delete Product"><i class="fa fa-trash-o"></i></a>@endif
    </td>
</tr>