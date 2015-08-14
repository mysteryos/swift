<fieldset data-name="payment-voucher" class="fieldset-paymentvoucher multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-12">
            <label class="col-md-2 control-label">Payment Voucher*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentvoucher_number_".$pv->id."\"" }} @endif class="editable paymentvoucher-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="paymentvoucher" data-name="number" data-pk="{{ $pv->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/paymentvoucher/{{ $form->encrypted_id }}" data-value="{{ $pv->number or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Validated</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentvoucher_validated_".$pv->id."\"" }} @endif class="editable paymentvoucher-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$currentUser->isSuperUser()) editable-disabled @endif" data-type="select" data-context="paymentvoucher" data-name="validated" data-pk="{{ $pv->encrypted_id or 0 }}" data-source='{{$pv_validation}}' data-url="/{{ $rootURL }}/paymentvoucher/{{ $form->encrypted_id }}" data-value="{{ $pv->validated or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Validated Msg</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentvoucher_validated_msg_".$pv->id."\"" }} @endif class="editable paymentvoucher-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$currentUser->isSuperUser()) editable-disabled @endif" data-type="textarea" data-context="paymentvoucher" data-name="validated_msg" data-pk="{{ $pv->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/paymentvoucher/{{ $form->encrypted_id }}" data-value="{{ $pv->validated_msg or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/paymentvoucher"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>