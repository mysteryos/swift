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
<div id="content" data-js="ot_transit_foreign" data-urljs="{{Bust::url('/js/swift/swift.ot_transit_foreign.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-plane"></i> Transit Foreign &nbsp;</h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        <li class="@if($business_unit === false){{ "active" }}@endif">
            <a href="/{{ $rootURL }}/transit-calendar/0" class="pjax">All</a>
        </li>
        @foreach(SwiftOrder::$business_unit as $k => $bu)
            <li class="@if($business_unit == $k){{ "active" }}@endif">
                    <a href="/{{ $rootURL }}/transit-calendar/{{ $k }}" class="pjax">{{ $bu }}</a>
            </li>
        @endforeach
    </ul>

    <!-- widget grid -->
    <section id="widget-grid">

        <!-- START ROW -->

        <div class="row">

            <!-- NEW COL START -->
            <article class="col-xs-12">
                <div class="row">
                    @if(count($freightToday))
                    <div class="well well-sm col-lg-6 col-xs-12">
                        @if(count($freightToday))
                            <h4>Today</h4>
                            <table class="table table-hover table-striped table-condensed">
                                @foreach($freightToday as $ft)
                                <?php
                                    switch($ft->order->business_unit)
                                    {
                                        case SwiftOrder::SCOTT_CONSUMER:
                                            $className = "bg-color-orange txt-color-white";
                                            break;
                                        case SwiftOrder::SCOTT_HEALTH;
                                            $className = "bg-color-green txt-color-white";
                                            break;
                                        case SwiftOrder::SEBNA:
                                            $className = "bg-color-blue txt-color-white";
                                            break;
                                    }
                                ?>
                                <tr data-url="{{ Helper::generateURL($ft->order) }}">
                                    <td class="{{ $className }}">
                                        <?php
                                        switch($ft->freight_type)
                                        {
                                            case SwiftFreight::TYPE_AIR:
                                                echo '<i class="fa fa-lg fa-plane" title="air"></i>';
                                                break;
                                            case SwiftFreight::TYPE_LAND:
                                                echo '<i class="fa fa-lg fa-truck" title="land"></i>';
                                                break;
                                            case SwiftFreight::TYPE_SEA:
                                                echo '<i class="fa fa-lg fa-anchor" title="sea"></i>';
                                                break;
                                            default:
                                                echo '<i class="fa fa-lg fa-question" title="unknown"></i>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $ft->order->getReadableName() }}
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $ft->freight_company }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                    </div>
                    @endif
                    @if(count($freightTomorrow))
                    <div class="well well-sm col-lg-6 col-xs-12">
                        <h4>Tomorrow</h4>
                        <table class="table table-hover table-striped table-condensed">
                            @foreach($freightTomorrow as $ft)
                            <?php
                                switch($ft->order->business_unit)
                                {
                                    case SwiftOrder::SCOTT_CONSUMER:
                                        $className = "bg-color-orange txt-color-white";
                                        break;
                                    case SwiftOrder::SCOTT_HEALTH;
                                        $className = "bg-color-green txt-color-white";
                                        break;
                                    case SwiftOrder::SEBNA:
                                        $className = "bg-color-blue txt-color-white";
                                        break;
                                }
                            ?>
                            <tr data-url="{{ Helper::generateURL($ft->order) }}">
                                <td class="{{ $className }}">
                                    <?php
                                    switch($ft->freight_type)
                                    {
                                        case SwiftFreight::TYPE_AIR:
                                            echo '<i class="fa fa-lg fa-plane" title="air"></i>';
                                            break;
                                        case SwiftFreight::TYPE_LAND:
                                            echo '<i class="fa fa-lg fa-truck" title="land"></i>';
                                            break;
                                        case SwiftFreight::TYPE_SEA:
                                            echo '<i class="fa fa-lg fa-anchor" title="sea"></i>';
                                            break;
                                        default:
                                            echo '<i class="fa fa-lg fa-question" title="unknown"></i>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="{{ $className }}">
                                    {{ $ft->order->getReadableName() }}
                                </td>
                                <td class="{{ $className }}">
                                    {{ $ft->freight_company }}
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif
                </div>
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="ot-overview-transit" data-widget-deletebutton="false" data-widget-colorbutton="false" data-widget-fullscreenbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false">
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
                            @include('order-tracking.overview_transitforeign')
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
            </article>
        </div>
    </section>
</div>

@stop