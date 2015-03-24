<fieldset data-name="purchase order" class="fieldset-purchaseorder multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Number*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_reference_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="purchaseorder" data-name="reference" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($order->id) }}" data-value="{{ $p->reference or "" }}"></a><a @if(isset($dummy) || !isset($p->id) || (isset($p->id) && $p->validated !== \SwiftPurchaseOrder::VALIDATION_FOUND))style="display:none;"@endif href="/jde-purchase-order/view-by-form/{{$p->id or ""}}" class="purchase-order-view colorbox-ajax row-space-left-1"><i class="fa fa-search"></i> View</a>
            </div>                                                                                        
        </div>
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Type</label>
            <div class="col-md-8">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_type_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="purchaseorder" data-name="type" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($order->id) }}" data-source='{{ $po_type }}' data-value="{{ $p->type or "OF" }}"></a>
            </div>
        </div>
    </div>
    @if($currentUser->isSuperUser())
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Validated</label>
            <div class="col-md-8">
                <a href="#" @if(isset($p->id)) {{ "id=\"purchaseorder_validated_".Crypt::decrypt($p->id)."\"" }} @endif class="editable purchaseorder-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="purchaseorder" data-name="validated" data-pk="{{ $p->id or 0 }}" data-url="/{{ $rootURL }}/purchaseorder/{{ Crypt::encrypt($order->id) }}" data-source='{{ $po_validation }}' data-value="{{ $p->validated or 0 }}"></a>
            </div>
        </div>
    </div>
    @endif
    @if(isset($p->id))
        @if($p->validated !== \SwiftPurchaseOrder::VALIDATION_FOUND)
            <div class="alert alert-info fade in">
                <button data-dismiss="alert" class="close">×</button>
                <i class="fa-fw fa fa-info"></i>
                <strong>Info!</strong>&nbsp;
                <?php
                switch($p->validated)
                {
                    case \SwiftPurchaseOrder::VALIDATION_PENDING:
                        echo "Awaiting PO information from JDE";
                        break;

                    case \SwiftPurchaseOrder::VALIDATION_NOTFOUND:
                        echo "Purchase Order Information not found. Check again tomorrow";
                        break;

                    case \SwiftPurchaseOrder::VALIDATION_NOTFOUND_PERMANENT:
                        echo "Purchase Order information incorrect. Please double check the details entered";
                        break;
                }
                ?>
            </div>
        @endif
    @endif
    <legend class="top"></legend>
    @if(!isset($dummy) && isset($p))<span class="float-id">ID: {{ Crypt::decrypt($p->id) }}</span> @endif
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/purchaseorder"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>