<div class="container">
    <div class="row">
        <div class="col-xs-12 well well-sm">
            <span class="text-capitalize text-center h4 col-xs-12">Payment Number: {{$pay->docm}}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4">
            <table class="table table-borderless">
                <tr>
                    <td>Type</td>
                    <td>:</td>
                    <td>{{ $pay->pyin_descr }}</td>
                </tr>
                <tr>
                    <td>User</td>
                    <td>:</td>
                    <td>{{ $pay->user }}</td>
                </tr>
                <tr>
                    <td>Bank Account</td>
                    <td>:</td>
                    <td>{{ $pay->bank_account_no }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row row-space-left-1">
        <div class="row">
            <div class="col-xs-12 h4">
                <b>Supplier:</b>&nbsp;<span>@if($pay->supplier){{$pay->supplier->getReadableName()}}@else{{"N/A"}}@endif</span>
            </div>
        </div>
    </div>
    <div class="row row-space-top-2">
        <div class="col-xs-12">
            <table class="table table-hover">
                <tr>
                    <th>No.</th>
                    <th>Billable Company</th>
                    <th>Remarks</th>
                    <th>Currency Rate</th>
                    <th>Amount</th>
                </tr>
                <?php $sub_total = 0; ?>
                @if(count($pay->detail))
                    @foreach($pay->detail as $d)
                        <tr>
                            <td>{{$d->rc5}}</td>
                            <td>{{(int)$d->co}}</td>
                            <td>{{$d->rmk}}</td>
                            <td>{{$d->crr}}</td>
                            <td>{{$d->crrd}} {{number_format(abs($d->paap))}}</td>
                        </tr>
                        <?php
                            $sub_total += abs($d->paap);
                        ?>
                    @endforeach
                    <tr>
                        <td colspan="4" align="right">Sub Total</td>
                        <td>{{ $pay->currency->code }} {{ number_format($sub_total) }}</td>
                    </tr>
                @else
                <tr>
                    <td colspan="5">
                        <h1 class="text-center">
                            No Details.
                        </h1>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div>