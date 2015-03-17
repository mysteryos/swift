@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment">
            <ol class="breadcrumb">
                <li>Home</li>
                <li>Accounts Payable</li>
                <li>Create</li>
            </ol>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_create">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-gift"></i>
                    Accounts Payable
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
                            <form action="/{{ $rootURL }}/create" method="POST" id="acprequest-create-form" enctype="multipart/form-data" class="form-horizontal" name="acprequest-create" novalidate="novalidate">
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
                                                        <label class="col-md-2 control-label">Billable Company*</label>
                                                        <div class="col-md-10">
                                                            <input type="hidden" class="full-width" id="customercode" name="billable_company_code" placeholder="Type in the company's name/code" />
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">Supplier*</label>
                                                        <div class="col-md-10">
                                                            <input type="hidden" class="full-width" id="suppliercode" name="supplier_code" placeholder="Type in the supplier's name/code" />
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">Name</label>
                                                        <div class="col-md-10">
                                                             <input type="text" class="form-control" name="name" placeholder="Type in a name" />
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                            <label class="col-md-2 control-label">Description</label>
                                                            <div class="col-md-10">
                                                                <textarea name="description" rows="2" class="form-control" placeholder="Type in a description"></textarea>
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