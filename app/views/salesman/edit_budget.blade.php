<fieldset data-name="budget" class="fieldset-budget multi @if(isset($dummy) && $dummy == true) dummy hide @endif @if(isset($b->id) && $b->isActive) bg-color-lighten @endif ">
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Month*</label>
            <div class="col-md-8">
                <div class="input-group">
                    <a href="#" @if(isset($b->id)) {{ "id=\"budget_date_".Crypt::decrypt($b->id)."\"" }} @endif data-type="date" class="editable budget-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="budget" data-pk="{{ $b->id or 0 }}" data-viewformat="mm/yyyy" data-date="@if(isset($b->date_start)){{$b->date_start->format('m/Y')}}@endif" data-url="/{{ $rootURL }}/budget/{{ Crypt::encrypt($form->id) }}" data-name="date">@if(isset($b->date_start)){{$b->date_start->format('m/Y')}}@endif</a>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Value*</label>
            <div class="col-md-8">
                <span>Rs. </span><a href="#" @if(isset($b->id)) {{ "id=\"budget_value_".Crypt::decrypt($b->id)."\"" }} @endif class="editable budget-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="budget" data-type="text" data-name="value" data-pk="{{ $b->id or 0 }}" data-url="/{{ $rootURL }}/budget/{{ Crypt::encrypt($form->id) }}" data-value="{{ $b->value or "" }}"></a>
            </div>
        </div>        
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Scheme*</label>
            <div class="col-md-8">
                <a href="#" @if(isset($b->id)) {{ "id=\"budget_scheme_".Crypt::decrypt($b->id)."\"" }} @endif class="editable budget-editable" data-type="select" data-name="scheme_id" data-pk="{{ $b->id or 0 }}" data-context="budget" data-url="/{{ $rootURL }}/budget/{{ Crypt::encrypt($form->id) }}" data-title="Select type of scheme" data-value="{{ $b->scheme_id or "" }}" data-source='{{ $schemeList }}'></a>
            </div>
        </div>        
    </div>
    <legend class="top"></legend>    
    @if($edit || $isAdmin)<a class="btn btn-default btn-xs top-right btn-delete" href="/{{ $rootURL }}/budget" title="Delete Budget"><i class="fa fa-trash-o"></i></a>@endif
    @if(!isset($dummy) && isset($b))<span class="float-id">ID: {{ Crypt::decrypt($b->id) }}</span> @endif    
</fieldset>