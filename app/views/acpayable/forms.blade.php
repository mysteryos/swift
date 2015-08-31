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
<div id="content" data-js="acp_forms" data-urljs="{{Bust::url('/js/swift/swift.acp_forms.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Forms &nbsp;</h1>            
        </div>
        <div class="col-md-8 col-lg-10 hidden-mobile">
            <div class="inbox-inline-actions page-title">
                <div class="btn-group">
                    <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
                    <button class="btn btn-default" id="filter-btn" data-original-title="Filter" data-placement="bottom" rel="tooltip">
                        <i class="fa fa-filter"></i>
                    </button>
                </div>
            </div>            
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-lg-2 hidden-tablet hidden-mobile">
            @if($permission->canCreate())
                <div class="row">
                    <div class="col-xs-12 btn-group">
                        <a href="/{{ $rootURL }}/create" class="btn btn-primary col-xs-6 pjax @if(!$permission->canCreate()){{ "disabled" }}@endif"> <strong>Create</strong> </a>
                        <a href="/{{ $rootURL }}/create-multi" class="btn btn-primary col-xs-6 pjax @if(!$permission->canCreate()){{ "disabled" }}@endif"> <i class="fa fa-plus-circle"></i> Multi </a>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-xs-12 inbox-side-bar">
                    <h6> Filters <!--<a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a>--></h6>
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

                    <h6> Quick Access <!--<a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Add Another" class="pull-right txt-color-darken"><i class="fa fa-plus"></i></a>--> </h6>

                    <ul class="inbox-menu-sm">
                            @if($permission->canCreate())
                                <li @if($type=="mine"){{"class=\"active\""}}@endif >
                                      <a href="/{{ $rootURL }}/forms/mine" class="form-pjax-filter pjax"><i class="fa fa-heart"></i>Mine</a>
                                </li>
                            @endif
                            <li @if($type=="recent"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/forms/recent" class="form-pjax-filter pjax"><i class="fa fa-history"></i>Recent</a>
                            </li>
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
            @include('acpayable.filter-form')
            <div class="row row-space-2 row-space-top-2">
                <div class="col-xs-8">
                    @if($filter_on)
                        <div class="hidden-tablet pull-left">
                            <span><i>Filtered By: </i></span>
                            @foreach($filter as $name => $f)
                                @if($f['enabled'])
                                    <a href="{{"/".$rootURL."/forms/".$type."/0/0/0".\Helper::filterQueryParam(Url::full(),$name)}}" class="btn btn-sm btn-default pjax">{{$f['name'].": ".$f['value']}} <i class="fa fa-times"></i></a>
                                @endif
                            @endforeach
                            <a href="{{"/".$rootURL."/forms/".$type."/0/0/0"}}" class="btn btn-sm btn-default pjax">Clear All <i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>
                <div class="col-xs-4">
                    @if($permission->canCreate())
                        <a href="/{{ $rootURL }}/create" id="compose-mail-mini" class="btn btn-primary pull-right hidden-desktop visible-tablet pjax"> <strong><i class="fa fa-file fa-lg"></i></strong> </a>
                    @endif
                    @if($formCount)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($prev_offset === 0 && $next_offset < $limit_per_page){{"javascript:void(0);"}}@else{{"/".$rootURL."/forms/".$type."/".$movement_offset."/".($prev_offset-$movement_offset < 0 ? 0 : $prev_offset-$movement_offset)."/".($prev_offset).$filter_string}}@endif" class="btn btn-default btn-sm @if(($next_offset-$movement_offset) < 0){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($next_offset > $formCount){{"javascript:void(0);"}}@else{{"/".$rootURL."/forms/".$type."/".$movement_offset."/".$prev_offset."/".$next_offset.$filter_string}}@endif" class="btn btn-default btn-sm @if($next_offset >= $formCount){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
                    </div>
                    @endif
                    <div class="inbox-inline-actions hidden-desktop hidden-tablet visible-mobile">
                        <div class="btn-group">
                            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
                            <button class="btn btn-default" id="filter-btn" data-original-title="Filter" data-placement="bottom" rel="tooltip">
                                <i class="fa fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    @if($formCount) <span class="pull-right inbox-pagenumber"><strong><span id="count-start">{{ $record_offset_start }}</span>-<span id="count-end">@if($next_offset > $formCount) {{ $formCount }} @else{{ $next_offset }}@endif</span></strong> of <strong><span id="count-total">{{ $formCount }}</span></strong></span> @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 table-wrap custom-scroll animated fast fadeInRight">
                    @include('acpayable.forms-list',array('forms'=>$forms))
                </div>
            </div>
        </div>
    </div>
</div>

@stop