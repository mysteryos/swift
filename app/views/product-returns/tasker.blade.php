@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">
    <div class="ribbon-button-alignment hidden-xs">
        <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
    </div>
    <div class="pull-right hidden-xs whos-online"></div>
    <div class="ribbon-button-alignment-xs visible-xs">
        <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>

    </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="{{$task['js']}}" data-urljs="{{$task['urljs']}}">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-reply"></i>
                    Product Returns
                <span>&gt;
                    Tasks
                </span>
            </h1>
        </div>
    </div>
    <ul class="nav nav-tabs row-space-4">
        @if($currentUser->isSuperUser())
        <li class="@if($type === "all"){{ "active" }}@endif">
            <a href="/{{$rootURL}}/tasks/all" class="pjax">All</a>
        </li>
        @endif
        @foreach($taskList as $t)
            @if(in_array(true,$t['permission']))
                <li class="@if($t['type'] == $type){{ "active" }}@endif">
                    <a href="/{{$rootURL}}/tasks/{{ $t['type'] }}" class="pjax">{{ $t['name'] }}</a>
                </li>
            @endif
        @endforeach
    </ul>
    @include($task['view'])
</div>
@stop