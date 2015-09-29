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
<div id="content" data-js="pr_create" data-urljs="{{Bust::url('/js/swift/swift.pr_create.js')}}">
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
                            </fieldset>
                            <fieldset>
                                <legend>Products</legend>
                                <div class="container-fluid" id="product-table">
                                    <div class="row row-space-4 visible-lg">
                                        <div class="col-md-3 form-group text-align-center">
                                            Product
                                        </div>
                                        <div class="col-md-1 form-group text-align-center">
                                            Qty
                                        </div>
                                        <div class="col-md-2 form-group text-align-center">
                                            Invoice Number
                                        </div>
                                        <div class="col-md-2 form-group text-align-center">
                                            Reason
                                        </div>
                                        <div class="col-md-2 form-group text-align-center">
                                            Comment
                                        </div>
                                        <div class="col-md-1 form-group text-align-center">
                                            Pickup
                                        </div>
                                        <div class="col-md-1 form-group">
                                            &nbsp;
                                        </div>
                                    </div>
                                    <div class="row hide dummy product-row">
                                        <div class="col-lg-3 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Name*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="hidden" name="product[][jde_itm]" value="" class="full-width product-id" autocomplete="off" disabled />
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Qty*</label>
                                            <div class="col-lg-12 col-md-9">
                                                <input type="number" name="product[][qty_client]" value="" class="form-control product-qty" placeholder="Quantity" autocomplete="off" disabled/>
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
                                        <div class="col-lg-2 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Comment</label>
                                            <div class="col-lg-12 col-md-9">
                                                <textarea class="form-control" name="product[][reason_others]" cols="3" rows="1" placeholder="Comment" disabled></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 form-group">
                                            <label class="col-md-3 hidden-lg control-label">Pickup</label>
                                            <div class="col-lg-12 col-md-9">
                                                <select name="product[][pickup]" disabled class="form-control">
                                                    <option value="1" selected>Yes</option>
                                                    <option value="0">No</option>
                                                </select>
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
                                        <button type="button" id="btn-add-product" class="btn btn-default col-md-offset-4 col-md-3 col-sm-12 col-xs-12"><i class="fa fa-plus row-space-right-2 hide visible-sm visible-xs"></i>Add Product</button>
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

	<!-- END ROW -->

    </section>
    <!-- end widget grid -->
</div>

@stop