<fieldset data-name="payment" class="fieldset-payment multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row payment-1 payment-2">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Type*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_type_".$pay->id."\"" }} @endif class="payment_type payment-editable editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="type" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-source='{{ $payment_type }}' data-value="{{ $pay->type or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Batch Number</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_batch_number_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="batch_number" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->batch_number or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-1 payment-2" @if(!isset($pay->type) || (int)$pay->type ===0) style="display:none;" @endif>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Payment Number*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_payment_number_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="payment_number" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->payment_number or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Payment Date</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($pay->id)) {{ "id=\"payment_date_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-name="date" data-date="@if(isset($pay->date)){{$pay->date->format('d/m/Y')}}@endif" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}">@if(isset($pay->date)){{$pay->date->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row payment-1 payment-2" @if(!isset($pay->type) || (int)$pay->type ===0) style="display:none;" @endif>
        <div class="form-group col-lg-6 col-md-6">
            <label class="col-md-4 control-label">Amount</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_amount_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="amount" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->amount or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Currency</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_currency_code_".$pay->id."\"" }} @endif class="payment-editable editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="currency_code" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-source='{{ $currency }}' data-value="{{ $pay->currency_code or "MUR" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-2" @if(!isset($pay->type) || $pay->type !== \SwiftACPPayment::TYPE_BANKTRANSFER) style="display:none;" @endif>
    </div>
    <div class="row payment-1" @if(!isset($pay->type) || $pay->type !== \SwiftACPPayment::TYPE_CHEQUE) style="display:none;" @endif>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Cheque Status*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_journal_status_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="status" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-source='{{ $cheque_status }}' data-value="{{ $pay->status or \SwiftACPPayment::STATUS_INPROGRESS }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Cheque Signator*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_cheque_signator_id_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="cheque_signator_id" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-source='{{ $chequesign_users }}' data-value="{{ $pay->cheque_signator_id or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Dispatch By*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_cheque_dispatch_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="cheque_dispatch" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->cheque_dispatch or "" }}" data-source='{{ $cheque_dispatch }}'></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Dispatch Comment</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_cheque_dispatch_comment_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="textarea" data-name="cheque_dispatch_comment" data-pk="{{ $pay->encrypted_id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->cheque_dispatch_comment or "" }}"></a>
            </div>
        </div>
    </div>
    @if($currentUser->isSuperUser())
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Validated</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_validated_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="payment" data-name="validated" data-pk="{{ $pay->encrypted_id or 0 }}" data-source='{{$pay_validation}}' data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->validated or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label">Validated Msg</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_validated_msg_".$pay->id."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="textarea" data-context="payment" data-name="validated_msg" data-pk="{{ $pay->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/payment/{{ $form->encrypted_id }}" data-value="{{ $pay->validated_msg or "" }}"></a>
            </div>
        </div>
    </div>
    @endif
    <legend class="top"></legend>
    @if($edit && ($isAdmin || $isAccountingDept))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/payment" title="Delete Payment"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($pay))<span class="float-id">ID: {{ $pay->id }}</span> @endif
</fieldset>