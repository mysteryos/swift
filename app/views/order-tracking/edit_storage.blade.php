<fieldset data-name="storage" class="fieldset-storage multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Storage Start</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($s->id)) {{ "id=\"storage_storage_start_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="date" data-viewformat="dd/mm/yyyy" data-date="@if(isset($s->storage_start)){{$s->storage_start->format('d/m/Y')}}@endif" data-name="storage_start" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}">@if(isset($s->storage_start)){{$s->storage_start->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Demurrage Start</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($s->id)) {{ "id=\"storage_demurrage_start_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="date" data-viewformat="dd/mm/yyyy" data-date="@if(isset($s->demurrage_start)){{$s->demurrage_start->format('d/m/Y')}}@endif" data-name="demurrage_start" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}">@if(isset($s->storage_start)){{$s->storage_start->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Storage Charges (Rs.)</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"storage_storage_charges_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="text" data-name="storage_charges" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}" data-value="{{ $s->storage_charges or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Demurrage Charges (Rs.)</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"storage_demurrage_charges_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="text" data-name="demurrage_charges" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}" data-value="{{ $s->demurrage_charges or "" }}"></a>
            </div>
        </div>          
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Reason</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"storage_reason_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="textarea" data-name="reason" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}" data-value="{{ $s->reason or "" }}"></a>
            </div>
        </div>        
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Storage Invoice No.</label>
            <div class="col-md-8">
                <a href="#" @if(isset($s->id)) {{ "id=\"storage_invoice_no_".Crypt::decrypt($s->id)."\"" }} @endif class="editable storage-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="storage" data-type="text" data-name="invoice_no" data-pk="{{ $s->id or 0 }}" data-url="/order-tracking/storage/{{ Crypt::encrypt($order->id) }}" data-value="{{ $s->invoice_no or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/storage"><i class="fa fa-trash-o"></i></a>@endif    
</fieldset>