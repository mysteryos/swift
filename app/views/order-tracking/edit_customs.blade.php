<fieldset data-name="customs" class="fieldset-customs multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Status*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_status_".Crypt::decrypt($c->id)."\"" }} @endif class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-type="select" data-name="customs_status" data-pk="{{ $c->id or 0 }}" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-title="Select status" data-value="{{ $c->customs_status or "" }}" data-source='{{ $customs_status }}'></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Bill of Entry Number</label>
            <div class="col-md-8">
                <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_reference_".Crypt::decrypt($c->id)."\"" }} @endif class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-pk="{{ $c->id or 0 }}" data-type="text" data-value="{{ $c->customs_reference or "" }}" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-name="customs_reference"></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Filled At</label>
            <div class="col-md-8">
                <div class="input-group">                
                    <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_filled_at_".Crypt::decrypt($c->id)."\"" }} @endif data-type="date" class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-pk="{{ $c->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($c->customs_filled_at)){{$c->customs_filled_at->format('d/m/Y')}}@endif" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-name="customs_filled_at">@if(isset($c->customs_filled_at)){{$c->customs_filled_at->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Sent/Processed At</label>
            <div class="col-md-8">
                <div class="input-group">                
                    <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_processed_at_".Crypt::decrypt($c->id)."\"" }} @endif data-type="date" class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-pk="{{ $c->id or 0 }}" data-viewformat="dd/mm/yyyy" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-date="@if(isset($c->customs_processed_at)){{$c->customs_processed_at->format('d/m/Y')}}@endif" data-name="customs_processed_at">@if(isset($c->customs_processed_at)){{$c->customs_processed_at->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Under Verification At</label>
            <div class="col-md-8">
                <div class="input-group">                
                    <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_under_verification_at_".Crypt::decrypt($c->id)."\"" }} @endif data-type="date" class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-pk="{{ $c->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($c->customs_under_verification_at)){{$c->customs_under_verification_at->format('d/m/Y')}}@endif" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-name="customs_under_verification_at">@if(isset($c->customs_under_verification_at)){{$c->customs_under_verification_at->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Cleared At</label>
            <div class="col-md-8">
                <div class="input-group">                
                    <a href="#" @if(isset($c->id)) {{ "id=\"customs_customs_cleared_at_".Crypt::decrypt($c->id)."\"" }} @endif data-type="date" class="editable customs-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="customs" data-pk="{{ $c->id or 0 }}" data-viewformat="dd/mm/yyyy" data-url="/order-tracking/customsdeclaration/{{ Crypt::encrypt($order->id) }}" data-date="@if(isset($c->customs_customs_cleared_at)){{$c->customs_customs_cleared_at->format('d/m/Y')}}@endif" data-name="customs_customs_cleared_at">@if(isset($c->customs_customs_cleared_at)){{$c->customs_customs_cleared_at->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>    
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/customsdeclaration"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>