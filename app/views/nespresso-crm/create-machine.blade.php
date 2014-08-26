@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <ol class="breadcrumb"><li>Home</li><li>Nespresso CRM - Machine</li><li>Create</li></ol>            
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="ncrm_machinecreate">
    <div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<!-- PAGE HEADER -->
			<i class="fa-fw fa fa-wrench"></i> 
				Nespresso CRM - Machine
			<span>&gt;  
				Create
			</span>
		</h1>
	</div>
    </div>
    <!-- widget grid -->
        <section id="widget-grid" class="">

            <!-- START ROW -->

            <div class="row">

                    <!-- NEW COL START -->
                    <article class="col-md-8 col-xs-12">

                            <!-- Widget ID (each widget will need unique ID)-->
                            <div class="jarviswidget" id="widget-form" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false" data-widget-sortable="false">

                                    <header>
                                            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                                            <h2>Create Machine </h2>

                                    </header>
                                    <!-- widget div-->
                                    <div>
                                            <!-- widget content -->
                                            <div class="widget-body no-padding">
                                                <form action="/nespresso-crm/createmachine" method="POST" id="nespresso-crm-create-form" enctype="multipart/form-data" class="form-horizontal" name="nespresso-crm-create" novalidate="novalidate">                                            
                                                    <input type="hidden" name="id" value="" />
                                                    <div class="panel-group smart-accordion-default" id="accordion">
                                                            <div class="panel panel-default" id="generalInfo">
                                                                    <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                    <a data-toggle="collapse" href="#accordion-1"> 
                                                                                        <i class="fa fa-lg fa-angle-down pull-right"></i> 
                                                                                        <i class="fa fa-lg fa-angle-up pull-right"></i> 
                                                                                        General info 
                                                                                    </a>
                                                                            </h4>
                                                                    </div>
                                                                    <div id="accordion-1" class="panel-collapse collapse in">
                                                                            <div class="panel-body">
                                                                                <fieldset>
                                                                                        <div class="form-group">
                                                                                            <label class="col-md-2 control-label">Client Name*</label>
                                                                                            <div class="col-md-10">
                                                                                                <input type="hidden" style="width:100%" id="selectcustomer" name="customer_id" />
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="col-md-2 control-label">Date of purchase*</label>
                                                                                            <div class="col-md-10">
                                                                                                <div class="row">
                                                                                                    <div class="col-sm-12">
                                                                                                        <div class="input-group">
                                                                                                            <input type="text" class="datepicker hasDateicker form-control" name="purchase_date" data-dateformat="dd/mm/yy" />
                                                                                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="col-md-2 control-label">Machine</label>
                                                                                            <div class="col-md-10">
                                                                                                <input type="hidden" style="width:100%" id="selectmachine" name="machine_id" />
                                                                                            </div>
                                                                                        </div>
<!--                                                                                            <div class="form-group col-md-6">
                                                                                                <label class="col-md-4 control-label">Color</label>
                                                                                                <div class="col-md-8">
                                                                                                    <input type="text" class="form-control" name="serial_no"/>
                                                                                                </div>
                                                                                            </div>-->
                                                                                        <div class="row">
                                                                                            <div class="form-group col-md-6">
                                                                                                <label class="col-md-4 control-label">Serial Number</label>
                                                                                                <div class="col-md-8">
                                                                                                    <input type="text" class="form-control" name="serial_no"/>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="form-group col-md-6">
                                                                                                <label class="col-md-4 control-label">Warranty Period</label>
                                                                                                <div class="col-md-8">
                                                                                                    <div class="row">
                                                                                                        <div class="col-sm-12">
                                                                                                            <div class="input-group">
                                                                                                                <input type="text" class="form-control" name="warranty"/>
                                                                                                                <span class="input-group-addon">years</span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                </fieldset>
                                                                            </div>
                                                                    </div>
                                                            </div>
                                                        <div class="form-actions">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <button class="btn btn-default" id="save-draft" type="submit">
                                                                            Save
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- end widget content -->

                                    </div>
                                    <!-- end widget div -->

                            </div>
                            <!-- end widget -->

                    </article>
                    <article class="col-md-4 col-xs-12">
                            <!-- start widget document -->
                            <div class="jarviswidget" id="widget-doc" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false" data-widget-sortable="false">
                                    <header>
                                            <span class="widget-icon"> <i class="fa fa-file-text-o"></i> </span>
                                            <h2>Documents </h2>				
                                    </header>

                                    <!-- widget div-->
                                    <div>

                                            <!-- widget edit box -->
                                            <div class="jarviswidget-editbox">
                                                    <!-- This area used as dropdown edit box -->

                                            </div>
                                            <!-- end widget edit box -->

                                            <!-- widget content -->
                                            <div class="widget-body no-padding">
                                                <form action="/order-tracking/upload" class="dropzone" id="ordertrackingdropzone"></form>
                                            </div>
                                    </div>
                            </div>
                            <!-- end widget document -->
                    </article>
            </div>

            <!-- END ROW -->

        </section>
        <!-- end widget grid -->
</div>

@stop    