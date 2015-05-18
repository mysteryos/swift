<fieldset data-name="general_info">
        @if($this->currentUser->isSuperUser())
            <div class="form-group">
                <label class="col-md-2 control-label">Type</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_type_id_{{ $form->id }}" class="editable @if(!$isOwner && !$isAdmin) editable-disabled @endif" data-type="select" data-name="type" data-pk="{{$form->encrypted_id}}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-source='{{$pr_type}}' data-value="{{$form->type}}"></a>
                </div>
            </div>
        @endif
         <div class="form-group">
            <label class="col-md-2 control-label">Customer*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_customer_{{ $form->id }}" class="editable editable-click @if(!$isOwner && !$isAdmin) editable-disabled @endif" data-type="select2" data-name="customer_code" data-pk="{{$form->encrypted_id}}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-value="{{ $form->customer_code }}">{{ $form->customer_name }}</a>
            </div>
        </div>
        @if($form->type === \SwiftPR::ON_DELIVERY || $this->currentUser->isSuperUser())
        <div class="form-group">
            <label class="col-md-2 control-label">RFRF Paper Number</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_paper_number_{{ $form->id }}" class="editable @if(!$isOwner && !$isAdmin) editable-disabled @endif" data-type="textarea" data-name="paper_number" data-pk="{{$form->encrypted_id}}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-value="{{$form->paper_number or ""}}"></a>
            </div>
        </div>
        @endif
        @if($form->type === \SwiftPR::ON_DELIVERY || $this->currentUser->isSuperUser())
            <div class="form-group">
                <label class="col-md-2 control-label">Driver</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_driver_id_{{ $form->id }}" class="editable @if(!$driverInfo) editable-disabled @endif" data-type="select" data-name="driver_id" data-pk="{{$form->encrypted_id}}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-source='{{$drivers}}' data-value="{{$form->driver_id or ""}}"></a>
                </div>
            </div>
        @endif
        <div class="form-group">
            <label class="col-md-2 control-label">Description</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable @if(!$isOwner && !$isAdmin) editable-disabled @endif" data-type="textarea" data-name="description" data-pk="{{$form->encrypted_id}}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-value="{{ $form->description or "" }}"></a>
            </div>
        </div>
</fieldset>