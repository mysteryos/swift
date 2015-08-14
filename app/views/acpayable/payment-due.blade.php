@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment">
            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_payment_due" data-urljs="{{Bust::url('/js/swift/swift.acp_payment_due.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-clock-o"></i> Payment Due &nbsp;</h1>
        </div>
        <div class="col-md-8 col-lg-10 hidden-mobile">
            <div class="ribbon-button-alignment page-title">
                <div class="btn-group">
                    <button class="btn btn-default" id="filter-btn" data-original-title="Filter" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-filter"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-lg-2 hidden-tablet hidden-mobile">
            <div class="row">
                <div class="col-xs-12 inbox-side-bar">
                    <h6> Filters </h6>
                    <ul class="inbox-menu-lg">
                            <li @if($type=="all"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All @if($all_count > 0 ){{"(".$all_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="overdue"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/overdue" class="form-pjax-filter pjax"><i class="fa fa-clock-o"></i>Overdue @if($overdue_count > 0 ){{"(".$overdue_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="today"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/today" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Today @if($today_count > 0){{"(".$today_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="tomorrow"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/tomorrow" class="form-pjax-filter pjax"><i class="fa fa-reply"></i>Tomorrow @if($tomorrow_count > 0){{"(".$tomorrow_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="future"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/future" class="form-pjax-filter pjax"><i class="fa fa-calendar"></i>Future @if($future_count){{"(".$future_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="nodate"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/payment-due/nodate" class="form-pjax-filter pjax"><i class="fa fa-question"></i>No Due Date @if($nodate_count){{"(".$nodate_count.")"}} @endif</a>
                            </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            @include('acpayable.filter-form')
            <div class="row row-space-2">
                <div class="col-xs-8">
                    @if($filter_on)
                        <div class="hidden-tablet pull-left">
                            <span><i>Filtered By: </i></span>
                            @foreach($filter as $name => $f)
                                @if($f['enabled'])
                                    <a href="{{"/".$rootURL."/payment-due/".$type."/1".Helper::filterQueryParam(Url::full(),$name)}}" class="btn btn-sm btn-default pjax">{{$f['name'].": ".$f['value']}} <i class="fa fa-times"></i></a>
                                @endif
                            @endforeach
                            <a href="{{"/".$rootURL."/payment-due/".$type."/1"}}" class="btn btn-sm btn-default pjax">Clear All <i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>
                <div class="col-xs-4">
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/".$rootURL."/payment-due/".$type."/".($page-1).$filter_string}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/".$rootURL."/payment-due/".$type."/".($page+1).$filter_string}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
                    </div>
                    @endif
                    <div class="inbox-inline-actions hidden-desktop hidden-tablet visible-mobile">
                        <div class="btn-group">
                            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
                        </div>
                    </div>
                    @if($count) <span class="pull-right inbox-pagenumber"><strong><span id="count-start">@if($page == 1){{1}}@else{{ (($page-1)*$limit_per_page)+1 }}@endif</span>-<span id="count-end">@if($count < ($page*$limit_per_page)) {{ $count }} @else{{ $page*$limit_per_page }}@endif</span></strong> of <strong><span id="count-total">{{ $count }}</span></strong></span> @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 table-wrap custom-scroll animated fast fadeInRight">
                    @include('acpayable.payment-due-list')
                </div>
            </div>
        </div>
    </div>
</div>

@stop