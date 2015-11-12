@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="fc_edit" data-urljs="{{Bust::url('/js/swift/swift.fc_edit.js')}}">
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
				Freight Company
			<span>&gt;
				ID: {{ $fc->id }}
			</span>
		</h1>
	</div>
        <div class="hidden-xs hidden-sm col-md-4 col-lg-4 col-md-offset-4 col-lg-offset-4">
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
                        <div class="jarviswidget" id="fc-generalInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                                <header>
                                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>General Info </h2>
                                </header>
                                <!-- widget div-->
                                <div>
                                        <!-- widget content -->
                                        <div class="widget-body">
                                            <form class="form-horizontal">
                                                <input type="hidden" name="id" id="id" value="{{ Crypt::encrypt($fc->id) }}" />
                                                <input type="hidden" name="last_update" id="last_update" value="{{ $fc->updated_at }}" />
                                                <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
                                                <fieldset>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Type*</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="select" data-name="type" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-title="Select Type Of Company" data-value="{{ $fc->type }}" data-source='{{ $type }}'></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Name*</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="name" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->name }}"></a>
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
                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="fc-businessInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                                <header>
                                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Business Info </h2>
                                </header>
                                <!-- widget div-->
                                <div>
                                        <!-- widget content -->
                                        <div class="widget-body">
                                            <form class="form-horizontal">
                                                <fieldset>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">BRN</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="brn" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->brn }}"></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">VAT number</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="vat_no" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->vat_no }}"></a>
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
                        <!-- Widget ID (each widget will need unique ID)-->
                        <div class="jarviswidget" id="fc-contactInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                                <header>
                                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                        <h2>Contact Info </h2>
                                </header>
                                <!-- widget div-->
                                <div>
                                        <!-- widget content -->
                                        <div class="widget-body">
                                            <form class="form-horizontal">
                                                <fieldset>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Address</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="address" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->address }}"></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Telephone number</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="tel" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->tel }}"></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Fax number</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="fax" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->fax }}"></a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">Email</label>
                                                            <div class="col-md-10">
                                                                <a href="#" class="editable" data-type="text" data-name="email" data-pk="{{ Crypt::encrypt($fc->id) }}" data-url="/order-tracking/freightcompanyform" data-value="{{ $fc->email }}"></a>
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
                </article>
                <!-- COL END -->

                <!-- NEW COL START -->
                <article class="col-md-4 col-xs-12">
                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="fc-ticker" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                            <header>
                                    <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                    <h2>Ticker </h2>
                            </header>
                            <!-- widget div-->
                            <div>
                                    <!-- widget content -->
                                    <div class="widget-body nopadding">
                                        <div class="activity-container">
                                            @include('freight-company.edit_ticker',array('ticker'=>$ticker))
                                        </div>
                                    </div>
                                    <!-- end widget content -->
                            </div>
                            <!-- end widget div -->
                    </div>
                    <!-- end widget -->

                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="fc-actionlog" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">

                            <header>
                                    <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                                    <h2>Activity </h2>
                            </header>
                            <!-- widget div-->
                            <div>
                                    <!-- widget content -->
                                    <div class="widget-body nopadding">
                                        <div class="activity-container">
                                            @include('freight-company.edit_activity',array('activity'=>$activity))
                                        </div>
                                    </div>
                                    <!-- end widget content -->
                            </div>
                            <!-- end widget div -->
                    </div>
                    <!-- end widget -->
                </article>

        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop