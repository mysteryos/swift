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
<div id="content" data-js="salescommission_commissionview" data-urljs="{{Bust::url('/js/swift/swift.salescommission_commissionview.js')}}">
    <input type="hidden" name="id" id="id" value="{{ Crypt::encrypt($commissions->first()->id) }}" />
    <input type="hidden" name="last_update" id="last_update" value="{{ $commissions->first()->updated_at }}" />
    <input type="hidden" name="channel_name" id="channel_name" value="{{ $commissions->first()->getChannelName() }}" />
    <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
    <input type="hidden" id="project-name" value='<i class="fa-fw fa fa-money"></i> Commisions (ID: {{ $commissions->first()->id }})'/>
    <input type="hidden" id="project-id" value='ot_{{ $commissions->first()->id }}' />
    
    <div class="row">
	<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
		<h1 class="page-title txt-color-blueDark">
			<!-- PAGE HEADER -->
			<i class="fa-fw fa fa-money"></i> 
				Commission View
			<span>&gt;  
                                <b>Salesman:</b> {{ \Helper::getUserName($commissions->first()->salesman->user_id,$currentUser) }}
			</span>
		</h1>
	</div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <span><b>Period:</b> {{ $commissions->first()->date_start->toDateString() }} to {{ $commissions->first()->date_end->toDateString() }}</span>
            </h1>
        </div>
        <div class="hidden-xs hidden-sm col-md-4 col-lg-4">
            <h1 class="page-title">
                <span><b>Date Generated:</b> <abbr title="{{date("Y/m/d H:i",strtotime($commissions->first()->created_at))}}" data-livestamp="{{strtotime($commissions->first()->created_at)}}"></abbr></span>
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
		<article class="col-xs-12">
			
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="commission-view-salesman" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-user"></i> </span>
                                        <h2>Salesman</h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <form class="form-horizontal">
                                                @include('sales-commission.commission-view_salesman',array('salesman'=>$commissions->first()->salesman_info_data))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="commission-view-commision-simple" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-money"></i> </span>
                                        <h2>Commission Simple</h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <form class="form-horizontal">
                                                @include('sales-commission.commission-view_commission_simple',array('commissions'=>$commissions))
                                            </form>
                                        </div>
                                        <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                        </div>
                        <!-- end widget -->
                        
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="commission-view-commision-detailed" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-cogs"></i> </span>
                                        <h2>Commission Detailed</h2>			
				</header>
                                <!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body">
                                            <p class="text-center h3"><a class="btn btn-default btn-lg get-detailed" href="/{{ $rootURL }}/commission-detail-calc-view/{{ $salesman_id }}/{{ $date_start }}">Load detailed calculation</a></p>
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