<tr data-name="product" class="fieldset-product multi @if(isset($dummy) && $dummy == true) dummy hide @endif" @if(isset($p)) {{ "data-approvalstatus='".$p->getApprovalStatus()."'" }} @endif >
    <td>
        {{$p->id or ""}}
    </td>
    <td class="col-xs-4 col-md-3 editable-select2">
        <a href="#" @if(isset($p->id)) {{ "id=\"product_jde_itm_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="select2" data-context="product" data-name="jde_itm" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->jde_itm or "" }}">{{ $p->jdeproduct->DSC1 or "" }}@if(isset($p->jdeproduct->AITM)){{ " - ".$p->jdeproduct->AITM }}@endif</a>
    </td>
    <td>
        <a href="#"  @if(isset($p->id)) {{ "id=\"product_invoice_id_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="invoice_id" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->invoice_id or "" }}">{{$p->invoice_id or ""}}</a>
    </td>
    <td>
        @if(isset($p))
            <a href="#" @if(isset($p->id)) {{ "id=\"product_approval_".$p->id."\"" }} @endif class="editable productretaiilman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isRetailMan) editable-disabled @endif" data-type="select" data-context="product" data-name="approval_approved" data-pk="@if(isset($p->approvalretailman->id)){{ Crypt::encrypt($p->approvalretailman->id) }}@else{{ "0" }}@endif" data-source='{{ $approval_code }}' data-url="/{{ $rootURL }}/product-approval/{{ SwiftApproval::PR_RETAILMAN }}/{{ $p->encrypted_id }}" data-value="{{ $p->approvalretailman->approved or 0 }}"></a>
        @endif
    </td>
    <td>
        @if(isset($p))
            <a href="#" @if(isset($p->id)) {{ "id=\"product_approvalcomment_".$p->id."\"" }} @endif class="editable productretaiilman-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isRetailMan) editable-disabled @endif" data-type="textarea" data-context="product" data-name="approval_comment" data-pk="@if(isset($p->approvalretailman->id)){{ Crypt::encrypt($p->approvalretailman->id) }}@else{{ "0" }}@endif" data-url="/{{ $rootURL }}/product-approval-comment/{{ SwiftApproval::PR_RETAILMAN }}/{{ $p->encrypted_id }}" data-value="{{ $p->approvalretailman->comment->comment or "" }}"></a>
        @endif
    </td>
    @if($form->type === \SwiftPR::SALESMAN)
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_pickup_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="select" data-context="product" data-name="pickup" data-pk="{{ $p->encrypted_id or 0 }}" data-source='[{"value":0,"text":"No"},{"value":1,"text":"Yes"}]' data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->pickup or 1 }}"></a>
    </td>
    @endif
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_reason_code_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="select" data-context="product" data-name="reason_id" data-pk="{{ $p->encrypted_id or 0 }}" data-source='{{$product_reason_codes}}' data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->reason_id or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"product_reason_comment_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="textarea" data-context="product" data-name="reason_others" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->reason_others or "" }}"></a>
    </td>
    <td>
        <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_client_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$addProduct) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_client" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->qty_client or "" }}"></a>
    </td>
    @if(!$edit || ($form->type === \SwiftPR::SALESMAN && ($publishReception || $publishStoreValidation)))
        <td>
            <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_pickup_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStoreReception && !$isAdmin) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_pickup" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{$form->encrypted_id}}" data-value="{{ $p->qty_pickup or "" }}"></a>
        </td>
        @if(!$publishReception)
        <td>
            <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_store_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStoreValidation && !$isAdmin) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_store" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_store or "" }}"></a>
        </td>
        @endif
        <td>
            <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_triage_picking_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if((!$isStoreReception || !$isStoreValidation) && !$isAdmin) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_triage_picking" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_triage_picking or "" }}"></a>
        </td>
        <td>
            <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_qty_triage_disposal_".$p->id."\"" }} @endif class="editable product-editable @if(isset($dummy) && $dummy == true) dummy @endif @if((!$isStoreReception || !$isStoreValidation) && !$isAdmin) editable-disabled @endif" data-type="text" data-context="product" data-name="qty_triage_disposal" data-pk="{{ $p->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/product/{{ Crypt::encrypt($form->id) }}" data-value="{{ $p->qty_triage_disposal or "" }}"></a>
        </td>
    @endif
    <td>
        @if(($addProduct && $isOwner) || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/product" title="Delete Product"><i class="fa fa-trash-o"></i></a>@endif
    </td>
</tr>