@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_supplier_list" data-urljs="{{Bust::url('/js/swift/swift.acp_supplier_list.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Suppliers &nbsp;</h1>
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
            <div class="row">
                <div class="col-xs-12">
                    <h6> Filter By
                    <ul class="inbox-menu-sm">
                        <li @if($filter==="active"){{"class=\"active\""}}@endif >
                            <a href="/{{ $rootURL }}/supplier-list/active" class="form-pjax-filter pjax"><i class="fa fa-angle-right"></i>Active</a>
                        </li>
                        <li @if($filter==="all"){{"class=\"active\""}}@endif >
                            <a href="/{{ $rootURL }}/supplier-list/" class="form-pjax-filter pjax"><i class="fa fa-angle-right"></i>All</a>
                        </li>
                        @foreach($alphabetSoup as $alpha)
                            <li @if($filter===$alpha){{"class=\"active\""}}@endif >
                                <a href="/{{ $rootURL }}/supplier-list/{{ $alpha }}" class="form-pjax-filter pjax"><i class="fa fa-angle-double-right"></i>{{ $alpha }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
        <div class="col-md-8 col-lg-10 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/".$rootURL."/supplier-list/".$filter."/".($page-1)}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/".$rootURL."/supplier-list/".$filter."/".($page+1)}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
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
                    @include('acpayable.supplier-list',array('forms'=>$forms))
                </div>
            </div>
        </div>
    </div>
</div>

@stop