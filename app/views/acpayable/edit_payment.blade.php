<fieldset data-name="payment" class="fieldset-payment multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Type*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_type_".Crypt::decrypt($p->id)."\"" }} @endif class="payment_type editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="select" data-name="type" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-source='{{ $payment_type }}' data-value="{{ $p->type or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12 payment-1" @if(!isset($p->type) || $p->type !== \SwiftACPPayment::TYPE_CHEQUE) style="display:none;" @endif>
            <label class="col-md-2 control-label">Cheque Status*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_journal_entry_number_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="journal_entry_number" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-value="{{ $p->journal_entry_number or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-1 payment-2" @if(!isset($p->type) || (int)$p->type ===0) style="display:none;" @endif>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Date*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_date_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-name="date" data-date="@if(isset($p->date)){{$p->date->format('d/m/Y')}}@endif" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment">@if(isset($p->date)){{$p->date->format('d/m/Y')}}@endif</a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Amount*</label>
            <div class="col-md-10">
                Rs. <a href="#" @if(isset($p->id)) {{ "id=\"payment_amount_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="amount" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-value="{{ $p->amount or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-2" @if(!isset($p->type) || $p->type !== \SwiftACPPayment::TYPE_BANKTRANSFER) style="display:none;" @endif>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Journal Entry Number*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_journal_entry_number_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="journal_entry_number" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-value="{{ $p->journal_entry_number or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row payment-1" @if(!isset($p->type) || $p->type !== \SwiftACPPayment::TYPE_CHEQUE) style="display:none;" @endif>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Dispatch By*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_cheque_dispatch_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="text" data-name="cheque_dispatch" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-value="{{ $p->cheque_dispatch or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Dispatch Comment</label>
            <div class="col-md-10">
                <a href="#" @if(isset($p->id)) {{ "id=\"payment_cheque_dispatch_comment_".Crypt::decrypt($p->id)."\"" }} @endif class="editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin && !$isAccountingDept) editable-disabled @endif" data-type="textarea" data-name="cheque_dispatch_comment" data-pk="{{ $p->id or 0 }}" data-context="payment" data-url="/{{ $rootURL }}/payment" data-value="{{ $p->journal_entry_number or "" }}"></a>
            </div>
        </div>
    </div>
</fieldset>