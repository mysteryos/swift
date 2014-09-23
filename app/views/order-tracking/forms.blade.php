@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <!--<a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Left</a>-->
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="ot_forms">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Forms &nbsp;</h1>            
        </div>
        <div class="col-md-8 col-lg-10 hidden-mobile">
            <div class="inbox-inline-actions page-title">
                <div class="btn-group">
                    <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
                </div>
            </div>            
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-lg-2 hidden-tablet hidden-mobile">
            @if(Sentry::getUser()->hasAccess(['ot-admin']))
                <div class="row">
                    <div class="col-xs-12">
                        <a href="/order-tracking/create" class="btn btn-primary btn-block pjax @if(!Sentry::getUser()->hasAccess('ot-admin')){{ "disabled" }}@endif"> <strong>Create</strong> </a>                            
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-xs-12 inbox-side-bar">
                    <h6> Filters <!-- <a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a>--> </h6>

                    <ul class="inbox-menu-lg">
                            @if($edit_access)
                                <li @if($type=="inprogress"){{"class=\"active\""}}@endif >
                                        <a href="/order-tracking/forms/inprogress" class="form-pjax-filter pjax"><i class="fa fa-clock-o"></i>In Progress</a>
                                </li>
                            @endif
                            <li @if($type=="all"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All</a>
                            </li>
                            <li @if($type=="completed"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/completed" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Completed</a>
                            </li>
                            <li @if($type=="rejected"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/rejected" class="form-pjax-filter pjax"><i class="fa fa-times"></i>Rejected </a>
                            </li>
                    </ul>

                    <h6> Quick Access <!--<a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Add Another" class="pull-right txt-color-darken"><i class="fa fa-plus"></i></a>--> </h6>

                    <ul class="inbox-menu-sm">
                            <li @if($type=="starred"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/starred" class="form-pjax-filter pjax"><i class="fa fa-star"></i>Starred</a>
                            </li>
                            <li @if($type=="important"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/important" class="form-pjax-filter pjax"><i class="fa fa-exclamation-triangle"></i>Important</a>
                            </li>
                    </ul>

                </div>
            </div>
            
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    <div class="hidden-tablet pull-left">
                        <ul class="nav nav-pills filter-nav">
                            <li>
                                <a href="javascript:void(0);"><i>Filter By: </i></a>
                            </li>
                            <li class="dropdown">
                                <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0);">@if(Helper::sessionHasFilter('ot_form_filter','business_unit')) {{ SwiftOrder::$business_unit[Session::get('ot_form_filter')['business_unit']] }} @else {{ "Business Unit" }} @endif<i class="fa fa-angle-down"></i></a>                        
                                <ul class="dropdown-menu">
                                    @foreach(SwiftOrder::$business_unit as $bu_key => $bu_val)
                                        <li @if(Helper::sessionHasFilter('ot_form_filter','business_unit',$bu_key)){{ 'class="active"' }}@endif>
                                            <a href="{{ URL::current()."?filter=1&filter_name=business_unit&filter_value=".urlencode($bu_key) }}" class="pjax">{{ $bu_val }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                            @if($type != 'inprogress')
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0);">@if(Helper::sessionHasFilter('ot_form_filter','node_definition_id')) {{ $node_definition_list[Session::get('ot_form_filter')['node_definition_id']] }} @else {{ "Current Step" }} @endif<i class="fa fa-angle-down"></i></a>
                                    <ul class="dropdown-menu">
                                        @foreach($node_definition_list as $node_key => $node_val)
                                            <li @if(Helper::sessionHasFilter('ot_form_filter','node_definition_id',$node_key)){{ 'class="active"' }}@endif>
                                                <a href="{{ URL::current()."?filter=1&filter_name=node_definition_id&filter_value=".urlencode($node_key) }}" class="pjax">{{ $node_val }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </div>
                    @if(Sentry::getUser()->hasAccess('ot-admin'))
                        <a href="/order-tracking/create" id="compose-mail-mini" class="btn btn-primary pull-right hidden-desktop visible-tablet pjax"> <strong><i class="fa fa-file fa-lg"></i></strong> </a>
                    @endif
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/order-tracking/forms/".$type."/".($page-1).$filter}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/order-tracking/forms/".$type."/".($page+1).$filter}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
                    </div>
                    @endif
                    <div class="inbox-inline-actions hidden-desktop hidden-tablet visible-mobile">
                        <div class="btn-group">
                            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
                        </div>
                    </div>
                    @if($count) <span class="pull-right inbox-pagenumber"><strong><span id="count-start">@if($page == 1){{1}}@else{{ $page*$limit_per_page }}@endif</span>-<span id="count-end">@if($count < 30) {{ $count }} @else{{ $page*$limit_per_page }}@endif</span></strong> of <strong><span id="count-total">{{ $count }}</span></strong></span> @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 table-wrap custom-scroll animated fast fadeInRight">
                    @include('order-tracking.forms-list',array('orders'=>$orders))                    
                </div>
            </div>
        </div>
    </div>
</div>

@stop