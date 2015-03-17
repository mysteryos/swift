<fieldset data-name="invoice" class="fieldset-invoice multi single @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Number*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($i->id)) {{ "id=\"invoice_number_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="number"  data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}" data-value="{{ $i->number or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Date Received*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($i->id)) {{ "id=\"invoice_date_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-name="date" data-date="@if(isset($i->date)){{$i->date->format('d/m/Y')}}@endif" data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}">@if(isset($i->date)){{$i->date->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Currency</label>
            <div class="col-md-8">
                <a href="#" @if(isset($i->id)) {{ "id=\"invoice_currency_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="currency"  data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}" data-source='{{ $currency }}' data-value="{{ $i->currency or 96 }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Amount Due*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($i->id)) {{ "id=\"invoice_amount_due_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="due_amount"  data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}" data-value="{{ $i->due_amount or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Payment Term</label>
            <div class="col-md-8">
                <a href="#" @if(isset($i->id)) {{ "id=\"invoice_payment_term_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="payment_term"  data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}" data-source='{{ $payment_term }}' data-value="{{ $i->payment_term or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Date Due*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($i->id)) {{ "id=\"invoice_date_due_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="date" data-name="due_date" data-viewformat="dd/mm/yyyy" data-date="@if(isset($i->due_date)){{$i->due_date->format('d/m/Y')}}@endif" data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}">@if(isset($i->due_date)){{$i->due_date->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">GL Code</label>
            <div class="col-md-8">
                <a href="#" @if(isset($i->id)) {{ "id=\"invoice_gl_code_".Crypt::decrypt($i->id)."\"" }} @endif class="editable invoice-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$owner && !$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="gl_code"  data-pk="{{ $i->id or 0 }}" data-context="invoice" data-url="/{{ $rootURL }}/invoice/{{ Crypt::encrypt($form->id) }}" data-value="{{ $i->gl_code or "" }}"></a>
            </div>
        </div>
    </div>
    @if($edit && ($owner || $isAdmin || $isAccountingDept))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/invoice" title="Delete Invoice"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($d))<span class="float-id">ID: {{ Crypt::decrypt($d->id) }}</span> @endif
</fieldset>