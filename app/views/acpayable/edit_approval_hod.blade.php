<fieldset data-name="approval-hod" class="fieldset-approval-hod multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-6">
            <label class="col-md-4 control-label"><strong>HOD*</strong></label>
            <div class="col-md-8">
                <a href="#" @if(isset($approval->id)) {{ "id=\"approval_hod_user_".$approval->id."\"" }} @endif class="editable approval-hod-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="approvalhod" data-name="approval_user_id" data-pk="{{ $approval->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/approval-hod/{{ Crypt::encrypt($form->id) }}" data-source='{{ $approval_hod }}' data-value="{{ $approval->approval_user_id or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-xs-6" @if(isset($dummy) && $dummy == true) style="display:none;" @endif>
             <label class="col-md-4 control-label"><strong>Approval</strong></label>
            <div class="col-md-8">
                <a href="#" @if(isset($approval->id)) {{ "id=\"approval_hod_approved_".$approval->id."\"" }} @endif class="editable @if((!$permission->isHOD() && $publishOwner) && !$permission->isAdmin()) editable-disabled @endif editable-noblur approval-hod-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select" data-context="approvalhod" data-name="approved" data-pk="{{ $approval->encrypted_id or 0 }}" data-url="/{{ $rootURL }}/approval-hod/{{ Crypt::encrypt($form->id) }}" data-source='{{ $approval_code }}' data-value="{{ $approval->approved or 0 }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if(!isset($dummy) && isset($approval))<span class="float-id">ID: {{ $approval->id }}</span> @endif
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" title="Delete Approval HOD" href="/{{ $rootURL }}/approval-hod"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>