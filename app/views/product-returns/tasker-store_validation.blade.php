<input type="hidden" name="channel_name" id="channel_name" value="{{$task['channel']}}" />

<div class="row row-space-2" id="noForms" @if($hasForms){{"style='display:none;'"}}@endif>
    <div class="col-xs-12 text-center">
        <p class="h2"><i class="fa fa-lg fa-smile-o"></i> No pending tasks. Good job!</p>
    </div>
</div>

<!-- Today -->

<div class="row row-space-left-2" id="today_forms" @if(!count($today_forms)){{"style='display:none;'"}}@endif>
    <div class="row-space-2 row-space-left-2">
        <span class="h5">Today</span>
    </div>
    <div class="panel-group smart-accordion-default">
        @foreach($today_forms as $tf)
            @include('product-returns/store_validation_form',['form'=>$tf])
        @endforeach
    </div>
</div>

<!-- Yesterday -->

<div class="row row-space-left-2 row-space-top-2" id="yesterday_forms" @if(!count($yesterday_forms)){{"style='display:none;'"}}@endif>
    <div class="row-space-2 row-space-left-2">
        <span class="h5">Yesterday</span>
    </div>
    <div class="panel-group smart-accordion-default">
        @foreach($yesterday_forms as $yf)
            @include('product-returns/store_validation_form',['form'=>$yf])
        @endforeach
    </div>
</div>

<!-- Others -->

<div class="row row-space-left-2 row-space-top-2" id="other_forms" @if(!count($forms)){{"style='display:none;'"}}@endif>
    <div class="row-space-2 row-space-left-2">
        <span class="h5">Earlier</span>
    </div>
    <div class="panel-group smart-accordion-default">
        @foreach($forms as $of)
            @include('product-returns/store_validation_form',['form'=>$of])
        @endforeach
    </div>
</div>