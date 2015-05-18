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
<div id="content" data-js="pr_forms" data-urljs="{{Bust::url('/js/swift/swift.pr_forms.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Forms &nbsp;</h1>            
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="ribbon-button-alignment page-title">
                @if($canCreate)
                    <div class="btn-group hidden-lg">
                        <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                            <i class="fa fa-file"></i> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu col-xs-12">
                            @if($canCreateSalesman)
                            <li>
                                <a href="/{{ $rootURL }}/create/{{\SwiftPR::SALESMAN}}" class="pjax">Salesman</a>
                            </li>
                            @endif
                            @if($canCreateOnDelivery)
                            <li>
                                <a href="/{{ $rootURL }}/create/{{\SwiftPR::ON_DELIVERY}}" class="pjax">On Delivery</a>
                            </li>
                            @endif
                            @if($canCreateInvoiceCancelled)
                            <li>
                                <a href="/{{ $rootURL }}/create/{{\SwiftPR::INVOICE_CANCELLED}}" class="pjax">Invoice Cancelled</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                @endif
                <div class="btn-group">
                    <button class="btn btn-default popover-trigger" id="filter-btn" data-original-title="Filter" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-filter"></i>
                    </button>
                    <div id="filter-popover" class="hide">
                        <form method="GET" action="" name="filter_pr_form">
                            <input type="hidden" name="filter" value="1" />
                            <div class="form-group">
                                <label>Customer</label>
                                <select name="filter_customer_code" class="form-control">
                                    <option disabled selected>Please select a customer</option>
                                    @foreach($filter_list_customers as $c)
                                        <option value="{{$c->AN8}}">{{$c->getReadableName()}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>User</label>
                                <select name="filter_owner_user_id" class="form-control">
                                    <option disabled selected>Please select a user</option>
                                    @foreach($filter_list_owners as $o)
                                        <option value="{{$o->id}}">{{$o->getfullName()}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group hidden-mobile">
                                <label>Driver</label>
                                <select name="filter_driver_id" class="form-control">
                                    <option disabled selected>Please select a driver</option>
                                    @foreach($filter_list_drivers as $d)
                                        <option value="{{$d->id}}">{{$d->name}} - {{$d->type_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group hidden-mobile">
                                <div class="row">
                                    <div class="col-xs-5 text-center">
                                        <input type="text" class="datepicker form-control" name="filter_start_date" value="" placeholder="Start Date" date-format="dd/mm/yy"/>
                                    </div>
                                    <div class="col-xs-2 text-center">
                                        -
                                    </div>
                                    <div class="col-xs-5 text-center">
                                        <input type="text" class="datepicker form-control" name="filter_end_date" value="" placeholder="End Date" date-format="dd/mm/yy"/>
                                    </div>
                                </div>
                            </div>
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
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-lg-2 hidden-tablet hidden-mobile">
            @if($canCreate)
                <div class="row">
                    <div class="col-xs-12">
                        <div class="btn-group col-xs-12 no-padding">
							<button data-toggle="dropdown" class="btn btn-primary btn-block dropdown-toggle">
								Create <span class="caret"></span>
							</button>
							<ul class="dropdown-menu col-xs-12">
                                @if($canCreateSalesman)
								<li>
									<a href="/{{ $rootURL }}/create/{{\SwiftPR::SALESMAN}}" class="pjax">Salesman</a>
								</li>
                                @endif
                                @if($canCreateOnDelivery)
								<li>
									<a href="/{{ $rootURL }}/create/{{\SwiftPR::ON_DELIVERY}}" class="pjax">On Delivery</a>
								</li>
                                @endif
                                @if($canCreateInvoiceCancelled)
								<li>
									<a href="/{{ $rootURL }}/create/{{\SwiftPR::INVOICE_CANCELLED}}" class="pjax">Invoice Cancelled</a>
								</li>
                                @endif
							</ul>
						</div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-xs-12 inbox-side-bar">
                    <h6> Filters <!--<a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a>--></h6>
                    @if($isAdmin)
                        <ul class="inbox-menu-lg">
                                <li @if($type=="inprogress"){{"class=\"active\""}}@endif >
                                        <a href="/{{ $rootURL }}/forms/inprogress" class="form-pjax-filter pjax"><i class="fa fa-clock-o"></i>In Progress</a>
                                </li>
                                <li @if($type=="all"){{"class=\"active\""}}@endif >
                                        <a href="/{{ $rootURL }}/forms/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All</a>
                                </li>
                                <li @if($type=="completed"){{"class=\"active\""}}@endif >
                                        <a href="/{{ $rootURL }}/forms/completed" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Completed</a>
                                </li>
                                <li @if($type=="rejected"){{"class=\"active\""}}@endif >
                                        <a href="/{{ $rootURL }}/forms/rejected" class="form-pjax-filter pjax"><i class="fa fa-times"></i>Rejected </a>
                                </li>
                        </ul>
                    @endif

                    <h6> Quick Access <!--<a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Add Another" class="pull-right txt-color-darken"><i class="fa fa-plus"></i></a>--> </h6>

                    <ul class="inbox-menu-sm">
                        @if($canCreate)
                            <li @if($type=="mine"){{"class=\"active\""}}@endif >
                                  <a href="/{{ $rootURL }}/forms/mine" class="form-pjax-filter pjax"><i class="fa fa-heart"></i>Mine</a>
                            </li>
                        @endif
                        <li @if($type=="starred"){{"class=\"active\""}}@endif >
                                <a href="/{{ $rootURL }}/forms/starred" class="form-pjax-filter pjax"><i class="fa fa-star"></i>Starred</a>
                        </li>
                        <li @if($type=="important"){{"class=\"active\""}}@endif >
                                <a href="/{{ $rootURL }}/forms/important" class="form-pjax-filter pjax"><i class="fa fa-exclamation-triangle"></i>Important</a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/".$rootURL."/forms/".$type."/".($page-1).$filter}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/".$rootURL."/forms/".$type."/".($page+1).$filter}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
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
                    @include('product-returns.forms-list',array('forms'=>$forms))
                </div>
            </div>
        </div>
    </div>
</div>

@stop