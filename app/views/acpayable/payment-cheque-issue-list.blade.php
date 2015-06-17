<table id="cheque-issue-table" class="table table-striped table-hover table-condensed">
	<tbody>
        @if(count($forms) != 0)
            <tr>
                <th colspan="2">&nbsp;</th>
                <th>Id</th>
                <th>Due</th>
                <th>PV Number</th>
                <th>Supplier</th>
                <th>Billable Company</th>
                <th>Amount Due</th>
                <th>Payment Number</th>
                <th>Batch Number</th>
            </tr>
            @foreach($forms as $f)
                <tr class="pvform @if(!$f->flag_read) {{ "unread" }} @endif" data-pk="{{ \Crypt::encrypt($f->id) }}">
                    <td class="inbox-table-icon">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox style-2" />
                                <span></span>
                            </label>
                        </div>
                    </td>
                    <td class="inbox-table-icon">
                        @if($f->flag_important)
                        <span>
                            <i class="fa fa-exclamation-triangle" title="Important"></i>
                        </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{Helper::generateURL($f)}}" class="pjax">#{{ $f->id }}</a>
                    </td>
                    <td>
                        @if($f->paymentVoucher->invoice)
                            @if($f->paymentVoucher->invoice->due_date !== null)
                                {{ucfirst(\Helper::dueInDays($f->paymentVoucher->invoice->due_date))}}
                            @else
                                {{"(No Due Date)"}}
                            @endif
                        @else
                            {{"(No Invoice Match)"}}
                        @endif
                    </td>
                    <td>
                        <?php
                        switch($f->paymentVoucher->validated)
                        {
                            case \SwiftACPPaymentVoucher::VALIDATION_PENDING:
                                echo '<i class="fa fa-question" title="Validation Pending"></i> ';
                                break;
                            case \SwiftACPPaymentVoucher::VALIDATION_ERROR:
                                echo '<i class="fa fa-times" title="Validation Error"></i> ';
                                break;
                            case \SwiftACPPaymentVoucher::VALIDATION_COMPLETE:
                                echo '<i class="fa fa-check" title="Validation Complete"></i> ';
                                break;
                        }
                        ?>
                        {{$f->paymentVoucher->number}}
                    </td>
                    <td>
                        <div>
                            <span>{{$f->company_name}}</span>
                        </div>
                    </td>
                    <td>
                        <span>{{$f->supplier_name}}</span>
                    </td>
                    <td>
                        <span>{{number_format($f->due_amount,2)}}</span>
                    </td>
                    <td>
                        <input type="text" class="form-control input-block-level input-paymentnumber" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/payment-number/{{ \Crypt::encrypt($f->id) }}" name="payment_number" value="" />
                    </td>
                    <td>
                        <input type="text" class="form-control input-block-level input-batchnumber" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/batch-number/{{ \Crypt::encrypt($f->id) }}" name="batch_number" value="" />
                    </td>
                </tr>
            @endforeach
        @else
            <tr id="noorders" class="empty">
                <td class="text-align-center">
                    <h1>
                        <i class="fa fa-smile-o"></i> <span>No forms at all. Clean & Shiny!</span>
                    </h1>
                </td>
            </tr>
        @endif
	</tbody>
</table>