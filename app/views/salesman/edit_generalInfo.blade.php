<fieldset data-name="general_info">
        <div class="form-group">
                <label class="col-md-2 control-label">Notes</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_description_{{ $form->id }}" class="editable" data-type="textarea" data-name="notes" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/generalinfo" @if($form->notes != "") data-value="{{ $form->notes }}" @endif></a>
                </div>
        </div>
</fieldset>