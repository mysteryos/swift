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
<div id="content" data-js="acp_payment_cheque_issue" data-urljs="{{Bust::url('/js/swift/swift.acp_payment_cheque_issue.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-print"></i> Cheque Issue &nbsp;</h1>
        </div>
        <div class="col-md-8 col-lg-10 hidden-mobile">
            <div class="ribbon-button-alignment page-title">
                <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
                        Tick <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" class="btn-tick-all">All</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="btn-tick-nobatchnumber">No Batch number</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="btn-tick-nopvnumber">No PV Number</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:void(0);" class="btn-tick-clear">Clear</a>
                        </li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default popover-trigger" id="filter-btn" data-original-title="Filter" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-filter"></i>
                    </button>
                    <div id="filter-popover" class="hide">
                        <form method="GET" action="" name="filter_cheque">
                            <input type="hidden" name="filter" value="1" />
                            <div class="form-group">
                                <label>Supplier</label>
                                <select name="filter_supplier" class="form-control">
                                    <option disabled selected>Please select a supplier</option>
                                    @foreach($activeSuppliers as $s)
                                        <option value="{{$s->supplier_code}}">{{$s->supplier->getReadableName()}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($type==="all" || $type === "future")
                                <div class="form-group">
                                    <div class="row">
                                        @if($type==="all")
                                        <div class="col-xs-5 text-center">
                                            <input type="text" class="datepicker form-control" name="filter_start_date" value="" placeholder="Start Date" date-format="dd/mm/yy"/>
                                        </div>
                                        <div class="col-xs-2 text-center">
                                            -
                                        </div>
                                        @endif
                                        <div class="col-xs-5 text-center">
                                            <input type="text" class="datepicker form-control" name="filter_end_date" value="" placeholder="End Date" date-format="dd/mm/yy"/>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button type="submit" class="btn btn-primary btn-sm">Filter Now</button>
                                    </div>
                                    <div class="col-xs-6">
                                        <button class="btn btn-default" id="filter-btn-close">Close</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="btn-group toggle-oncheck"style="display:none;">
                    <button class="btn btn-default" data-original-title="Set Payment Voucher Number" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-file-text-o"></i>
                    </button>
                    <button class="btn btn-default" data-original-title="Set Batch Number" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-list"></i>
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
                                    <a href="/{{ $rootURL }}/cheque-issue/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All</a>
                            </li>
                            <li @if($type=="overdue"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/cheque-issue/overdue" class="form-pjax-filter pjax"><i class="fa fa-clock-o"></i>Overdue @if($overdue_count > 0 ){{"(".$overdue_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="today"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/cheque-issue/today" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Today @if($today_count > 0){{"(".$today_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="tomorrow"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/cheque-issue/tomorrow" class="form-pjax-filter pjax"><i class="fa fa-reply"></i>Tomorrow @if($tomorrow_count > 0){{"(".$tomorrow_count.")"}} @endif</a>
                            </li>
                            <li @if($type=="future"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/cheque-issue/future" class="form-pjax-filter pjax"><i class="fa fa-calendar"></i>Future @if($future_count){{"(".$future_count.")"}} @endif</a>
                            </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="row">
                <div class="col-xs-8">
                    @if($filter_on)
                        <div class="hidden-tablet pull-left">
                            <span><i>Filtered By: </i></span>
                            @foreach($filter as $name => $f)
                                @if($f['enabled'])
                                    <a href="{{"/".$rootURL."/cheque-issue/".$type."/0".Helper::filterQueryParam(Url::full(),$name)}}" class="btn btn-sm btn-default pjax">{{$f['name'].": ".$f['value']}} <i class="fa fa-times"></i></a>
                                @endif
                            @endforeach
                            <a href="{{"/".$rootURL."/cheque-issue/".$type."/0"}}" class="btn btn-sm btn-default pjax">Clear All <i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>
                <div class="col-xs-4">
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/".$rootURL."/cheque-issue/".$type."/".($page-1).$filter_string}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/".$rootURL."/cheque-issue/".$type."/".($page+1).$filter_string}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
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
                    @include('acpayable.payment-cheque-issue-list')
                </div>
            </div>
        </div>
    </div>
</div>

@stop