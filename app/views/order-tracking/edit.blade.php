@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax" href="/order-tracking/forms" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            <a class="btn btn-default" rel="tooltip" data-original-title="Send" data-placement="bottom"><i class="fa fa-lg fa-mail-forward"></i></a>
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS)<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/order-tracking/cancel/{{ Crypt::encrypt($order->id) }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>
    
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax" href="/order-tracking/forms" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            <a class="btn btn-default" rel="tooltip" data-original-title="Send" data-placement="bottom"><i class="fa fa-lg fa-mail-forward"></i></a>
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS)<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/order-tracking/cancel/{{ Crypt::encrypt($order->id) }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>    

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="@if($edit){{"ot_edit"}}@else{{"ot_view"}}@endif">
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
                <span>Current Step: <span class="{{$current_activity['status_class']}}">{{ $current_activity['label'] }}</span></span>
            </h1>
        </div>
        <div class="hidden-xs hidden-sm col-md-4 col-lg-4">
            <h1 class="page-title">
                <span>Last update was by <?php echo Helper::getUserName($activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($activity[0]->created_at))}}" data-livestamp="{{strtotime($activity[0]->created_at)}}"></abbr></span>
            </h>
        </div>
    </div>
    
<!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">

		<!-- NEW COL START -->
		<article class="col-md-8 col-xs-12">
			
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
                                                <input type="hidden" name="id" id="id" value="{{ Crypt::encrypt($order->id) }}" />
                                                <input type="hidden" name="last_update" id="last_update" value="{{ $order->updated_at }}" />
                                                <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
                                                <input type="hidden" id="project-name" value='<i class="fa-fw fa fa-map-marker"></i> {{ $order->name }} (ID: {{ $order->id }})'/>
                                                <fieldset>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Business Unit*</label>
                                                            <div class="col-md-10">
                                                                <a href="#" id="business_unit" class="editable" data-type="select" data-name="business_unit" data-pk="{{ Crypt::encrypt($order->id) }}" data-url="/order-tracking/generalinfo" data-title="Select Business Unit" data-value="{{ $order->business_unit }}" data-source='{{ $business_unit }}'></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Name*</label>
                                                            <div class="col-md-10">
                                                                <a href="#" id="name" class="editable" data-type="text" data-name="name" data-pk="{{ Crypt::encrypt($order->id) }}" data-url="/order-tracking/generalinfo" data-value="{{ $order->name }}"></a>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                                <label class="col-md-2 control-label">Description</label>
                                                                <div class="col-md-10">
                                                                    <a href="#" id="description" class="editable" data-type="textarea" data-name="description" data-pk="{{ Crypt::encrypt($order->id) }}" data-url="/order-tracking/generalinfo" data-original-title="Enter a description for this form" @if($order->description != "") data-value="{{ $order->description }}" @endif></a>
                                                                </div>
                                                        </div>
                                                </fieldset>
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
                <article class="col-md-4 col-xs-12">
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
			<div class="jarviswidget" id="ot-actionlog" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                        <h2>Activity </h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body nopadding">
                                            <div id="activity-container">
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