<fieldset data-name="general_info">
        <div class="form-group">
            <label class="col-md-2 control-label">Business Unit*</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_business_unit_{{ $order->id }}" class="editable" data-type="select" data-name="business_unit" data-pk="{{ $order->encrypted_id }}" data-context="generalinfo" data-url="/order-tracking/generalinfo" data-title="Select Business Unit" data-value="{{ $order->business_unit }}" data-source='{{ $business_unit }}'></a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Name*</label>
            <div class="col-md-10">
                <a href="#" id="generalinfo_name_{{ $order->id }}" class="editable" data-type="text" data-name="name" data-pk="{{ $order->encrypted_id }}" data-context="generalinfo" data-url="/order-tracking/generalinfo" data-value="{{ $order->name }}"></a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Supplier</label>
            <div class="col-md-10 editable-select2">
                <a href="#" id="generalinfo_supplier_{{ $order->id }}" class="editable" data-type="select2" data-name="supplier_code" data-pk="{{ $order->encrypted_id }}" data-context="generalinfo" data-url="/order-tracking/generalinfo" data-value="{{ $order->supplier_code  }}" data-placeholder="Select a supplier">{{ $order->supplier_name }}</a>
            </div>
        </div>
        <div class="form-group">
                <label class="col-md-2 control-label">Description</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_description_{{ $order->id }}" class="editable" data-type="textarea" data-name="description" data-pk="{{ $order->encrypted_id }}" data-context="generalinfo" data-url="/order-tracking/generalinfo" @if($order->description != "") data-value="{{ $order->description }}" @endif></a>
                </div>
        </div>
</fieldset>