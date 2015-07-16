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
<div id="content" data-js="acp_cheque_sign_exec" data-urljs="{{Bust::url('/js/swift/swift.acp_cheque_sign_exec.js')}}">
    <input type="hidden" name="channel_name" id="channel_name" value="acp_cheque_sign" />
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
            @if($payment_count === 0)
                <div id="no-cs" class="row">
                    <div class="col-xs-12">
                        <h2 class="text-center">No cheques to sign</h2>
                    </div>
                </div>
            @else
                <div class="panel-group smart-accordion-default">
                    <?php $current_payment_number = $total = 0;
                        if($payments->first()->invoice)
                        {
                            $total = $payments->first()->invoice->due_amount;
                        }
                        ?>
                        @foreach($payments as $pay)
                            <?php
                                //If new payment number
                                if($current_payment_number !== $pay->payment_number)
                                {
                                    //if not first payment number
                                    if($current_payment_number !== 0)
                                    {
                                    ?>
                                        @include('acpayable.cheque-sign-exec-single-footer')
                                    <?php
                                    }
                                    //Reset total
                                    if($pay->invoice)
                                    {
                                        $total = $pay->invoice->due_amount;
                                    }
                                    ?>
                                    @include('acpayable.cheque-sign-exec-single-header')
                            <?php
                                    $current_payment_number = $pay->payment_number;
                                }
                                else
                                {
                                    if($pay->invoice)
                                    {
                                        $total += $pay->invoice->due_amount;
                                    }
                                }
                            ?>
                            @include('acpayable.cheque-sign-exec-single')
                        @endforeach
                        @include('acpayable.cheque-sign-exec-single-footer')
                </div>
            @endif
        </div>
        <div id="pv-process-doc">
            <iframe></iframe>
            <div id="no-doc">
                <div class="text-center"><i class="fa fa-file-pdf-o"></i></div>
                <h2 class="text-center">Select an invoice.</h2>
            </div>
            <div id="doc-browser">
            </div>
        </div>
    </div>
    <!-- END Payment Voucher -->
</div>

@stop       