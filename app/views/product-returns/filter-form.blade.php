<div class="row row-space-2" id="filter-options" @if(!$filter_on){{"style='display:none;'"}}@endif>
    <form method="GET" action="" name="filter_pr_form" class="form-inline" id="form-filter-options">
        <input type="hidden" name="filter" value="1" />
        @if(array_key_exists('filter_customer_code',$filter))
            <div class="form-group col-md-2">
                <select name="filter_customer_code" class="full-width" id="select_filter_customer_code">
                    <option></option>
                    @foreach($filter_list_customers as $c)
                        <option value="{{$c->AN8}}">{{$c->getReadableName()}}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(isset($type) && $type!=='mine')
            @if(array_key_exists('filter_owner_user_id',$filter))
                <div class="form-group col-md-2">
                    <select name="filter_owner_user_id" class="full-width" id="select_filter_owner_user_id">
                        <option></option>
                        @foreach($filter_list_owners as $o)
                            <option value="{{$o->id}}">{{$o->getfullName()}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endif

        @if(array_key_exists('filter_driver_id',$filter))
            <div class="form-group col-md-2">
                <select name="filter_driver_id" class="full-width" id="select_filter_driver_id">
                    <option></option>
                    @foreach($filter_list_drivers as $d)
                        <option value="{{$d->id}}" @if((int)$d->id === (int) \Input::old('filter_driver_id')) {{"selected"}} @endif>{{$d->name}} - {{$d->type_name}}</option>
                    @endforeach
                </select>
            </div>
        @endif

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
        
        @if(array_key_exists('filter_step',$filter) && isset($type) && $type==="all")
            <div class="form-group col-md-2">
                <select name="filter_step" class="full-width" id="select_filter_step">
                    <option></option>
                    @foreach($filter_list_step as $step)
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