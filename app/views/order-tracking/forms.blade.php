@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Left</a>
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="ot_forms">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Forms &nbsp;</h1>            
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
                    <h6> Filters <a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a></h6>

                    <ul class="inbox-menu-lg">
                            <li @if($type=="all"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All</a>
                            </li>
                            <li @if($type=="inprogress"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/inprogress" class="form-pjax-filter pjax"><i class="fa fa-clock-o"></i>In Progress</a>
                            </li>
                            <li @if($type=="completed"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/completed" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Completed</a>
                            </li>
                            <li @if($type=="rejected"){{"class=\"active\""}}@endif >
                                    <a href="/order-tracking/forms/rejected" class="form-pjax-filter pjax"><i class="fa fa-times"></i>Rejected </a>
                            </li>
                    </ul>

                    <h6> Quick Access <a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Add Another" class="pull-right txt-color-darken"><i class="fa fa-plus"></i></a></h6>

                    <ul class="inbox-menu-sm">
                            <li>
                                    <a href="javascript:void(0);" class="form-pjax-filter pjax"><i class="fa fa-star-o"></i>Starred</a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);" class="form-pjax-filter pjax"><i class="fa fa-exclamation-triangle"></i>Important</a>
                            </li>
                    </ul>

                </div>
            </div>
            
        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    @if(Sentry::getUser()->hasAccess('ot-admin'))
                        <a href="/order-tracking/create" id="compose-mail-mini" class="btn btn-primary pull-right hidden-desktop visible-tablet pjax"> <strong><i class="fa fa-file fa-lg"></i></strong> </a>
                    @endif
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/order-tracking/forms/"}}{{$page-1}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/order-tracking/forms/"}}{{$page+1}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
                    </div>
                    @endif
                    <div class="inbox-inline-actions">
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