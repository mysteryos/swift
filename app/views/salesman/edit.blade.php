@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
            @if($isAdmin)<a class="btn btn-default btn-delete" href="/{{ $rootURL }}/@if($form->deleted_at !== null){{ "restore" }}@else{{ "delete" }}@endif/{{ urlencode(Crypt::encrypt($form->id)) }}" rel="tooltip" data-original-title="@if($form->deleted_at !== null) {{ "Restore" }} @else {{ "Delete" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($form->deleted_at !== null) {{ "fa-undo" }} @else {{ "fa-trash-o" }} @endif"></i></a>@endif
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
            @if($isAdmin)<a class="btn btn-default btn-delete" href="/{{ $rootURL }}/@if($form->deleted_at !== null){{ "restore" }}@else{{ "delete" }}@endif/{{ urlencode(Crypt::encrypt($form->id)) }}" rel="tooltip" data-original-title="@if($form->deleted_at !== null) {{ "Restore" }} @else {{ "Delete" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($form->deleted_at !== null) {{ "fa-undo" }} @else {{ "fa-trash-o" }} @endif"></i></a>@endif
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="@if($edit){{"salesman_edit"}}@else{{"salesman_view"}}@endif" dataurljs="@if($edit){{Bust::url('/js/swift/swift.salesman_edit.js')}}@else{{Bust::url('/js/swift/swift.salesman_view.js')}}@endif">
    <input type="hidden" name="id" id="id" value="{{ Crypt::encrypt($form->id) }}" />
    <input type="hidden" name="last_update" id="last_update" value="{{ $form->updated_at }}" />
    <input type="hidden" name="channel_name" id="channel_name" value="{{ $form->channelName() }}" />
    <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
    <input type="hidden" id="project-name" value='<i class="fa-fw fa {{ $form->getIcon() }}"></i> {{ $form->name }} (ID: {{ $form->id }})'/>
    <input type="hidden" id="project-id" value='{{ $form->channelName() }}' />
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-8">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-map-marker"></i>
                    Salesman
                <span>&gt;
                    {{ $form->getReadableName() }}
                </span>
            </h1>
        </div>
        <div class="hidden-xs hidden-sm col-md-4 col-lg-4">
            <h1 class="page-title">
                <span>Last update was by <?php echo Helper::getUserName($activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($activity[0]->created_at))}}" data-livestamp="{{strtotime($activity[0]->created_at)}}"></abbr></span>
            </h1>
        </div>
    </div>

    @if(isset($message) && $message !== false)
        <div class="row">
            <article class="col-xs-12">
                @foreach($message as $m)
                    <?php
                        switch($m['type'])
                        {
                            case 'warning':
                                echo '<div class="alert alert-warning fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-warning"></i>
                                    <strong>Warning</strong> ';
                                break;
                            case 'success':
                                echo '<div class="alert alert-success fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-check"></i>
                                    <strong>Success</strong> ';
                                break;
                            case 'danger':
                                echo '<div class="alert alert-danger fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-times"></i>
                                    <strong>Error!</strong> ';
                                break;
                            default:
                            case 'info':
                                echo '<div class="alert alert-info fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-info"></i>
                                    <strong>Info!</strong> ';
                                break;
                        }
                        echo $m['msg']."</div>";
                    ?>
                @endforeach
            </article>
        </div>
    @endif

<!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">

		<!-- NEW COL START -->
		<article class="col-lg-8 col-xs-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="salesman-generalInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>General Info </h2>
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <form class="form-horizontal">
                                                @include('salesman.edit_generalinfo')
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->

                        <!-- Widget Clients-->
			<div class="jarviswidget" id="salesman-clients" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Clients </h2>
                                        @if($edit)
                                            <div class="widget-toolbar" role="menu">
                                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                                            </div>
                                        @endif
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <form class="form-horizontal">
                                                    @if(count($form->client))
                                                        @foreach($form->client as &$c)
                                                            <?php $c->id = Crypt::encrypt($c->id); ?>
                                                            @include('salesman.edit_client',array('c'=>$c))
                                                        @endforeach
                                                    @else
                                                        @include('salesman.edit_client')
                                                    @endif
                                                    @include('salesman.edit_client',array('dummy'=>true,'c'=>null))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->

                        <!-- Widget Budget-->
			<div class="jarviswidget" id="salesman-budget" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Budget </h2>
                                        @if($edit)
                                            <div class="widget-toolbar" role="menu">
                                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                                            </div>
                                        @endif
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <form class="form-horizontal">
                                                    @if(count($form->salesbudget))
                                                        @foreach($form->salesbudget as &$b)
                                                            <?php $b->id = Crypt::encrypt($b->id); ?>
                                                            @include('salesman.edit_budget',array('b'=>$b))
                                                        @endforeach
                                                    @else
                                                        @include('salesman.edit_budget')
                                                    @endif
                                                    @include('salesman.edit_budget',array('dummy'=>true,'b'=>null))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->-
                </article>
                <!-- NEW COL END -->

                <!-- NEW COL START -->
                <article class="col-lg-4 col-xs-12">

                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="salesman-swiftchat" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                           <header>
                                   <span class="widget-icon"> <i class="fa fa-comment"></i> </span>
                                   <h2>Chat </h2>
                           </header>
                           <!-- widget div-->
                           <div>
                                   <!-- widget content -->
                                   <div class="widget-body widget-hide-overflow no-padding">
                                       @include('comments', array('commentable' => $form, 'comments' => $comments))
                                   </div>
                                   <!-- end widget content -->
                           </div>
                           <!-- end widget div -->
                    </div>
                    <!-- end widget -->

                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="salesman-actionlog" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                            <header>
                                    <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                    <h2>Activity </h2>
                            </header>
                            <!-- widget div-->
                            <div>
                                    <!-- widget content -->
                                    <div class="widget-body nopadding">
                                        <div class="activity-container">
                                            @include('salesman.edit_activity')
                                        </div>
                                    </div>
                                    <!-- end widget content -->
                            </div>
                            <!-- end widget div -->
                    </div>
                    <!-- end widget -->

                </article>
                <!-- NEW COL END -->
        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop