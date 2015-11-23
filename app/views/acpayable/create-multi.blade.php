@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment">
            <ol class="breadcrumb">
                <li>Home</li>
                <li>Accounts Payable</li>
                <li>Create Multi</li>
            </ol>
        </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_create_multi" data-urljs="{{Bust::url('/js/swift/swift.acp_create_multi.js')}}">
    <div id="draghover" class="text-align-center">
        <div class="circle bg-color-blue">
            <i class="fa fa-cloud-upload fa-4x"></i><br>
            <h2 class="text-align-center ">Incoming!</h2>
            <p class="text-align-center">Drop your files instantly to upload it!</p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-money"></i>
                    Accounts Payable
                <span>&gt;
                    Create Multi
                </span>
            </h1>
        </div>
    </div>
    <div class="row" id="acp_create_multi_container">
        <form action="/{{$rootURL}}/save-multi-form" id="multi_form" class="form-horizontal" method="POST">
            <div class="col-xs-3 ui-widget-content" id="doc-list" style="overflow-x:auto;overflow-y:auto;">
                <div class="row row-space-top-2">
                    <div class="col-xs-6 checkbox">
                        <label class="row-space-left-2">
                            <input type="checkbox" class="checkbox" id="check-all-files" />
                            <span>Select All</span>
                        </label>
                    </div>
                    <div class="col-xs-6 text-right">
                        <a class="btn btn-primary btn-sm" id="btn-upload" href="javascript:void(0);"><i class="fa fa-plus"></i> Upload</a>
                    </div>
                </div>
                <hr/>
                <div id="multi-dropzone" data-action="/{{$rootURL}}/multi-upload" data-delete="/{{$rootURL}}/multi-upload">
                    @if(count($files))
                        @foreach($files as $f)
                            <div class="row dz-success" data-url="{{$f->document->url()}}" data-name="{{$f->document_file_name}}" data-id="{{$f->id}}">
                                <div class="col-xs-12">
                                    <div class="hide">
                                        <span class="preview"><img data-dz-thumbnail=""></span>
                                    </div>
                                    <div class="col-xs-8">
                                        <div class="col-xs-12 checkbox">
                                            <label>
                                                <input type="checkbox" class="form-group checkbox check-document" value="{{$f->id}}" name="document[]" autocomplete="off" />
                                                <span class="name" data-dz-name=""><a class="file-view" target="_blank" data-type="{{$f->document_content_type}}" href="{{$f->document->url()}}">
                                                <?php
                                                switch($f->document_content_type)
                                                {
                                                    case "image/jpeg":
                                                    case "image/png":
                                                    case "image/bmp":
                                                    case "image/jpg":
                                                        echo '<i class="fa fa-file-image-o row-space-right-1"></i>';
                                                        break;
                                                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                                                    case "application/vnd.ms-excel":
                                                        echo '<i class="fa fa-file-excel-o row-space-right-1"></i>';
                                                        break;
                                                    case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                                    case "application/msword":
                                                        echo '<i class="fa fa-file-word-o row-space-right-1"></i>';
                                                        break;
                                                    case "application/pdf":
                                                        echo '<i class="fa fa-file-pdf-o row-space-right-1"></i>';
                                                        break;
                                                    default:
                                                        echo '<i class="fa fa-file-o row-space-right-1"></i>';
                                                        break;
                                                }
                                                ?>{{preg_replace('/^\d+_(.*)$/', '$1', $f->document_file_name)}}</a> <a class="row-space-left-1" target="_blank" href="<?php
                                                switch($f->document_content_type)
                                                {
                                                    case "image/jpeg":
                                                    case "image/png":
                                                    case "image/bmp":
                                                    case "image/jpg":
                                                        echo $f->document->url();
                                                        break;
                                                    case "application/pdf":
                                                        echo "/pdfviewer/viewer.html?file=".urlencode($f->document->url());
                                                        break;
                                                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                                                    case "application/vnd.ms-excel":
                                                    case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                                    case "application/msword":
                                                    default:
                                                        echo "https://docs.google.com/viewerng/viewer?url=".urlencode($f->document->url());
                                                        break;
                                                }?>" rel="tooltip" data-original-title="Open in new window" data-placement="bottom"><i class="fa fa-external-link"></i></a></span>
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <strong class="error text-danger" data-dz-errormessage=""></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-2">
                                        <p class="size hide" data-dz-size=""></p>
                                        <div class="progress progress-striped active hide" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress=""></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-2">
                                      <button data-dz-remove="" class="btn btn-danger delete btn-xs">
                                        <i class="glyphicon glyphicon-trash"></i>
                                      </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div id="template" class="row">
                        <div class="col-xs-12">
                            <!-- This is used as the file preview template -->
                            <div class="hide">
                                <span class="preview"><img data-dz-thumbnail=""></span>
                            </div>
                            <div class="col-xs-8">
                                <div class="row">
                                    <div class="col-xs-12 checkbox">
                                        <label>
                                            <input type="checkbox" class="form-group checkbox check-document" style="display:hidden;" disabled="disabled" name="document[]" />
                                            <span class="name" data-dz-name=""></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <strong class="error text-danger" data-dz-errormessage=""></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-2">
                                <p class="size hide" data-dz-size=""></p>
                                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                  <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress=""></div>
                                </div>
                            </div>
                            <div class="col-xs-2">
                              <button data-dz-remove="" class="btn btn-danger delete btn-xs">
                                <i class="glyphicon glyphicon-trash"></i>
                              </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-3 ui-widget-content" id="form-content" style="position:relative;overflow-y:auto;">
                <div class="row-space-top-6 text-center" id="no-form">
                    <h4 class="row-space-top-6"><i class="fa fa-arrow-left"></i> Select a document on the left pane to get started.</h4>
                </div>
                <div id="form-container" style="display:none;">
                    <fieldset>
                        <legend>
                            General
                            <button type="reset" class="btn btn-sm btn-danger row-space-left-2" id="btn-reset">Reset</button>
                        </legend>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Billable Company*</label>
                            <div class="col-md-8">
                                <input type="hidden" class="full-width" id="customercode" name="billable_company_code" placeholder="Type in the company's name/code" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Supplier*</label>
                            <div class="col-md-8">
                                <input type="hidden" class="full-width" id="suppliercode" name="supplier_code" placeholder="Type in the supplier's name/code" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Invoice Number</label>
                            <div class="col-md-8">
                                 <input type="text" autocomplete="off" class="form-control" name="invoice_number" id="input_invoice_number" placeholder="Type in an invoice number" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Invoice Due Date</label>
                            <div class="col-md-8">
                                 <input type="text" autocomplete="off" class="form-control" name="invoice_due_date" id="invoice_due_date" placeholder="Type in a date" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Invoice Due Amount</label>
                            <div class="col-md-8">
                                <div class="col-xs-4 no-padding">
                                    <select name="invoice_currency_code" class="form-control" autocomplete="off">
                                        @foreach($currency as $ccode => $cname)
                                            <option value="{{$ccode}}" @if($ccode === "MUR"){{'selected'}}@endif>{{$cname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-8 no-padding">
                                    <input type="text" autocomplete="off" class="form-control" name="invoice_due_amount" placeholder="Type in an amount" />
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4 control-label">PO number</label>
                            <div class="col-xs-4">
                                    <input type="text" autocomplete="off" class="form-control" name="po_number" placeholder="Type in a purchase order number" />
                            </div>
                            <div class="col-xs-4">
                                <select name="po_type" class="form-control">
                                    @foreach(\SwiftPurchaseOrder::$types as $k => $v)
                                        <option value="{{$k}}" @if($v==="ON")selected @endif>{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">HOD approval</label>
                            <div class="col-md-8">
                                <input type="hidden" class="full-width" id="hodapproval" name="hod_approval" placeholder="Type in the supplier's name/code" />
                                <input type="hidden" id="hod_user_list" value='{{json_encode($users)}}'/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Comments</label>
                            <div class="col-md-8">
                                <div class="textarea-div">
                                    <div class="typearea">
                                        <div data-ph="Write a comment..." autocomplete="off" id="comment-textarea" class="custom-scroll inputor" contenteditable="true"></div>
                                        <input type="hidden" name="usermention" id="input_mentions" value="[]" />
                                        <input type="hidden" name="comment" id='input_comment' value="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <hr/>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="checkbox row-space-left-5">
                                <label>
                                    <input type="checkbox" class="checkbox" name="save_one" id="input_save_one" />
                                    <span>Combine into one form</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row row-space-top-4">
                        <div class="col-xs-12">
                            <input name="save" type="submit" class="btn btn-default col-xs-5 col-xs-offset-1" id="btn-save" value="Create" />
                            <input name="save_publish" type="submit" class="btn btn-primary col-xs-5 col-xs-offset-1" id="btn-save-publish" value="Create & Publish" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 ui-widget-content" id="doc-content">
                <div class="row-space-top-6 text-center" id="no-doc">
                    <h3><i class="fa fa-file-o"></i> Document Preview will appear here</h3>
                </div>
                <div id="doc-container" style="display:none;height:100%;width:100%;"></div>
            </div>
        </form>
    </div>
</div>

@stop