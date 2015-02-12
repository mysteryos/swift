<fieldset data-name="salesman" class="fieldset-salesman multi @if(isset($dummy) && $dummy == true) dummy hide @endif">
    <div class="row">
        <div class="form-group col-lg-12 col-xs-12">
            <label class="col-md-2 control-label">Salesman</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($s->id)) {{ "id=\"salesman_salesman_id_".Crypt::decrypt($s->id)."\"" }} @endif class="editable salesman-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="select2" data-context="salesman" data-name="salesman_id" data-pk="{{ $s->id or 0 }}" data-url="/{{ $rootURL }}/scheme-salesman/{{ Crypt::encrypt($form->id) }}" data-value="{{ $s->id or "" }}">@if(isset($s->id)){{ $s->user->first_name." ".$s->user->last_name }}@endif</a>
            </div>
        </div>        
    </div>
    @if($edit || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/scheme-salesman/{{ Crypt::encrypt($form->id) }}" title="Delete Salesman"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($s))<span class="float-id">ID: {{ Crypt::decrypt($s->id) }}</span> @endif       
</fieldset>