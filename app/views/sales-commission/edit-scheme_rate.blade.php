<fieldset data-name="rate" class="fieldset-rate multi @if(isset($dummy) && $dummy == true) dummy hide @endif @if(isset($r->id) && $r->isActive) bg-color-lighten @endif">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Effective Start Date*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($r->id)) {{ "id=\"rate_effective_date_start_".Crypt::decrypt($r->id)."\"" }} @endif data-type="date" class="editable rate-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="rate" data-pk="{{ $r->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($r->effective_date_start)){{$r->effective_date_start->format('d/m/Y')}}@endif" data-url="/{{ $rootURL }}/scheme-rate/{{ Crypt::encrypt($form->id) }}" data-name="effective_date_start">@if(isset($r->effective_date_start)){{$r->effective_date_start->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Effective End Date*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($r->id)) {{ "id=\"rate_effective_date_end_".Crypt::decrypt($r->id)."\"" }} @endif data-type="date" class="editable rate-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="rate" data-pk="{{ $r->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($r->effective_date_end)){{$r->effective_date_end->format('d/m/Y')}}@endif" data-url="/{{ $rootURL }}/scheme-rate/{{ Crypt::encrypt($form->id) }}" data-name="effective_date_end">@if(isset($r->effective_date_end)){{$r->effective_date_end->format('d/m/Y')}}@endif</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Rate</label>
            <div class="col-md-8">
                <a href="#" @if(isset($r->id)) {{ "id=\"rate_rate_".Crypt::decrypt($r->id)."\"" }} @endif class="editable rate-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="rate" data-type="text" data-name="rate" data-pk="{{ $r->id or 0 }}" data-url="/{{ $rootURL }}/scheme-rate/{{ Crypt::encrypt($form->id) }}" data-value="{{ $r->rate or "" }}"></a><span> %</span>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Status</label>
            <div class="col-md-8">
                <a href="#" @if(isset($r->id)) {{ "id=\"rate_status_".Crypt::decrypt($r->id)."\"" }} @endif class="editable rate-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="rate" data-type="select" data-name="status" data-pk="{{ $r->id or 0 }}" data-url="/{{ $rootURL }}/scheme-rate/{{ Crypt::encrypt($form->id) }}" data-value="{{ $r->status or "" }}" data-source='{{ $status_list }}'></a>
            </div>
        </div>
    </div>    
    <legend class="top"></legend>
    @if(!isset($dummy) && isset($r))<span class="float-id">ID: {{ Crypt::decrypt($r->id) }}</span> @endif
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/scheme-rate" title="Delete rate"><i class="fa fa-trash-o"></i></a>@endif    
</fieldset>