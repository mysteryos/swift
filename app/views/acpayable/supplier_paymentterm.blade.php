<fieldset data-name="payment-term" class="fieldset-payment-term multi single @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Type*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pt->id)) {{ "id=\"paymentterm_type_".Crypt::decrypt($pt->id)."\"" }} @endif class="editable payment-term-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isAccountingDept() && !$permission->isAdmin()) editable-disabled @endif" data-type="select" data-source='{{ $payment_term_type }}' data-name="type" data-pk="{{ $pt->id or 0 }}" data-context="payment-term" data-url="/{{ $rootURL }}/supplier-payment-term/{{ Crypt::encrypt($form->Supplier_Code) }}" data-value="{{ $pt->type or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Term*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($pt->id)) {{ "id=\"paymentterm_term_".Crypt::decrypt($pt->id)."\"" }} @endif class="editable payment-term-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isAccountingDept() && !$permission->isAdmin()) editable-disabled @endif" data-type="select" data-source='{{ $payment_term_term }}' data-name="term_id" data-pk="{{ $pt->id or 0 }}" data-context="payment-term" data-url="/{{ $rootURL }}/supplier-payment-term/{{ Crypt::encrypt($form->Supplier_Code) }}" data-value="{{ $pt->term_id or "" }}"></a>
            </div>
        </div>
    </div>
</fieldset>