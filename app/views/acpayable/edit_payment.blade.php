<fieldset data-name="payment" class="fieldset-payment multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row payment-1 payment-2">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Type*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_type_".Crypt::decrypt($pay->id)."\"" }} @endif class="payment_type payment-editable editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="type" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-source='{{ $payment_type }}' data-value="{{ $pay->type or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Currency*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_currency_".Crypt::decrypt($pay->id)."\"" }} @endif class="payment-editable editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="currency" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-source='{{ $currency }}' data-value="{{ $pay->currency or "96" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-1 payment-2" @if(!isset($pay->type) || (int)$pay->type ===0) style="display:none;" @endif>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Date*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($pay->id)) {{ "id=\"payment_date_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-name="date" data-date="@if(isset($pay->date)){{$pay->date->format('d/m/Y')}}@endif" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}">@if(isset($pay->date)){{$pay->date->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-6 col-md-6">
            <label class="col-md-4 control-label">Amount*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_amount_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="amount" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-value="{{ $pay->amount or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-2" @if(!isset($pay->type) || $pay->type !== \SwiftACPPayment::TYPE_BANKTRANSFER) style="display:none;" @endif>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Journal Entry Number*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_journal_entry_number_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="journal_entry_number" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-value="{{ $pay->journal_entry_number or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-1" @if(!isset($pay->type) || $pay->type !== \SwiftACPPayment::TYPE_CHEQUE) style="display:none;" @endif>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Cheque Status*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_journal_status_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="status" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-source='{{ $cheque_status }}' data-value="{{ $pay->status or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Dispatch By*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_cheque_dispatch_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="cheque_dispatch" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-value="{{ $pay->cheque_dispatch or "" }}" data-source='{{ $cheque_dispatch }}'></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Dispatch Comment</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pay->id)) {{ "id=\"payment_cheque_dispatch_comment_".Crypt::decrypt($pay->id)."\"" }} @endif class="editable payment-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="textarea" data-name="cheque_dispatch_comment" data-pk="{{ $pay->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment/{{ Crypt::encrypt($form->id) }}" data-value="{{ $pay->cheque_dispatch_comment or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit && ($isAdmin || $isAccountingDept))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/payment" title="Delete Payment"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($pay))<span class="float-id">ID: {{ Crypt::decrypt($pay->id) }}</span> @endif
</fieldset>