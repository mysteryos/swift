@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="apr_approval" data-urljs="{{Bust::url('/js/swift/swift.apr_approval.js')}}">
    <input type="hidden" name="channel_name" id="channel_name" value="apr_approval_{{$currentUser->id}}" />
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-gift"></i>
                    A&P Request
                <span>&gt;
                    Approval
                </span>
            </h1>
        </div>
    </div>
    @if(!$hasForms)
    <div class="row row-space-2" id="noForms">
        <div class="col-xs-12 text-center">
            <p class="h2"><i class="fa fa-lg fa-smile-o"></i> No pending approvals. Good job!</p>
        </div>
    </div>
    @endif
    <!-- Today -->

    <div class="row row-space-left-2" id="today_forms" @if(!count($today_forms)){{"style='display:none;'"}}@endif>
        <div class="row-space-2 row-space-left-2">
            <span class="h5">Today</span>
        </div>
        <div class="panel-group smart-accordion-default">
            @foreach($today_forms as $tf)
                @include('aprequest.approval_form',['form'=>$tf])
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
                @include('aprequest.approval_form',['form'=>$yf])
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
                @include('aprequest.approval_form',['form'=>$of])
            @endforeach
        </div>
    </div>
</div>

@stop