<fieldset data-name="creditnote" class="fieldset-credit-note multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-lg-6 col-xs-12">
            <label class="col-md-4 control-label">Number*</label>
            <div class="col-md-8 editable-select2">
                <a href="#" @if(isset($c->id)) {{ "id=\"creditnote_status_".Crypt::decrypt($c->id)."\"" }} @endif class="editable creditnote-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(!$isCreditor && !$isAdmin) editable-disabled @endif" data-type="text" data-context="creditnote" data-name="number" data-pk="{{ $c->id or 0 }}" data-url="/{{ $rootURL }}/credit-note/{{ Crypt::encrypt($form->id) }}" data-value="{{ $c->number or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit && ($isCreditor || $isAdmin))<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/credit-note" title="Delete Credit Note"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($c))<span class="float-id">ID: {{ Crypt::decrypt($c->id) }}</span> @endif
</fieldset>