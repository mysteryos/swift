@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <a class="btn btn-default pjax" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="dashboard">

        <div class="row">
                <div class="col-xs-12">
                        <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-home"></i> Dashboard <span>> My Dashboard</span></h1>
                </div>
        </div>
        <!-- widget grid -->
        <section id="widget-grid" class="">
                <!-- row -->
                <div class="row">

                        <article class="col-sm-12 col-md-12 col-lg-6">
                                
                                @if(!$currentUser->isSuperUser())
                                <!-- new widget -->
                                <div class="jarviswidget jarviswidget-color-blue" id="dashboard-todolist" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-deletebutton="false">

                                        <header>
                                                <span class="widget-icon"> <i class="fa fa-check txt-color-white"></i></span>
                                                <h2> Todo's </h2>
                                        </header>

                                        <!-- widget div-->
                                        <div>
                                                <div class="widget-body widget-body-compressed no-padding .infinite-body-scroll" id="dashboard-todolist-body">
                                                        <!-- content goes here -->
                                                        <table class="table table-striped table-hover table-responsive">
                                                            @include('dashboard.todolist')
                                                        </table>
                                                        <div class="infinite-scroll-loading text-center"></div>
                                                        <!-- end content -->
                                                </div>
                                        </div>
                                        <!-- end widget div -->
                                </div>
                                <!-- end widget -->
                                @endif
                                
                                @if($isAdmin)
                                <!-- new widget -->
                                <div class="jarviswidget jarviswidget-color-blueDark" id="dashboard-latestworkflow" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-deletebutton="false" data-widget-togglebutton="false">

                                        <header>
                                                <span class="widget-icon"> <i class="fa fa-check txt-color-white"></i></span>
                                                <h2> Latest Workflows </h2>
                                        </header>

                                        <!-- widget div-->
                                        <div>
                                                <div class="widget-body widget-body-compressed no-padding .infinite-body-scroll" id="dashboard-latestworkflow-body">
                                                    <table class="table table-striped table-hover table-responsive">
                                                        @include('dashboard.latestworkflow')
                                                    </table>
                                                    <div class="infinite-scroll-loading text-center"></div>
                                                </div>
                                        </div>
                                        <!-- end widget div -->
                                </div>                                
                                <!-- end widget -->
                                @endif
                               
                        </article>

                        <article class="col-sm-12 col-md-12 col-lg-6">
                                <!-- new widget -->
                                <div class="jarviswidget" id="dashboard-stories" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-fullscreenbutton="false" data-widget-togglebutton="false" data-widget-load="/dashboard/stories">
                                    <header>
                                            <span class="widget-icon"> <i class="fa fa-globe"></i> </span>
                                            <h2>Stories</h2>                               
                                    </header>
                                            <!-- widget div-->
                                            <div>
                                                    <!-- widget content -->
                                                    <div class="widget-body widget-body-compressed" id="timeline-body">
                                                        <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                                    </div>
                                            </div>
                                </div>
                                <!-- end widget -->
                                
                                <!-- new widget -->
                                <div class="jarviswidget jarviswidget-color-greenLight" id="dashboard-syshealth" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-deletebutton="false" data-widget-load="/dashboard/latenodes">

                                        <header>
                                                <span class="widget-icon"> <i class="fa fa-hospital-o txt-color-white"></i></span>
                                                <h2> System Health </h2>
                                        </header>

                                        <!-- widget div-->
                                        <div>
                                                <div class="widget-body  no-padding">
                                                        <!-- content goes here -->
                                                        <p class="text-center h3"><i class="fa fa-lg fa-spin fa-refresh"></i> Loading</p>
                                                        <!-- end content -->
                                                </div>
                                        </div>
                                        <!-- end widget div -->
                                </div>
                                <!-- end widget -->                            
                        </article>

                </div>

                <!-- end row -->

        </section>
        <!-- end widget grid -->
</div>
<!-- END MAIN CONTENT -->


@stop