<fieldset data-name="erporder" class="fieldset-erporder multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Status*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($e->id)) {{ "id=\"erporder_status_".$e->id."\"" }} @endif class="editable erporder-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCcare && !$isAdmin) editable-disabled @endif" data-type="select" data-context="erporder" data-name="status" data-source='{{ $erporder_status }}' data-pk="{{ $e->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/erporder/{{$form->encrypted_id}}" data-value="{{ $e->status or "" }}"></a>
            </div>                                                                                        
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Reference*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($e->id)) {{ "id=\"erporder_ref_".$e->id."\"" }} @endif class="editable erporder-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCcare && !$isAdmin) editable-disabled @endif" data-type="text" data-context="erporder" data-name="ref" data-pk="{{ $e->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/erporder/{{$form->encrypted_id}}" data-value="{{ $e->ref or "" }}"></a>
            </div>                                                                                        
        </div> 
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Type*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($e->id)) {{ "id=\"erporder_type_".$e->id."\"" }} @endif class="editable erporder-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isAdmin) editable-disabled @endif" data-type="select" data-context="erporder" data-name="type" data-source='{{ $erporder_type }}' data-pk="{{ $e->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/erporder/{{$form->encrypted_id}}" data-value="{{ $e->type or "" }}"></a>
            </div>                                                                                        
        </div>        
    </div>
    <legend class="top"></legend>
    @if($edit && ($isCcare || $isAdmin))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/erporder" title="Delete Order"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($e))<span class="float-id">ID: {{ $e->id }}</span> @endif
</fieldset>