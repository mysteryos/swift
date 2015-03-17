<fieldset data-name="credit note" class="fieldset-creditnote multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
    <div class="row">
        <div class="form-group col-xs-12">
            <label class="col-md-2 control-label">Credit Note number*</label>
            <div class="col-md-10">
                <a href="#" @if(isset($c->id)) {{ "id=\"creditnote_number_".Crypt::decrypt($c->id)."\"" }} @endif class="editable creditnote-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="creditnote" data-name="number" data-pk="{{ $c->id or 0 }}" data-url="/{{ $rootURL }}/creditnote/{{ Crypt::encrypt($form->id) }}" data-value="{{ $c->number or "" }}"></a>
            </div>
        </div>
    </div>
    <legend class="top"></legend>
    @if(!isset($dummy) && isset($c))<span class="float-id">ID: {{ Crypt::decrypt($c->id) }}</span> @endif
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/creditnote"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>