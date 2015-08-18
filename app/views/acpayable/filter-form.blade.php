<div class="row row-space-2" id="filter-options" @if(!$filter_on){{"style='display:none;'"}}@endif>
    <form method="GET" action="" name="filter_cheque" class="form-inline" id="form-filter-options">
        <input type="hidden" name="filter" value="1" />
        @if(array_key_exists('filter_billable_company_code',$filter))
        <div class="form-group col-lg-3 col-md-2">
            <select name="filter_billable_company_code" class="full-width" id="select_filter_billable_company_code">
                <option></option>
                @foreach($activeBillableCompanies as $cc)
                    <option value="{{$cc->billable_company_code}}" @if((int)$cc->billable_company_code === (int)\Input::old('filter_billable_company_code')) {{"selected"}} @endif>{{$cc->company->getReadableName()}}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if(array_key_exists('filter_supplier',$filter))
            <div class="form-group col-lg-3 col-md-2">
                <select name="filter_supplier" class="full-width" id="select_filter_supplier">
                    <option></option>
                    @foreach($activeSuppliers as $s)
                        <option value="{{$s->supplier_code}}" @if((int)$s->supplier_code === (int)\Input::old('filter_supplier')) {{"selected"}} @endif>{{$s->supplier->getReadableName()}}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(isset($type) && $type==="all")
            @if(count(array_intersect(['filter_start_date','filter_end_date'],array_keys($filter))))
                <div class="form-group">
                    @if(array_key_exists('filter_start_date',$filter))
                    <input type="text" class="datepicker form-control" name="filter_start_date" value="{{\Input::old('filter_start_date')}}" placeholder="Start Date" date-format="dd/mm/yy"/>
                    @endif
                        -
                    @if(array_key_exists('filter_end_date',$filter))
                    <input type="text" class="datepicker form-control" name="filter_end_date" value="{{\Input::old('filter_end_date')}}" placeholder="End Date" date-format="dd/mm/yy"/>
                    @endif
                </div>
            @endif
        @endif
        @if(array_key_exists('filter_step',$filter))
            <div class="form-group col-lg-3 col-md-2">
                <select name="filter_step" class="full-width" id="select_filter_step">
                    <option></option>
                    @foreach($activeSteps as $step)
                        <option value="{{$step->id}}" @if((int)$step->id === (int)\Input::old('filter_step')) {{"selected"}} @endif>{{$step->label}}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-sm">Filter Now</button>
        </div>
    </form>
</div>