<fieldset data-name="general_info">
        <div class="form-group">
            <label class="col-md-2 control-label"><strong>Billable Company*</strong></label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_business_unit_{{ $form->id }}" class="editable" data-type="select2" data-name="billable_company_code" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->billable_company_code }}" data-placeholder="Select a billable company">{{ $form->company_name }}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label"><strong>Supplier*</strong></label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_supplier_{{ $form->id }}" class="editable" data-type="select2" data-name="supplier_code" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->supplier_code }}" data-placeholder="Select a supplier">{{ $form->supplier_name }}</a>
            </div>
        </div>
        @if($form->payable_type !== "")
        <?php
            switch($form->payable_type)
            {
                case "SwiftOrder":
                    ?>
                    <div class="form-group">
                        <label class="col-md-2 control-label"><strong>Type of Charge*</strong></label>
                        <div class="col-md-10 editable-select2">
                            <a href="#" id="generalinfo_charge_{{ $form->id }}" class="editable" data-type="select" data-name="type" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" data-value="{{ $form->type }}" data-source='{{$type_order}}'></a>
                        </div>
                    </div>
                    <?php
                    break;
            }
        ?>
        @endif
        <div class="form-group">
            <label class="col-md-2 control-label"><strong>Description</strong></label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable" data-type="textarea" data-name="description" data-pk="{{ $form->encrypted_id }}" data-context="generalinfo" data-url="/{{ $rootURL }}/general-info" @if($form->description != "") data-value="{{ $form->description }}" @endif></a>
            </div>
        </div>
</fieldset>