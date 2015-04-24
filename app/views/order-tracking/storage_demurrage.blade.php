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
<div id="content" data-js="ot_storage_demurrage" data-urljs="{{Bust::url('/js/swift/swift.ot_storage_demurrage.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-clock-o"></i> Storage/Demurrage &nbsp;</h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        @foreach(SwiftOrder::$business_unit as $k => $bu)
            <li class="@if($business_unit == $k){{ "active" }}@endif">
                    <a href="/{{ $rootURL }}/storage-demurrage-calendar/{{ $k }}" class="pjax">{{ $bu }}</a>
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
                    @if(count($storageToday) || count($storageTomorrow))
                    <div class="well well-sm col-lg-6 col-xs-6">
                        <h3>Storage</h3>
                        @if(count($storageToday))
                            <h4>Today</h4>
                            <table class="table table-hover table-striped table-condensed">
                                @foreach($storageToday as $st)
                                <?php
                                    switch($st->order->business_unit)
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
                                <tr data-url="{{ Helper::generateURL($st->order) }}">
                                    <td class="{{ $className }}">
                                        {{ $st->order->getReadableName() }}
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $st->order->activity['label'] or "(Unknown)" }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                        @if(count($storageTomorrow))
                            <h4>Tomorrow</h4>
                            <table class="table table-hover table-striped table-condensed">
                                @foreach($storageTomorrow as $stm)
                                <?php
                                    switch($stm->order->business_unit)
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
                                <tr data-url="{{ Helper::generateURL($stm->order) }}">
                                    <td class="{{ $className }}">
                                        {{ $stm->order->getReadableName() }}
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $stm->order->activity['label'] or "(Unknown)" }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                    </div>
                    @endif
                    @if(count($demurrageTomorrow) || count($demurrageToday))
                    <div class="well well-sm col-lg-6 col-xs-12">
                        <h3>Demurrage</h3>
                        @if(count($demurrageToday))
                            <h4>Today</h4>
                            <table class="table table-hover table-striped table-condensed">
                                @foreach($demurrageToday as $dt)
                                <?php
                                    switch($dt->order->business_unit)
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
                                <tr data-url="{{ Helper::generateURL($dt->order) }}">
                                    <td class="{{ $className }}">
                                        {{ $dt->order->getReadableName() }}
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $dt->order->activity['label'] or "(Unknown)" }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                        @if(count($demurrageTomorrow))
                            <h4>Tomorrow</h4>
                            <table class="table table-hover table-striped table-condensed">
                                @foreach($demurrageTomorrow as $dtm)
                                <?php
                                    switch($dtm->order->business_unit)
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
                                <tr data-url="{{ Helper::generateURL($dtm->order) }}">
                                    <td class="{{ $className }}">
                                        {{ $dtm->order->getReadableName() }}
                                    </td>
                                    <td class="{{ $className }}">
                                        {{ $dtm->order->activity['label'] or "(Unknown)" }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                    </div>
                    @endif
                </div>
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="ot-storage-demurrage" data-widget-deletebutton="false" data-widget-colorbutton="false" data-widget-fullscreenbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-plane"></i> </span>
                        <h2>Storage/Demurrage </h2>
                        <div class="widget-toolbar">
                                <!-- add: non-hidden - to disable auto hide -->
                                <span>Legend: </span> <button class="bg-color-blue btn color-white">Storage</button> <button class="btn bg-color-red color-white">Demurrage</button>
                        </div>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            @include('order-tracking.overview_storage_demurrage')
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