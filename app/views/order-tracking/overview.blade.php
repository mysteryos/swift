@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <!--<a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Left</a>-->
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            @if($canCreate)
            <a href="/{{ $rootURL }}/create" class="btn btn-default pjax" rel="tooltip" data-original-title="Create" data-placement="bottom"><i class="fa fa-lg fa-file"></i></a>                            
            @endif            
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="ot_overview">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i> Overview &nbsp;</h1>            
        </div>
    </div>
    
<!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">

                <!-- NEW COL START -->
                <article class="col-md-6 col-xs-12">
                    <div class="jarviswidget" id="ot-overview-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false">
                        <header>
                                <span class="widget-icon"> <i class="fa fa-globe"></i> </span>
                                <h2>Stories</h2>
                        </header>                        
                                <!-- widget div-->
				<div class="no-padding">
					<!-- widget content -->
                                        <div class="widget-body">
                                                <div class="smart-timeline">
                                                    <ul class="smart-timeline-list">
                                                        @if(count($stories))
                                                            @foreach($stories as $story)
                                                                @include('story.single')
                                                            @endforeach
                                                        @else
                                                        <li class="text-center"><h2>No posts yet</h2></li>
                                                        @endif
                                                    </ul>
                                                </div>                                            
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
			<div class="jarviswidget" id="ot-overview-transit" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				<header>
					<span class="widget-icon"> <i class="fa fa-plane"></i> </span>
                                        <h2>Transit Calendar </h2>
                                        <div class="widget-toolbar">
                                                <!-- add: non-hidden - to disable auto hide -->
                                                <div class="btn-group">
                                                        <button class="btn dropdown-toggle btn-xs btn-default" data-toggle="dropdown">
                                                                Showing <i class="fa fa-caret-down"></i>
                                                        </button>
                                                        <ul class="dropdown-menu js-status-update pull-right">
                                                                <li>
                                                                        <a href="javascript:void(0);" id="transit_calendar_mt">Month</a>
                                                                </li>
                                                                <li>
                                                                        <a href="javascript:void(0);" id="transit_calendar_ag">Agenda</a>
                                                                </li>
                                                                <li>
                                                                        <a href="javascript:void(0);" id="transit_calendar_td">Today</a>
                                                                </li>
                                                        </ul>
                                                </div>
                                        </div>                                        
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                                @include('order-tracking.overview_transit')
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        
			<!-- Widget ID (each widget will need unique ID)-->
<!--			<div class="jarviswidget" id="ot-overview-storage" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
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
                        </div>-->
                </article>
                <!-- NEW COL END -->
        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop                    