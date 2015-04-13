@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <ol class="breadcrumb"><li>Home</li><li>Freight Company</li><li>Create</li></ol>            
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="fc_create" data-urljs="{{Bust::url('/js/swift/swift.fc_create.js')}}">
    <div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<!-- PAGE HEADER -->
			<i class="fa-fw fa fa-building"></i> 
				Freight Company
			<span>&gt;  
				Create
			</span>
		</h1>
	</div>
    </div>
    
    <!-- widget grid -->
    <section id="widget-grid">

	<!-- START ROW -->

	<div class="row">

		<!-- NEW COL START -->
		<article class="col-xs-12">
			
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="widget-form" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false" data-widget-sortable="false">
				
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Create Form </h2>				
					
				</header>
				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
                                            <form action="/order-tracking/freightcompanyform" method="POST" id="freight-company-create-form" enctype="multipart/form-data" class="form-horizontal" name="freight-company-create" novalidate="novalidate">                                            
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
                                                                                            <label class="col-md-2 control-label">Type*</label>
                                                                                            <div class="col-md-10">
                                                                                                @foreach(SwiftFreightCompany::$type as $t_key=>$t_val)
                                                                                                <label class="radio radio-inline">
                                                                                                    <input type="radio" name="type" value="{{$t_key}}" @if($t_key==1){{"checked"}}@endif autocomplete="off" />
                                                                                                    <span>{{$t_val}}</span>
                                                                                                </label>
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="form-group">
                                                                                            <label class="col-md-2 control-label">Name*</label>
                                                                                            <div class="col-md-10">
                                                                                                 <input type="text" class="form-control" name="name" placeholder="Type in a name" />
                                                                                            </div>
                                                                                        </div>
                                                                                </fieldset>
                                                                        </div>
								</div>
							</div>
                                                    
                                                        <div class="panel panel-default" id="businessInfo">
								<div class="panel-heading">
									<h4 class="panel-title">
                                                                                <a data-toggle="collapse" href="#accordion-2"> 
                                                                                    <i class="fa fa-lg fa-angle-down pull-right"></i> 
                                                                                    <i class="fa fa-lg fa-angle-up pull-right"></i> 
                                                                                     Business info 
                                                                                </a>
                                                                        </h4>
								</div>
								<div id="accordion-2" class="panel-collapse collapse in">
                                                                        <div class="panel-body">
                                                                                <fieldset>
                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">BRN</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="text" class="form-control" name="brn" placeholder="Type in business registration number" />
                                                                                                </div>
                                                                                        </div> 

                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">VAT number</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="text" class="form-control" name="vat_no" placeholder="Type in vat number" />
                                                                                                </div>
                                                                                        </div>                                                                                             
                                                                                </fieldset>
                                                                        </div>
								</div>
							</div>
                                                        <div class="panel panel-default" id="contactInfo">
								<div class="panel-heading">
									<h4 class="panel-title">
                                                                                <a data-toggle="collapse" href="#accordion-3"> 
                                                                                    <i class="fa fa-lg fa-angle-down pull-right"></i> 
                                                                                    <i class="fa fa-lg fa-angle-up pull-right"></i> 
                                                                                     Contact info 
                                                                                </a>
                                                                        </h4>
								</div>
								<div id="accordion-3" class="panel-collapse collapse in">
                                                                        <div class="panel-body">
                                                                                <fieldset>
                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">Address</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="text" class="form-control" name="address" placeholder="Type in address" />
                                                                                                </div>
                                                                                        </div>

                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">Telephone number</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="text" class="form-control" name="tel" placeholder="Type in telephone number" />
                                                                                                </div>
                                                                                        </div> 

                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">Fax number</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="text" class="form-control" name="fax" placeholder="Type in fax number" />
                                                                                                </div>
                                                                                        </div>

                                                                                        <div class="form-group">
                                                                                                <label class="col-md-2 control-label">Email</label>
                                                                                                <div class="col-md-10">
                                                                                                    <input type="email" class="form-control" name="email" placeholder="Type in email" />
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
	</div>

	<!-- END ROW -->

    </section>
    <!-- end widget grid -->
</div>

@stop