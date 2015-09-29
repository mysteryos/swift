@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

    <div class="ribbon-button-alignment hidden-xs">
        <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        @include('product-returns.ribbon-create')
    </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="pr_create_ondelivery" data-urljs="{{Bust::url('/js/swift/swift.pr_create_ondelivery.js')}}">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-reply"></i>
                    Product Returns: {{$type_name}}
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
        <form id="pr_create_form" action="/{{$rootURL}}/create" method="post" class="form-horizontal" name="pr_create_form">
            <input type="hidden" name="type" value="{{$type}}" />
            <!-- NEW COL START -->
            <article class="col-xs-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="widget-form" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-togglebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>Create Form </h2>
                        <div class="widget-toolbar" role="menu">
                            <a id="btn-reset" class="btn btn-default" type="button">Reset</a>
                        </div>
                    </header>
                    <div>
                        <div class="widget-body">
                            <fieldset>
                                <div class="form-group">
                                    <div class="checkbox pull-right">
                                        <label>
                                            <input type="checkbox" id="check_new_after_save" class="checkbox style-0">
                                            <span>Create new after save</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-xs-4 col-md-2">Customer</label>
                                    <div class="col-xs-8 col-md-10">
                                        <input type="hidden" id="ccode" name="customer_code" class="full-width" autocomplete="off" value="" />
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                                @if($type == \SwiftPR::ON_DELIVERY)
                                    <div class="form-group">
                                        <label class="control-label col-xs-4 col-md-2">Paper Number</label>
                                        <div class="col-xs-8 col-md-10">
                                            <input type="number" name="paper_number" id="paper_number" autocomplete="off" class="form-control" placeholder="Enter the number of the RFRF paper" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4 col-md-2">Driver</label>
                                        <div class="controls col-xs-8 col-md-10">
                                            <select name="driver_id" id="driver_id" class="full-width">
                                                <option></option>
                                                @foreach($driver_list_array as $d_id => $d_label)
                                                    <option value="{{$d_id}}">{{$d_label}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </fieldset>
                            <fieldset>
                                <legend>Products</legend>
                                <div class="container-fluid" id="product-table">
                                    <div class="row hide dummy product-row">
                                        <div class="col-lg-3 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Name*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="hidden" name="product[][jde_itm]" value="" class="full-width product-id" autocomplete="off" disabled />
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Qty at Client*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" title="Quantity Client" name="product[][qty_client]" value="" class="form-control product-qty-client" placeholder="Client" autocomplete="off" disabled/>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Qty at Pickup*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" title="Quantity Pickup" name="product[][qty_pickup]" value="" class="form-control" placeholder="Pickup" autocomplete="off" disabled/>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Qty at T.Picking*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" title="Quantity Triage Picking" name="product[][qty_triage_picking]" value="" class="form-control" placeholder="Picking" autocomplete="off" disabled/>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Qty at T.Disposal*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" title="Quantity Triage Disposal" name="product[][qty_triage_disposal]" value="" class="form-control" placeholder="Disposal" autocomplete="off" disabled/>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Invoice No*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" name="product[][invoice_id]" value="" class="form-control" placeholder="Invoice Number" autocomplete="off" disabled/>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Reason*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <select name="product[][reason_id]" disabled class="form-control">
                                                    <option value="" selected disabled>Please select a reason</option>
                                                    @foreach($product_reason_codes_array as $reason_key => $reason_text)
                                                        <option value="{{$reason_key}}">{{$reason_text}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Comment</label>
                                            <div class="col-lg-12 col-md-9">
                                                <textarea class="form-control" name="product[][reason_others]" cols="3" rows="1" placeholder="Comment" disabled></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-1">
                                            <button class="btn btn-danger btn-delete-product col-md-3 col-md-offset-9 col-lg-offset-1 col-lg-11" type="button">
                                                <i class="fa fa-trash-o" title="delete"></i><span class="hidden-lg"> Delete</span>
                                            </button>
                                        </div>
                                        <hr class="hidden-lg"/>
                                    </div>
                                </div>
                                <div class="row row-space-top-4">
                                    <div class="col-xs-12">
                                        <button type="button" id="btn-add-product" class="btn btn-default col-md-offset-2 col-md-3 col-sm-offset-1 col-xs-4"></i>Add Product</button>
                                        <button type="button" id="btn-add-product-invoice" class="btn btn-default col-md-offset-1 col-md-3 col-sm-offset-1 col-xs-4" data-target="#productFromInvoiceModal" data-toggle="modal"></i>Add Product From Invoice</button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </article>
            <div class="row">
                <div class="col-xs-12">
                    <a id="btn-save-draft" class="btn btn-default col-xs-offset-2 col-xs-3">Save Draft</a>
                    <a id="btn-publish" class="btn btn-primary col-xs-offset-1 col-xs-3">Publish</a>
                </div>
            </div>
        </form>
	</div>

        <div class="modal fade" id="productFromInvoiceModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            &times;
                        </button>
                        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-lg fa-file-o"></i> Add Products From Invoice</h4>
                    </div>
                    <form name="product_by_invoice_form" class="form-horizontal" id="productFromInvoiceForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Invoice Number*</label>
                                <div class="col-md-10">
                                    <input type="hidden" class="full-width" id="invoice_id" name="invoice_id" placeholder="Type in the invoice number" />
                                </div>
                            </div>
                            <fieldset>
                                <legend>Optional</legend>
                                <div class="form-group">
                                    <label class="col-xs-2 control-label">&nbsp;</label>
                                    <div class="col-xs-10">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="qty_client_included" value="1">
                                                Include Qty Client </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-2 control-label">&nbsp;</label>
                                    <div class="col-xs-10">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="qty_pickup_included" value="1" id="qty_pickup_included">
                                                Include Qty Pickup </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" data-qtyincluded="1">
                                    <label class="col-xs-2 control-label">All Qty</label>
                                    <div class="col-xs-10">
                                        <label class="radio radio-inline">
                                            <input type="radio" name="qty_to" value="picking"/>
                                            To Picking </label>
                                        <label class="radio radio-inline"/>
                                        <input type="radio" name="qty_to" value="disposal">
                                        To Disposal </label>
                                        <label class="radio radio-inline" />
                                        <input type="radio" name="qty_to" value="n/a">
                                        N/A </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-2 control-label">Reason</label>
                                    <div class="col-xs-10">
                                        <select class="form-control" name="reason_id">
                                            <option>Select a reason</option>
                                            @foreach($product_reason_codes_array as $rkey => $rvalue)
                                                <option value="{{$rkey}}">{{$rvalue}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-2 control-label">Reason Comment</label>
                                    <div class="col-xs-10">
                                        <input name="reason_comment" type="text" class="form-control" placeholder="Type in a comment" />
                                    </div>
                                </div>
                            </fieldset>
                            <hr/>
                            <div class="row">
                                <div class="col-xs-12" id="product-list">
                                    <p class="text-center col-xs-12">Product Info will appear here</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default col-xs-3 col-xs-offset-3" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info col-xs-3 col-xs-offset-3" id="btn-add-product-to-form" name="save_selected"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

	<!-- END ROW -->

    </section>
    <!-- end widget grid -->
</div>

@stop