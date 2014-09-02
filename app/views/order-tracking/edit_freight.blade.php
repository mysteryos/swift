<fieldset data-name="freight" class="fieldset-freight multi @if(isset($dummy) && $dummy == true) dummy hide @endif ">
        <div class="row">
            <div class="form-group">
                <label class="col-md-2 control-label">Type</label>
                <div class="col-md-10">
                    <a href="#" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-type="select" data-name="freight_type" data-pk="{{ $f->id or 0 }}" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-title="Select freight type" data-value="{{ $f->freight_type or "" }}" data-source='{{ $freight_type }}'></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">Freight Company*</label>
                <div class="col-md-8 editable-select2">
                    <a href="#" class="editable editable-click freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-type="select2" data-name="freight_company_id" data-pk="{{ $f->id or 0 }}" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-source="/ajaxsearch/freightcompany" data-placeholder="Select a freight company" data-value="{{ $f->freight_company_id or "" }}">{{ $f->company->name or "" }}</a>
                    <input type="hidden" name="freight_company_id" id="freightcompanysearch" value="" class="col-xs-12 no-padding" />
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">Incoterms</label>
                <div class="col-md-8">
                    <a href="#" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-type="select" data-name="incoterms" data-pk="{{ $f->id or 0 }}" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-title="Select incoterm" data-value="{{ $f->incoterms or "" }}" data-source='{{ $incoterms }}'></a>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">Vessel No</label>
                <div class="col-md-8">
                    <a href="#" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-type="text" data-name="vessel_no" data-pk="{{ $f->id or 0 }}" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-value="{{ $f->vessel_no or "" }}"></a>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">Bill of Lading No</label>
                <div class="col-md-8">
                    <a href="#" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-type="text" data-name="bol_no" data-pk="{{ $f->id or 0 }}" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-value="{{ $f->bol_no or "" }}"></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">ETD</label>
                <div class="col-md-8">
                    <div class="input-group">
                        <a href="#" data-type="date" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-pk="{{ $f->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($f->freight_etd)){{$f->freight_etd->format('d/m/Y')}}@endif" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-name="freight_etd">@if(isset($f->freight_etd)){{$f->freight_etd->format('d/m/Y')}}@endif</a>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="col-md-4 control-label">ETA</label>
                <div class="col-md-8">
                    <div class="input-group">
                        <a href="#" data-type="date" class="editable freight-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="freight" data-pk="{{ $f->id or 0 }}" data-viewformat="dd/mm/yyyy" data-date="@if(isset($f->freight_eta)){{$f->freight_eta->format('d/m/Y')}}@endif" data-url="/order-tracking/freight/{{ Crypt::encrypt($order->id) }}" data-name="freight_eta">@if(isset($f->freight_eta)){{$f->freight_eta->format('d/m/Y')}}@endif</a>
                    </div>
                </div>
            </div>
        </div>
        <legend class="top"></legend>
        @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/freight"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>