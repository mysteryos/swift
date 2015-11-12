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
            @if($permission->canCreate())
            <a href="/{{ $rootURL }}/create" class="btn btn-default pjax" rel="tooltip" data-original-title="Create" data-placement="bottom"><i class="fa fa-lg fa-file"></i></a>
            @endif
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_overview" data-urljs="{{Bust::url('/js/swift/swift.acp_overview.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i> Overview &nbsp;</h1>
            <h1 class="pull-right hidden-tablet">Module Health: {{ \Helper::systemHealth($late_node_forms_count,$pending_node_count) }}</h1>
        </div>
    </div>
    <!-- widget grid -->
    <section id="widget-grid">

        <!-- START ROW -->

        <div class="row">

                    <!-- NEW COL START -->
                    <article class="col-md-6 col-xs-12">
                        <div class="jarviswidget" id="acp-overview-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false" data-widget-load="/{{ $rootURL }}/stories">
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
                        @if($permission->isAdmin())
                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="acp-overview-pendingnodes" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-load="/{{ $rootURL }}/pending-nodes/">
                                <header>
                                        <span class="widget-icon"> <i class="fa fa-refresh"></i> </span>
                                        <h2>Pending Nodes (Refreshed every 30 mins)</h2>
                                </header>
                                <div class="no-padding">
                                        <div class="widget-body widget-body-compressed">
                                                <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                        </div>
                                </div>
                        </div>
                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="acp-overview-latenodes" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-load="/{{ $rootURL }}/late-nodes/">
                                <header>
                                        <span class="widget-icon"> <i class="fa fa-bell"></i> </span>
                                        <h2>Late Nodes (Refreshed every 30 mins)</h2>
                                </header>
                                <div class="no-padding">
                                        <div class="widget-body widget-body-compressed">
                                            <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                        </div>
                                </div>
                        </div>
                        @endif
                    </article>
                    <!-- NEW COL END -->

            <!-- NEW COL START -->
            <article class="col-md-6 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="ot-overview-inprogress" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-clock-o"></i></span>
                        <h2>Work Spot </h2>
                    </header>
                    <!-- widget div-->
                    <div class="no-padding">
                        <!-- widget content -->
                        <div class="widget-body">
                            @include('acpayable.overview_inprogress')
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>

            </article>
            <!-- NEW COL END -->

        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop