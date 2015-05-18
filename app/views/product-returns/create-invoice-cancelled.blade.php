@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

    <div class="ribbon-button-alignment hidden-xs">
        <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
        <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
    </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="pr_invoice_cancelled" data-urljs="{{Bust::url('/js/swift/swift.pr_invoice_cancelled.js')}}">
    <div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<!-- PAGE HEADER -->
			<i class="fa-fw fa fa-map-marker"></i>
				Product Returns
			<span>&gt;
				Create - Invoice Cancelled
			</span>
		</h1>
	</div>
    </div>

    <!-- widget grid -->
    <section id="widget-grid" class="">

	<!-- START ROW -->

	<div class="row">
        <form id="invoice_cancelled_form" action="/{{$rootURL}}/create-invoice-cancelled" method="post" class="form-horizontal" name="pr_cancelledinvoice">
            <!-- NEW COL START -->
            <article class="col-xs-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="widget-form" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false">

                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>Create Form </h2>

                    </header>
                    <div>
                        <div class="widget-body">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4 col-md-2">Invoice Number</label>
                                        <div class="controls col-xs-8 col-md-10">
                                            <input type="hidden" id="invoicecode_autocomplete" name="invoice_code" class="full-width" autocomplete="off" name="invoice_code" value="" />
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </fieldset>
                        </div>
                    </div>
                </div>


                <div class="jarviswidget" id="product-widget" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-beer"></i></span>
                        <h2>Products</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <div id="product-container">
                                <p class="col-xs-12 text-center">Product Information will Appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <div class="row">
                <div class="col-xs-12">
                    <button id="btn-reset" class="btn btn-default col-xs-offset-2 col-xs-3" type="reset">Reset</button>
                    <button id="btn-publish" class="btn btn-danger col-xs-offset-1 col-xs-3" type="submit">Publish</button>
                </div>
            </div>
        </form>
	</div>

	<!-- END ROW -->

    </section>
    <!-- end widget grid -->
</div>

@stop