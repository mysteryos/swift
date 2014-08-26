@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-gear"></i> Icon Left</a>
<!--            <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa fa-grid"></i> Change Grid</span>
            <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa fa-plus"></i> Add</span>
            <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa fa-search"></i> <span class="hidden-mobile">Search</span></span>-->
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="inbox">
    <div class="inbox-nav-bar no-content-padding">
            <h1 class="page-title txt-color-blueDark hidden-tablet"><i class="fa fa-fw fa-wrench"></i> Machines &nbsp;
            </h1>

            <div class="btn-group hidden-desktop visible-tablet">
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            Machines <i class="fa fa-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-left">
                            <li>
                                    <a href="javascript:void(0);" class="inbox-load">All <i class="fa fa-check"></i></a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Broken</a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Fixed</a>
                            </li>
                    </ul>

            </div>

            <div class="inbox-inline-actions">
                    <div class="btn-group">
                        <a href="javascript:void(0);" rel="tooltip" title="" data-placement="bottom" data-original-title="Check All" class="btn btn-default"><strong><i class="fa fa-check fa-lg"></i></strong></a>
                        <a href="javascript:void(0);" rel="tooltip" title="" data-placement="bottom" data-original-title="Mark Important" class="btn btn-default inbox-view"><strong><i class="fa fa-exclamation fa-lg text-danger"></i></strong></a>
                        <a href="javascript:void(0);" rel="tooltip" title="" data-placement="bottom" data-original-title="Move to folder" class="btn btn-default inbox-checked"><strong><i class="fa fa-folder-open fa-lg"></i></strong></a>
                        <a href="javascript:void(0);" rel="tooltip" title="" data-placement="bottom" data-original-title="Delete" class="deletebutton btn btn-default inbox-checked"><strong><i class="fa fa-trash-o fa-lg"></i></strong></a>
                    </div>
            </div>

            <a href="/nespresso-crm/createmachine" id="compose-mail-mini" class="btn btn-primary pull-right hidden-desktop visible-tablet pjax"> <strong><i class="fa fa-file fa-lg"></i></strong> </a>

            <div class="btn-group pull-right inbox-paging">
                    <a href="javascript:void(0);" class="btn btn-default btn-sm"><strong><i class="fa fa-chevron-left"></i></strong></a>
                    <a href="javascript:void(0);" class="btn btn-default btn-sm"><strong><i class="fa fa-chevron-right"></i></strong></a>
            </div>
            <span class="pull-right"><strong>1-30</strong> of <strong>200</strong></span>

    </div>

    <div id="inbox-content" class="inbox-body no-content-padding">

            <div class="inbox-side-bar">

                    <a href="/nespresso-crm/createmachine" id="compose-mail" class="btn btn-primary btn-block pjax"> <strong>Create</strong> </a>

                    <h6> Folder <a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Refresh" class="pull-right txt-color-darken"><i class="fa fa-refresh"></i></a></h6>

                    <ul class="inbox-menu-lg">
                            <li class="active">
                                    <a class="inbox-load" href="javascript:void(0);"> All </a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Broken</a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Fixed</a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Sales</a>
                            </li>                            
                    </ul>

                    <h6> Quick Access <a href="javascript:void(0);" rel="tooltip" title="" data-placement="right" data-original-title="Add Another" class="pull-right txt-color-darken"><i class="fa fa-plus"></i></a></h6>

                    <ul class="inbox-menu-sm">
                            <li>
                                    <a href="javascript:void(0);">Starred</a>
                            </li>
                            <li>
                                    <a href="javascript:void(0);">Important</a>
                            </li>
                    </ul>

            </div>

            <div class="table-wrap custom-scroll animated fast fadeInRight">
                @include('nespresso-crm.machines-list')
            </div>

            <div class="inbox-footer">

                    <div class="row">

                            <div class="col-xs-6 col-sm-11 text-right">
                                    <div class="txt-color-white inline-block">
                                            <i class="txt-color-blueLight hidden-mobile">Last account activity <i class="fa fa-clock-o"></i> 52 mins ago |</i> Displaying <strong>44 of 259</strong>
                                    </div>
                            </div>

                    </div>

            </div>
        
    </div>
</div>

@stop