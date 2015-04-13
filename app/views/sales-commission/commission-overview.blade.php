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
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="salescommission_commissionoverview" data-urljs="{{Bust::url('/js/swift/swift.salescommission_commissionoverview.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i>Commission - Overview &nbsp;</h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        <li class="@if($selectedDepartment === false){{ "active" }}@endif">
            <a href="/{{ $rootURL }}/commission-overview" class="pjax">All</a>
        </li>
        @foreach($departmentList as $d)
            <li class="@if($selectedDepartment == $d->id){{ "active" }}@endif">
                <a href="/{{ $rootURL }}/commission-overview/{{ $d->id }}" class="pjax">{{ $d->name }}</a>
            </li>                                        
        @endforeach
    </ul>
    <!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">
                
                <article class="col-md-6 col-xs-12">
                    <div class="jarviswidget" id="salescommission-commission-overview-last3months" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false">
                        <header>
                                <span class="widget-icon"> <i class="fa fa-money"></i> </span>
                                <h2>Commision of last 3 months</h2>                               
                        </header>                        
                                <!-- widget div-->
				<div>
					<!-- widget content -->
                                        <div class="widget-body no-padding">
                                            @include('sales-commission.commission-overview_last3months')
                                        </div>
                                </div>
                    </div>                    
                </article>
                
                <!-- NEW COL START -->
                <article class="col-md-6 col-xs-12">
                    <div class="jarviswidget" id="salescommission-commission-overview-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false" data-widget-load="/{{ $rootURL }}/commission-stories/{{ $selectedDepartment }}">
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
                </article>
                <!-- COL END -->
        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop