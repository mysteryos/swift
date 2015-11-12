@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <!--<a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Left</a>-->
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
            @if($canCreate)
            <a href="/{{ $rootURL }}/create" class="btn btn-default pjax" rel="tooltip" data-original-title="Create" data-placement="bottom"><i class="fa fa-lg fa-file"></i></a>
            @endif
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="ot_overview" data-urljs="{{Bust::url('/js/swift/swift.ot_overview.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i> Overview &nbsp;</h1>
            <h1 class="pull-right hidden-tablet">Module Health: {{ \Helper::systemHealth($late_node_forms_count,$pending_node_count) }}</h1>
        </div>
    </div>
    @if($admin_list)
    <div class="row">
        <div class="col-xs-12">
            <h2 class="pull-right hidden-mobile hidden-tablet">Administrators: {{$admin_list}}</h2>
        </div>
    </div>
    @endif
    <ul class="nav nav-tabs row-space-4">
        <li class="@if($business_unit === false){{ "active" }}@endif">
            <a href="/order-tracking/overview/0" class="pjax">All</a>
        </li>
        @foreach(SwiftOrder::$business_unit as $k => $bu)
            <li class="@if($business_unit == $k){{ "active" }}@endif">
                <a href="/order-tracking/overview/{{ $k }}" class="pjax">{{ $bu }}</a>
            </li>
        @endforeach
    </ul>
    <!-- widget grid -->
    <section id="widget-grid">

        <!-- START ROW -->

        <div class="row">
                <!-- NEW COL START -->
                <article class="col-md-6 col-xs-12">
                        <div class="jarviswidget" id="ot-overview-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false" data-widget-load="/order-tracking/stories/{{ $business_unit }}">
                            <header>
                                <span class="widget-icon"> <i class="fa fa-globe"></i> </span>
                                <h2>Stories</h2>
                            </header>
                                    <!-- widget div-->
                            <div>
                            <!-- widget content -->
                                <div class="widget-body" id="timeline-body">
                                    <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                </div>
                            </div>
                        </div>
                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="ot-overview-pendingnodes" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" @if($business_unit==0)data-widget-load="/order-tracking/pending-nodes/"@endif>
                            <header>
                                    <span class="widget-icon"> <i class="fa fa-refresh"></i> </span>
                                    <h2>Pending Nodes (Refreshed every 30 mins)</h2>
                            </header>
                            <div class="no-padding">
                                    <div class="widget-body widget-body-compressed">
                                        @if($business_unit==0)
                                            <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                        @else
                                            <p class="text-center h3"><i class="fa fa-lg fa-exclamation-triangle"></i> Data unavailable when filtered by business unit</p>
                                        @endif
                                    </div>
                            </div>
                        </div>

                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="ot-overview-latenodes" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" @if($business_unit==0)data-widget-load="/order-tracking/late-nodes/"@endif>
                            <header>
                                    <span class="widget-icon"> <i class="fa fa-bell"></i> </span>
                                    <h2>Late Nodes (Refreshed every 30 mins)</h2>
                            </header>
                            <div class="no-padding">
                                <div class="widget-body widget-body-compressed">
                                    @if($business_unit==0)
                                        <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                    @else
                                        <p class="text-center h3"><i class="fa fa-lg fa-exclamation-triangle"></i> Data unavailable when filtered by business unit</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                </article>
                <!-- NEW COL END -->

            <!-- NEW COL START -->
            <article class="col-md-6 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="ot-overview-inprogress" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-clock-o"></i> </span>
                        <h2>Work Spot </h2>
                    </header>
                                    <!-- widget div-->
                    <div class="no-padding">
                        <!-- widget content -->
                        <div class="widget-body">
                                @include('order-tracking.overview_inprogress')
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>

                <!-- Widget ID (each widget will need unique ID)-->
                <!--
                <div class="jarviswidget" id="ot-overview-storage" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                            <h2>Upcoming Storage (Cost) </h2>
                    </header>
                                     widget div
                    <div>
                         widget content
                        <div class="widget-body">
                                                    @include('order-tracking.overview_storage')
                                            </div>
                                             end widget content
                                    </div>
                                     end widget div
                </div> -->
            </article>
            <!-- NEW COL END -->
        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop