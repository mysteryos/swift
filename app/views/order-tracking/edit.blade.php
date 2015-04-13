@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            @if($edit)<a class="btn btn-default btn-help" data-href="/order-tracking/help/{{ urlencode(Crypt::encrypt($order->id)) }}" rel="tooltip" data-original-title="Help" data-placement="bottom"><i class="fa fa-lg fa-question"></i></a>@endif
            @if(isset($isSubscribed))
                <button class="btn btn-default btn-togglesubscribe" data-href="{{$subscriptionUrl}}">
                    <i class="fa fa-lg fa-heart-o" rel="tooltip" data-original-title="Subscribe" data-placement="bottom" @if($isSubscribed)style="display:none;"@endif></i>
                    <i class="fa fa-lg fa-heart" rel="tooltip" data-original-title="Unsubscribe" data-placement="bottom" @if(!$isSubscribed)style="display:none;"@endif></i>
                </button>
            @endif
            @if($isAdmin)<a class="btn btn-default btn-mark-important" href="/order-tracking/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode(Crypt::encrypt($order->id)) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>@endif
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && ($isAdmin || $isCreator))<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/order-tracking/cancel/{{ Crypt::encrypt($order->id) }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            @if($edit)<a class="btn btn-default btn-help" data-href="/order-tracking/help/{{ urlencode(Crypt::encrypt($order->id)) }}" rel="tooltip" data-original-title="Help" data-placement="bottom"><i class="fa fa-lg fa-question"></i></a>@endif
            @if(isset($isSubscribed))
                <button class="btn btn-default btn-togglesubscribe" data-href="{{$subscriptionUrl}}">
                    <i class="fa fa-lg fa-heart-o" rel="tooltip" data-original-title="Subscribe" data-placement="bottom" @if($isSubscribed)style="display:none;"@endif></i>
                    <i class="fa fa-lg fa-heart" rel="tooltip" data-original-title="Unsubscribe" data-placement="bottom" @if(!$isSubscribed)style="display:none;"@endif></i>
                </button>
            @endif
            @if($isAdmin)<a class="btn btn-default btn-mark-important" href="/order-tracking/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode(Crypt::encrypt($order->id)) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>@endif            
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && ($isAdmin || $isCreator))<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/order-tracking/cancel/{{ Crypt::encrypt($order->id) }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="@if($edit){{"ot_edit"}}@else{{"ot_view"}}@endif" data-urljs="@if($edit){{Bust::url('/js/swift/swift.ot_edit.js')}}@else{{Bust::url('/js/swift/swift.ot_view.js')}}@endif">
    <input type="hidden" name="id" id="id" value="{{ Crypt::encrypt($order->id) }}" />
    <input type="hidden" name="last_update" id="last_update" value="{{ $order->updated_at }}" />
    <input type="hidden" name="channel_name" id="channel_name" value="{{ $order->channelName() }}" />
    <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
    <input type="hidden" id="project-name" value='<i class="fa-fw fa fa-map-marker"></i> {{ $order->name }} (ID: {{ $order->id }})'/>
    <input type="hidden" id="project-id" value='ot_{{ $order->id }}' />
    <div id="draghover" class="text-align-center">
        <div class="circle bg-color-blue">
            <i class="fa fa-cloud-upload fa-4x"></i><br>
            <h2 class="text-align-center ">Incoming!</h2>
            <p class="text-align-center">Drop your files instantly to upload it!</p>
        </div>
    </div>
    <div class="row">
	<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
		<h1 class="page-title txt-color-blueDark">
			<!-- PAGE HEADER -->
			<i class="fa-fw fa fa-map-marker"></i> 
				Order Process
			<span>&gt;  
				Form ID: {{ $order->id }}
			</span>
		</h1>
	</div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <span id="workflow_status">Current Step: <span class="{{$current_activity['status_class']}}">{{ $current_activity['label'] }}</span></span>
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
			<div class="jarviswidget" id="ot-generalInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>General Info </h2>			
				</header>
                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                        <form class="form-horizontal">
                            @include('order-tracking.edit_generalinfo',array('order'=>$order))
                        </form>
                    </div>
                    <!-- end widget content -->
                </div>
                <!-- end widget div -->
            </div>
            <!-- end widget -->
                        
            <!-- Widget Purchase Order-->
			<div class="jarviswidget" id="ot-purchaseOrder" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>Purchase Order </h2>
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
                            @if(count($order->purchaseOrder))
                                @foreach($order->purchaseOrder as &$p)
                                    <?php $p->id = Crypt::encrypt($p->id); ?>
                                    @include('order-tracking.edit_purchaseorder',array('p'=>$p))
                                @endforeach
                            @else
                                @include('order-tracking.edit_purchaseorder')
                            @endif
                            @include('order-tracking.edit_purchaseorder',array('dummy'=>true,'p'=>null))                                                
                    </form>
                    </div>
                    <!-- end widget content -->
                </div>
                <!-- end widget div -->
            </div>
            <!-- end widget -->
                        
                        <!-- Widget Freight Start-->
			<div class="jarviswidget" id="ot-freight" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Freight </h2>
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
                                                    @if(count($order->freight))
                                                        @foreach($order->freight as &$f)
                                                            <?php $f->id = Crypt::encrypt($f->id); ?>
                                                            @include('order-tracking.edit_freight',array('f'=>$f))
                                                        @endforeach
                                                    @else
                                                        @include('order-tracking.edit_freight')
                                                    @endif
                                                    @include('order-tracking.edit_freight',array('dummy'=>true,'f'=>null))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget freight -->                        
                        
                        <!-- Widget Shipment Start-->
			<div class="jarviswidget" id="ot-shipment" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Shipment </h2>
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
                                                    @if(count($order->shipment))
                                                        @foreach($order->shipment as &$s)
                                                            <?php $s->id = Crypt::encrypt($s->id); ?>
                                                            @include('order-tracking.edit_shipment',array('s'=>$s))
                                                        @endforeach
                                                    @else
                                                        @include('order-tracking.edit_shipment')
                                                    @endif
                                                    @include('order-tracking.edit_shipment',array('dummy'=>true,'s'=>null))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget shipment -->
                        
                        <!-- Widget Storage Start -->
			<div class="jarviswidget" id="ot-storage" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Storage & Demurrage </h2>
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
                                                    @if(count($order->storage))
                                                        @foreach($order->storage as &$sto)
                                                            <?php $sto->id = Crypt::encrypt($sto->id); ?>
                                                            @include('order-tracking.edit_storage',array('sto'=>$sto))
                                                        @endforeach
                                                    @else
                                                        @include('order-tracking.edit_storage')
                                                    @endif
                                                    @include('order-tracking.edit_storage',array('dummy'=>true,'s'=>null))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>                        
                        <!-- end widget Storage -->
                        
                        <!-- Widget Customs START-->
			<div class="jarviswidget" id="ot-customs" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Customs </h2>
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
                                                    @if(count($order->customsDeclaration))
                                                        @foreach($order->customsDeclaration as &$c)
                                                            <?php $c->id = Crypt::encrypt($c->id); ?>
                                                            @include('order-tracking.edit_customs',array('c'=>$c))
                                                        @endforeach
                                                    @else
                                                        @include('order-tracking.edit_customs')
                                                    @endif
                                                    @include('order-tracking.edit_customs',array('dummy'=>true,'c'=>null))
                                                </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget customs -->                        
                        
                        <!-- Widget Reception start-->
			<div class="jarviswidget" id="ot-reception" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Reception </h2>
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
                                                    @if(count($order->reception))
                                                        @foreach($order->reception as &$r)
                                                            <?php $r->id = Crypt::encrypt($r->id); ?>
                                                            @include('order-tracking.edit_reception',array('r'=>$r))
                                                        @endforeach
                                                    @else
                                                        @include('order-tracking.edit_reception')
                                                    @endif
                                                    @include('order-tracking.edit_reception',array('dummy'=>true,'r'=>null))                                                
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget reception -->                         
                </article>
                <!-- NEW COL END -->
                
                <!-- NEW COL START -->
                <article class="col-lg-4 col-xs-12">
                        <!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="ot-docs" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-file-o"></i> </span>
                                        <h2>Docs </h2>
                                        @if($edit)
                                            <div class="widget-toolbar" role="menu">
                                                <a class="btn btn-primary" id="btn-upload" href="javascript:void(0);"><i class="fa fa-plus"></i> Upload</a>
                                            </div>
                                        @endif
				</header>
                                <!-- widget div-->
				<div>
                                    
                                        <!-- widget content -->
					<div class="widget-body">
                                            <div id="upload-preview">
                                                @include('order-tracking.upload',array('doc'=>$order->document,'tags'=>$tags,'dummy'=>true))
                                            </div>                                                
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->
                        
                         <!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="ot-swiftchat" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-comment"></i> </span>
                                        <h2>Chat </h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body widget-hide-overflow no-padding">
                                            @include('comments', array('commentable' => $order, 'comments' => $comments))
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->                          
                        
                        <!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="ot-actionlog" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                        <h2>Activity </h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body nopadding">
                                            <div class="activity-container">
                                                @include('order-tracking.edit_activity',array('activity'=>$activity))
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