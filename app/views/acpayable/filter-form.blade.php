<div class="row row-space-2" id="filter-options" @if(!$filter_on){{"style='display:none;'"}}@endif>
    <form method="GET" action="" name="filter_cheque" class="form-inline" id="form-filter-options">
        <input type="hidden" name="filter" value="1" />
        <div class="form-group col-lg-3 col-md-2">
            <select name="filter_billable_company_code" class="full-width" id="select_filter_billable_company_code">
                <option></option>
                @foreach($activeBillableCompanies as $cc)
                    <option value="{{$cc->billable_company_code}}" @if((int)$cc->billable_company_code === (int)\Input::old('filter_billable_company_code')) {{"selected"}} @endif>{{$cc->company->getReadableName()}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-3 col-md-2">
            <select name="filter_supplier" class="full-width" id="select_filter_supplier">
                <option></option>
                @foreach($activeSuppliers as $s)
                    <option value="{{$s->supplier_code}}" @if((int)$s->supplier_code === (int)\Input::old('filter_supplier')) {{"selected"}} @endif>{{$s->supplier->getReadableName()}}</option>
                @endforeach
            </select>
        </div>
        @if($type==="all")
            <div class="form-group">
                    <input type="text" class="datepicker form-control" name="filter_start_date" value="{{\Input::old('filter_start_date')}}" placeholder="Start Date" date-format="dd/mm/yy"/>
                        -
                    <input type="text" class="datepicker form-control" name="filter_end_date" value="{{\Input::old('filter_end_date')}}" placeholder="End Date" date-format="dd/mm/yy"/>
            </div>
        @endif
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-sm">Filter Now</button>
        </div>
    </form>
</div>