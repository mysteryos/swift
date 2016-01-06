<fieldset data-name="payment-suggestion" class="fieldset-paymentsuggestion multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label"><strong>Type</strong></label>
            <div class="col-md-8">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentsuggestion_type_".$paySuggest->id."\"" }} @endif class="editable paymentsuggestion-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="paymentsuggestion" data-source='{{ $payment_type }}' data-name="type" data-pk="{{ $paySuggest->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/payment-suggestion/{{ $form->encrypted_id }}" data-value="{{ $paySuggest->type or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label"><strong>Amount(%)</strong></label>
            <div class="col-md-8">
                <a href="#" @if(isset($pv->id)) {{ "id=\"paymentsuggestion_amount_".$paySuggest->id."\"" }} @endif class="editable paymentsuggestion-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="paymentsuggestion" data-name="amount" data-pk="{{ $paySuggest->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/payment-suggestion/{{ $form->encrypted_id }}" data-value="{{ $paySuggest->amount or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/payment-suggestion"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>