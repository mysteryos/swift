@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="salesman_budget" dataurljs="{{Bust::url('/js/swift/swift.salesman_budget.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-home"></i>Budget - Period of {{ $datePeriodStart }} - {{ $datePeriodEnd }} &nbsp;</h1>
        </div>
    </div>    
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">
		<!-- NEW COL START -->
                <article class="col-xs-12">
                        @if(count($salesmanCommission))
                            @foreach($salesmanCommission as $s)
                                <!-- Widget ID (each widget will need unique ID)-->
                                <div class="jarviswidget" id="salesman-chart-{{ $s['salesman']->id }}" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                                        <header>
                                                <span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>
                                                <h2>{{ $s['salesman']->user->first_name." ".$s['salesman']->user->last_name}}</h2>
                                        </header>
                                        <!-- widget div-->
                                        <div class="no-padding">
                                                <!-- widget content -->
                                                <div class="widget-body">
                                                    <!-- content -->
                                                        <div class="padding-10">
                                                                <input type="hidden" class="budget-value" value='{{ json_encode($s['chart']) }}'/>
                                                                <div class="budget-chart chart chart-xl has-legend-unique" id="chart-{{ $s['salesman']->id }}"></div>
                                                                <div class="well">
                                                                    <table class="table table-hover">
                                                                        <tr>
                                                                            <th>
                                                                                Scheme
                                                                            </th>
                                                                            <th>
                                                                                Budget
                                                                            </th>
                                                                            <th>
                                                                                Actual Sales
                                                                            </th>
                                                                            <th>
                                                                                Achieved
                                                                            </th>
                                                                        </tr>
                                                                        @foreach($s['chart'] as $c)
                                                                        <tr>
                                                                            <td>{{ $c['scheme_name'] }}</td>
                                                                            <td>Rs. {{ number_format($c['budget']) }}</td>
                                                                            <td>Rs. {{ number_format($c['actual']) }}</td>
                                                                            <td>{{ round(($c['actual']/$c['budget'])*100,2) }}%</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </table>
                                                                </div>
                                                        </div>
                                                </div>
                                                <!-- end content -->
                                        </div>
                                        <!-- end widget div -->
                                </div>
                            @endforeach
                        @else
                            <div class='well'>
                                <h2 class='text-center'>
                                    No budget stats for the selected period.
                                </h2>
                            </div>
                        @endif
                </article>
        </div>
        
    </section>
    
</div>

@stop