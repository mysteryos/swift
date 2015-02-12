<fieldset data-name="general_info">
        <div class="form-group">
                <label class="col-md-2 control-label">Type</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_type_{{ $form->id }}" class="editable" data-type="select" data-name="type" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/scheme-generalinfo" data-title="Select type of scheme" data-value="{{ $form->type or "" }}" data-source='{{ $type_list }}'></a>
                </div>
        </div>
        <div class="form-group">
                <label class="col-md-2 control-label">Name</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_name_{{ $form->id }}" class="editable" data-type="text" data-name="name" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/scheme-generalinfo" data-value="{{ $form->name or "" }}">{{ $form->name or "" }}</a>
                </div>
        </div>        
        <div class="form-group">
                <label class="col-md-2 control-label">Notes</label>
                <div class="col-md-10">
                    <a href="#" id="generalinfo_notes_{{ $form->id }}" class="editable" data-type="textarea" data-name="notes" data-pk="{{ Crypt::encrypt($form->id) }}" data-context="generalinfo" data-url="/{{ $rootURL }}/scheme-generalinfo" @if($form->notes != "") data-value="{{ $form->notes or "" }}" @endif>{{ $form->notes or "" }}</a>
                </div>
        </div>
</fieldset>