<fieldset data-name="general_info">
        <div class="form-group">
            <label class="col-md-2 control-label">Name*</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_name_{{ $form->id }}" class="editable @if(!$isCreator && !$permission->isAdmin()) editable-disabled @endif" data-type="text" data-name="name" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-value="{{ $form->name }}"></a>
            </div>
        </div>

        <div class="form-group">
                <label class="col-md-2 control-label">Description</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable @if(!$isCreator && !$permission->isAdmin()) editable-disabled @endif" data-type="textarea" data-name="description" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" @if($form->description != "") data-value="{{ $form->description }}" @endif></a>
                </div>
        </div>
    
         <div class="form-group">
            <label class="col-md-2 control-label">Customer*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_customer_{{ $form->id }}" class="editable editable-click @if(!$isCreator && !$permission->isAdmin()) editable-disabled @endif" data-type="select2" data-name="customer_code" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-value="{{ $form->customer_code }}">@if($form->customer_code > 0 ){{ $form->customer->ALPH." (Code:".$form->customer_code.")" }}@endif</a>
            </div>
        </div>       
</fieldset>