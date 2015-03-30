@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="acp_payment_voucher_process">
    <input type="hidden" name="channel_name" id="channel_name" value="acp_payment_voucher_process" />
    <!--Payment Vouchers -->
    <div id="pv-process-container">
        <div class="border-left" id="pv-process-info">
            <div class="row">
                <div class="col-xs-12">
                    <div class="icon-addon addon-md">
                        <input type="text" class="form-control" placeholder="Search" name="search-pv" id="search-pv">
                        <label title="" rel="tooltip" class="glyphicon glyphicon-search" for="search-pv" data-original-title="search"></label>
                    </div>
                </div>
            </div>
            @if($form_count === 0)
                <div id="no-pv" class="row">
                    <div class="col-xs-12">
                        <h2 class="text-center">No payment voucher to process</h2>
                    </div>
                </div>
            @else
                <div class="panel-group smart-accordion-default">
                    <?php $first = true ?>
                    @foreach($forms as $f)
                        @include('acpayable.payment-voucher-single',array('first'=>$first))
                        <?php if($first){
                            $first = false;
                        } ?>
                    @endforeach
                </div>
            @endif
        </div>
        <div id="pv-process-doc">
            @if($form_count > 0 && count($forms->first()->document) > 0)
            <iframe src="/pdfviewer/viewer.html?file={{$forms->first()->document->first()->getAttachedfiles()['document']->url()}}"></iframe>
            @else
            <iframe></iframe>
            <div id="no-doc">
                <div class="text-center"><i class="fa fa-file-pdf-o"></i></div>
                <h2 class="text-center">No Documents Found.</h2>
            </div>
            @endif
        </div>
    </div>
    <!-- END Payment Voucher -->
</div>

@stop       