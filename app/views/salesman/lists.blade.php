@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="salesman_lists" data-urljs="{{Bust::url('/js/swift/swift.salesman_lists.js')}}">
    <div class="row">
        <div class="col-md-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-file-text-o"></i> Salesman Administration &nbsp;</h1>
        </div>
        <div class="col-md-10 col-lg-10 hidden-mobile">
            <div class="inbox-inline-actions page-title">
                <div class="btn-group">
                    <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 col-lg-2 hidden-tablet hidden-mobile">
            @if($canCreate)
                <div class="row">
                    <div class="col-xs-12">
                        <a href="/{{ $rootURL }}/create" class="btn btn-primary btn-block pjax"> <strong>Create</strong> </a>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-xs-12 inbox-side-bar">
                    <h6> Filters <!-- <a href="javascript:void(0);" rel="tooltip"page title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a>--> </h6>

                    <ul class="inbox-menu-lg">
                            <li @if($department=="all"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/administration/all" class="form-pjax-filter pjax"><i class="fa fa-file-text-o"></i>All</a>
                            </li>
                            <li @if($department=="deleted"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/administration/deleted" class="form-pjax-filter pjax"><i class="fa fa-trash-o"></i>Deleted</a>
                            </li>
                            <li @if($department=="active"){{"class=\"active\""}}@endif >
                                    <a href="/{{ $rootURL }}/administration/active" class="form-pjax-filter pjax"><i class="fa fa-check"></i>Active</a>
                            </li>
                    </ul>
                </div>
            </div>

        </div>
        <div class="col-md-10 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    @if($canCreate)
                        <a href="/{{ $rootURL }}/create" id="compose-mail-mini" class="btn btn-primary pull-right hidden-desktop visible-tablet pjax"> <strong><i class="fa fa-file fa-lg"></i></strong> </a>
                    @endif
                    @if($count)
                    <div class="btn-group pull-right inbox-paging">
                            <a href="@if($page == 1){{"javascript:void(0);"}}@else{{"/".$rootURL."/administration/".$department."/".($page-1).$filter}}@endif" class="btn btn-default btn-sm @if($page == 1){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-previous"><strong><i class="fa fa-chevron-left"></i></strong></a>
                            <a href="@if($page == $total_pages){{"javascript:void(0);"}}@else{{"/".$rootURL."/administration/".$department."/".($page+1).$filter}}@endif" class="btn btn-default btn-sm @if($page == $total_pages){{"disabled"}}@else{{"pjax"}}@endif" id="inbox-nav-next"><strong><i class="fa fa-chevron-right"></i></strong></a>
                    </div>
                    @endif
                    <div class="inbox-inline-actions hidden-desktop hidden-tablet visible-mobile">
                        <div class="btn-group">
                            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
                        </div>
                    </div>
                    @if($count) <span class="pull-right inbox-pagenumber"><strong><span id="count-start">@if($page == 1){{1}}@else{{ (($page-1)*$limit_per_page)+1 }}@endif</span>-<span id="count-end">@if($count < ($page*$limit_per_page)) {{ $count }} @else{{ $page*$limit_per_page }}@endif</span></strong> of <strong><span id="count-total">{{ $count }}</span></strong></span> @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 table-wrap custom-scroll animated fast fadeInRight">
                    <table id="inbox-table" class="table table-striped table-hover">
                            <tbody>
                                    @if(count($salesmanList) != 0)
                                        @foreach($salesmanList as $s)
                                            @include('salesman.list-single')
                                        @endforeach
                                    @else
                                        <tr id="noorders" class="empty">
                                            <td class="text-align-center">
                                                <h1>
                                                    <i class="fa fa-smile-o"></i> <span>No salesman at all. Clean & Shiny!</span>
                                                </h1>
                                            </td>
                                        </tr>
                                    @endif
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop