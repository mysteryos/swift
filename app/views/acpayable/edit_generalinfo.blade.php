<fieldset data-name="general_info">
        <div class="form-group">
            <label class="col-md-2 control-label">Billable Company*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_business_unit_{{ $form->id }}" class="editable" data-type="select2" data-name="billable_company_code" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->billable_company_code }}" data-placeholder="Select a billable company">{{ $form->company_name }}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Supplier*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_supplier_{{ $form->id }}" class="editable" data-type="select2" data-name="supplier_code" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->supplier_code }}" data-placeholder="Select a supplier">{{ $form->supplier_name }}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Name</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_name_{{ $form->id }}" class="editable" data-type="text" data-name="name" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->name }}"></a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Description</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable" data-type="textarea" data-name="description" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" @if($form->description != "") data-value="{{ $form->description }}" @endif></a>
            </div>
        </div>
</fieldset>