@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="apr_statistics" data-urljs="{{Bust::url('/js/swift/swift.apr_statistics.js')}}">
    <div class="row">
        <div class="col-md-4 col-lg-2 col-xs-12">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i> Statistics &nbsp;</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4">
            <div class="well well-sm">
                <div class="h4 text-align-center">Top Product of the month</div>
                <div class="h5 text-align-center">@if(count($topstat_product)) {{ $topstat_product->label }} @else {{ "No top Product" }} @endif - @if(count($topstat_product)) {{ "Rs ".$topstat_product->price_sum }} @else {{ "Rs 0" }} @endif</div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="well well-sm">
                <div class="h4 text-align-center">Top Customer of the month</div>
                <div class="h5 text-align-center">@if(count($topstat_customer)) {{ $topstat_customer->customer->ALPH }} @else {{ "No top customer" }} @endif - @if(count($topstat_customer)) {{ "Rs ".$topstat_customer->price_sum }} @else {{ "Rs 0" }} @endif</div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="well well-sm">
                <div class="h4 text-align-center">Top Requester of the month</div>
                <div class="h5 text-align-center">@if(count($topstat_requester)) {{ $topstat_requester->requester->first_name." ".$topstat_requester->requester->last_name }} @else {{ "No top requester" }} @endif - @if(count($topstat_requester)) {{ "Rs ".$topstat_requester->price_sum }} @else {{ "Rs 0" }} @endif</div>
            </div>
        </div>
    </div>
<!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">

		<!-- NEW COL START -->
		<article class="col-xs-4">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="apr-productpiechart"  data-widget-togglebutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
				<header>
					<span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>
					<h2>Product Stats</h2>
				</header>
                                <!-- widget div-->
				<div class="no-padding">
					<!-- widget content -->
					<div class="widget-body">
                                            <!-- content -->
                                                <div class="widget-body-toolbar bg-color-white">

                                                        <form id="productPieChartForm" class="form-inline" role="form">
                                                                <input type="hidden" value="product" name="type" />
                                                                <div class="form-group">
                                                                        <label class="sr-only" for="s123">Show From</label>
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-form" name="date-from" placeholder="Show From" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-to" name="date-to" placeholder="To" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-default btn-sm" autocomplete="off">Submit</button>
                                                                </div>
                                                        </form>

                                                </div>
                                                <div class="padding-10">
                                                        <div id="productPieChart" class="chart chart-xl has-legend-unique"></div>
                                                </div>
                                        </div>
                                        <!-- end content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                </article>
                <!-- NEW COL END -->

		<!-- NEW COL START -->
		<article class="col-xs-4">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="apr-customerpiechart"  data-widget-togglebutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
				<header>
					<span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>
					<h2>Customer Stats</h2>
				</header>
                                <!-- widget div-->
				<div class="no-padding">
					<!-- widget content -->
					<div class="widget-body">
                                            <!-- content -->
                                                <div class="widget-body-toolbar bg-color-white">

                                                        <form id="customerPieChartForm" class="form-inline" role="form">
                                                                <input type="hidden" value="customer" name="type" />
                                                                <div class="form-group">
                                                                        <label class="sr-only" for="s123">Show From</label>
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-form" name="date-from" placeholder="Show From" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-to" name="date-to" placeholder="To" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-default btn-sm" autocomplete="off">Submit</button>
                                                                </div>
                                                        </form>

                                                </div>
                                                <div class="padding-10">
                                                        <div id="customerPieChart" class="chart chart-xl has-legend-unique"></div>
                                                </div>
                                        </div>
                                        <!-- end content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                </article>
                <!-- NEW COL END -->

                <!-- NEW COL START -->
		<article class="col-xs-4">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="apr-requesterpiechart"  data-widget-togglebutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
				<header>
					<span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>
					<h2>Requester Stats</h2>
				</header>
                                <!-- widget div-->
				<div class="no-padding">
					<!-- widget content -->
					<div class="widget-body">
                                            <!-- content -->
                                                <div class="widget-body-toolbar bg-color-white">

                                                        <form id="requesterPieChartForm" class="form-inline" role="form">
                                                                <input type="hidden" value="requester" name="type" />
                                                                <div class="form-group">
                                                                        <label class="sr-only" for="s123">Show From</label>
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-form" name="date-from" placeholder="Show From" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                        <input type="text" class="form-control input-sm datepicker" id="date-to" nam="date-to" placeholder="To" autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-default btn-sm" autocomplete="off">Submit</button>
                                                                </div>
                                                        </form>

                                                </div>
                                                <div class="padding-10">
                                                        <div id="requesterPieChart" class="chart chart-xl has-legend-unique"></div>
                                                </div>
                                        </div>
                                        <!-- end content -->
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