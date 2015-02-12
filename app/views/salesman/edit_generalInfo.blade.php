<fieldset data-name="general_info">
        <div class="form-group">
                <label class="col-md-2 control-label">Department</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_department_{{ $form->id }}" class="editable" data-type="select" data-name="department_id" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" data-title="Select Department" data-value="{{ $form->department_id or "" }}" data-source='{{ $departmentList }}'></a>
                </div>            
        </div>
        <div class="form-group">
                <label class="col-md-2 control-label">Notes</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable" data-type="textarea" data-name="notes" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" @if($form->notes != "") data-value="{{ $form->notes }}" @endif></a>
                </div>
        </div>
</fieldset>