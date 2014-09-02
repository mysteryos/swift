<fieldset data-name="reception" class="fieldset-reception multi @if(isset($dummy) && $dummy == true) dummy hide @endif " >
    <div class="row">
        <div class="form-group">
            <label class="col-md-2 control-label">GRN Number*</label>
            <div class="col-md-10">
                <a href="#" class="editable reception-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-type="text" data-context="reception" data-name="grn" data-pk="{{ $r->id or 0 }}" data-url="/order-tracking/reception/{{ Crypt::encrypt($order->id) }}" data-value="{{ $r->grn or "" }}"></a>
            </div>                                                                                        
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Received By</label>
            <div class="col-md-8">
                    <a href="#" data-type="text"  class="editable reception-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="reception" data-pk="{{ $r->id or 0 }}" data-name="reception_user" data-url="/order-tracking/reception/{{ Crypt::encrypt($order->id) }}" data-value="{{ $r->reception_user or "" }}"></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label class="col-md-4 control-label">Received At</label>
            <div class="col-md-8">
                    <a href="#" data-type="combodate" class="editable reception-editable @if(isset($dummy) && $dummy == true) dummy @endif" data-context="reception" data-pk="{{ $r->id or 0 }}" data-name="reception_date" data-url="/order-tracking/reception/{{ Crypt::encrypt($order->id) }}" data-template="D MM YYYY  HH:mm" data-format="YYYY-MM-DD HH:mm" data-viewformat="D MMM YYYY, HH:mm" data-combodate='{"minuteStep":10,"maxYear":"{{ date("Y") }}","firstItem":"name","minYear":"{{ date("Y")-1 }}"}' data-value="{{ $r->reception_date or "" }}"></a>
            </div>
        </div>       
    </div>
    <legend class="top"></legend>
    @if($edit)<a class="btn btn-default btn-xs top-right btn-delete" href="/order-tracking/reception"><i class="fa fa-trash-o"></i></a>@endif
</fieldset>