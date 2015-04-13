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
<div id="content" data-js="ot_summary" data-urljs="{{Bust::url('/js/swift/swift.ot_summary.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-table"></i> Summary &nbsp;</h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        <li class="@if($business_unit === false){{ "active" }}@endif">
            <a href="/order-tracking/summary" class="pjax">All</a>
        </li>
        @foreach(SwiftOrder::$business_unit as $k => $bu)
            <li class="@if($business_unit == $k){{ "active" }}@endif">
                    <a href="/order-tracking/summary/{{ $k }}" class="pjax">{{ $bu }}</a>
            </li>                                        
        @endforeach
    </ul>
    <!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget" id="ot-overview-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false">
                    <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <h2>Summary</h2>
                    </header>                        
                            <!-- widget div-->
                            <div>
                                    <!-- widget content -->
                                    <div class="widget-body no-padding">
                                        <div class="widget-body-toolbar"></div>
                                            @include('order-tracking.summary_table')
                                    </div>
                            </div>
                </div>                
            </div>
        </div>
    </section>
    <!-- WIDGET GRID END -->

</div>

@stop          