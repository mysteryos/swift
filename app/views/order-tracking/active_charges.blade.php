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
<div id="content" data-js="ot_active_charges" data-urljs="{{Bust::url('/js/swift/swift.ot_active_charges.js')}}">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-bomb"></i> Active Charges &nbsp;</h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        @foreach(SwiftOrder::$business_unit as $k => $bu)
            <li class="@if($business_unit == $k){{ "active" }}@endif">
                    <a href="/{{ $rootURL }}/active-charges/{{ $k }}" class="pjax">{{ $bu }}</a>
            </li>
        @endforeach
    </ul>

    <!-- START ROW -->
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget" id="ot-storage-charges" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false">
                    <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <h2>Storage</h2>
                    </header>
                    <!-- widget div-->
                    <div>
                            <!-- widget content -->
                            <div class="widget-body no-padding">
                                <div class="widget-body-toolbar"></div>
                                @if(count($activeStorage))
                                    <table class="table table-hover" id="storage_table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    Id
                                                </th>
                                                <th>
                                                    Name
                                                </th>
                                                <th>
                                                    Current Step
                                                </th>
                                                <th>
                                                    Number of Days
                                                </th>
                                                <th>
                                                    Active Charges (USD)
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activeStorage as $as)
                                                <tr>
                                                    <td><a href="{{\Helper::generateURL($as->order)}}" class="pjax">#{{$as->order->id}}</a></td>
                                                    <td>{{$as->order->getReadableName()}}</td>
                                                    <td>{{$as->order->activity['label']}}</td>
                                                    <td>{{$as->numberOfDays}}</td>
                                                    <td>@if(is_numeric($as->cost)){{$as->cost}}@else{{$as->cost}}@endif</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center col-xs-12 h3">
                                        No active storage charges
                                    </div>
                                @endif
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget" id="ot-demurrage-charges" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false">
                    <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <h2>Demurrage</h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body no-padding">
                            <div class="widget-body-toolbar"></div>
                            @if(count($activeDemurrage))
                                <table class="table table-hover" id="storage_table">
                                    <thead>
                                        <tr>
                                            <th>
                                                Id
                                            </th>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Current Step
                                            </th>
                                            <th>
                                                Number of Days
                                            </th>
                                            <th>
                                                Active Charges (USD)
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeDemurrage as $ad)
                                            <tr>
                                                <td><a href="{{\Helper::generateURL($ad->order)}}" class="pjax">#{{$ad->order->id}}</a></td>
                                                <td>{{$ad->order->getReadableName()}}</td>
                                                <td>{{$ad->order->activity['label']}}</td>
                                                <td>{{$ad->numberOfDays}}</td>
                                                <td>@if(is_numeric($ad->cost)){{$ad->cost}}@else{{$ad->cost}}@endif</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center col-xs-12 h3">
                                    No active demurrage charges
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
