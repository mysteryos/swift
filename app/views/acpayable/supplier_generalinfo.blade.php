<fieldset data-name="general_info">
        <div class="form-group">
            <label class="col-xs-2 control-label">Name</label>
            <div class="col-xs-10">
                <p class="form-control form-control-static">{{ trim($form->Supplier_Name) }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-2 control-label">Code</label>
            <div class="col-xs-10">
                <p class="form-control form-control-static">{{ trim($form->Supplier_Code) }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-2 control-label">VAT</label>
            <div class="col-xs-10">
                <p class="form-control form-control-static">{{ trim($form->Supplier_LongAddNo) }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-2 control-label">City</label>
            <div class="col-xs-10">
                <p class="form-control form-control-static">{{ trim($form->Supplier_City) }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Address</label>
            <div class="col-md-10">
                <p class="form-control form-control-static">{{ trim($form->Supplier_Add1) }}</p>
            </div>
        </div>
</fieldset>