<fieldset data-name="client" class="fieldset-client multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group col-xs-12">
            <label class="col-md-2 control-label">Customer Name*</label>
            <div class="col-md-10 editable-select2">
                <a href="#" @if(isset($c->id)) {{ "id=\"client_ref_".Crypt::decrypt($c->id)."\"" }} @endif class="editable client-editable @if(isset($dummy) && $dummy == true) dummy @endif @if(isset($c) && isset($c->id)) editable-disabled @endif" data-type="select2" data-context="client" data-name="customer_code" data-pk="{{ $c->id or 0 }}" data-url="/{{ $rootURL }}/client/{{ Crypt::encrypt($form->id) }}" data-value="{{ $c->customer_code or "" }}">@if(isset($c) && $c->customer_code > 0 ){{ $c->customer->ALPH." (Code:".$c->customer_code.")" }}@endif</a>
            </div>                                                                                        
        </div>
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/client"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($c))<span class="float-id">ID: {{ Crypt::decrypt($c->id) }}</span> @endif    
</fieldset>