<fieldset data-name="delivery" class="fieldset-delivery multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Status*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_status_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="select" data-context="delivery" data-name="status" data-source='{{ $delivery_status }}' data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}" data-value="{{ $d->status or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Status Comment</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_status_comment_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="textarea" data-context="delivery" data-name="status_comment" data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}" data-value="{{ $d->status_comment or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Invoice Number</label>
            <div class="col-md-10">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_ref_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="text" data-context="delivery" data-name="invoice_number" data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}" data-value="{{ $d->invoice_number or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Invoice Recipient</label>
            <div class="col-md-10">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_ref_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="text" data-context="delivery" data-name="invoice_recipient" data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}" data-value="{{ $d->invoice_recipient or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>    
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Delivered By</label>
            <div class="col-md-10">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_delivery_person_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="text" data-context="delivery" data-name="delivery_person" data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}" data-value="{{ $d->delivery_person or "" }}"></a>
            </div>                                                                                        
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-2 control-label">Delivered On</label>
            <div class="col-md-10">
                <a href="#" @if(isset($d->id)) {{ "id=\"delivery_delivery_date_".Crypt::decrypt($d->id)."\"" }} @endif class="editable delivery-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isStore && !$isAdmin) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-date="@if(isset($d->delivery_date)){{$d->delivery_date->format('d/m/Y')}}@endif" data-context="delivery" data-name="delivery_date" data-pk="{{ $d->id or 0 }}" data-url="/{{ $rootURL }}/delivery/{{ $form->encrypted_id }}">@if(isset($d->delivery_date)){{$d->delivery_date->format('d/m/Y')}}@endif</a>
            </div>                                                                                        
        </div>        
    </div>
    <legend class="top"></legend>
    @if($edit && ($isStore || $isAdmin))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/delivery" title="Delete Delivery"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($d))<span class="float-id">ID: {{ Crypt::decrypt($d->id) }}</span> @endif
</fieldset>