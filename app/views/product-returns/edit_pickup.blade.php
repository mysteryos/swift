<fieldset data-name="pickup" class="fieldset-pickup multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Status*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($pickup->id)) {{ "id=\"pickup_status_".$pickup->id."\"" }} @endif class="editable pickup-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isStorePickup() && !$permission->isAdmin()) editable-disabled @endif" data-type="select" data-context="pickup" data-name="status" data-source='{{ $pickup_status }}' data-pk="{{ $pickup->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/pickup/{{$form->encrypted_id}}" data-value="{{ $pickup->status or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Driver*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($pickup->id)) {{ "id=\"pickup_driver_id_".$pickup->id."\"" }} @endif class="editable pickup-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isStorePickup() && !$permission->isAdmin()) editable-disabled @endif" data-type="select" data-context="pickup" data-name="driver_id" data-source='{{ $drivers }}' data-pk="{{ $pickup->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/pickup/{{$form->encrypted_id}}" data-value="{{ $pickup->driver_id or "" }}"></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Date*</label>
            <div class="col-md-8 editable-select2">
                <div class="input-group">
                    <a href="#" @if(isset($pickup->id)) {{ "id=\"pickup_date_".$pickup->id."\"" }} @endif class="editable pickup-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isStorePickup() && !$permission->isAdmin()) editable-disabled @endif" data-type="date" data-viewformat="dd/mm/yyyy" data-context="pickup" data-name="pickup_date" data-pk="{{ $pickup->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/pickup/{{$form->encrypted_id}}" data-date="@if(isset($pickup->pickup_date)){{$pickup->pickup_date->format('d/m/Y')}}@endif">@if(isset($pickup->pickup_date)){{$pickup->pickup_date->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Comment</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($pickup->id)) {{ "id=\"pickup_comment_".$pickup->id."\"" }} @endif class="editable pickup-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$permission->isAdmin() && !$permission->isStorePickup()) editable-disabled @endif" data-type="textarea" data-context="pickup" data-name="comment" data-pk="{{ $pickup->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/pickup/{{$form->encrypted_id}}" data-value="{{ $pickup->comment or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit && ($permission->isStorePickup() || $permission->isAdmin()))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/pickup" title="Delete Pickup"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($pickup))<span class="float-id">ID: {{ $pickup->id }}</span> @endif
</fieldset>