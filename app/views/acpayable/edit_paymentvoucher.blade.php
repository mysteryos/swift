<fieldset data-name="payment-voucher" class="fieldset-paymentvoucher multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-12">
            <label class="col-md-2 control-label">Payment Voucher*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentvoucher_number_".Crypt::decrypt($pv->id)."\"" }} @endif class="editable paymentvoucher-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="paymentvoucher" data-name="number" data-pk="{{ $pv->id or 0 }}" data-url="/{{ $rootURL }}/paymentvoucher/{{ Crypt::encrypt($form->id) }}" data-value="{{ $pv->number or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/paymentvoucher"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>