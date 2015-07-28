<table id="inbox-table" class="table table-striped table-hover table-condensed">
	<tbody>
        @if(count($forms) != 0)
            <tr>
                <th colspan="2">&nbsp;</th>
                <th>Id</th>
                <th></th>
                <th>Due</th>
                <th>PV Number</th>
                <th>Supplier</th>
                <th>Billable Company</th>
                <th>Amount Due</th>
                <th>Entries</th>
                <th>Type</th>
                <th>Payment Number</th>
                <th>Batch Number</th>
                <th>Signator</th>
                <th>&nbsp;</th>
            </tr>
            @foreach($forms as $f)
                <tr class="pvform @if(!$f->flag_read) {{ "unread" }} @endif" data-pk="{{ \Crypt::encrypt($f->id) }}" data-id="{{$f->id}}">
                    <td class="inbox-table-icon">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox style-2" tabindex="-1" />
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
                        <a href="{{Helper::generateURL($f)}}" class="pjax btn btn-default" tabindex="-1">#{{ $f->id }}</a>
                    </td>
                    <td>
                        <a class="btn btn-default" href="{{Helper::generateDocumentURL($f)}}" target="_blank" title="View Documents"><i class="fa fa-file-o"></i></a>
                    </td>
                    <td>
                        @if($f->invoice)
                            @if($f->invoice->due_date !== null)
                                {{ucfirst(\Helper::dueInDays($f->invoice->due_date))}}
                            @else
                                {{"(No Due Date)"}}
                            @endif
                        @else
                            {{"(No Invoice)"}}
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
                        <span>{{$f->supplier_name}}</span>
                    </td>
                    <td>
                        <div>
                            <span>{{$f->company_name}}</span>
                        </div>
                    </td>
                    <td>
                        <span>{{number_format($f->due_amount,2)}}</span>
                    </td>
                    <td>
                        {{count($f->payment)}}
                    </td>
                    <td>
                        <select name="payment_type" class="form-control input-with-pk input-block-level input-payment-type" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/payment-type/{{\Crypt::encrypt($f->id)}}">
                            <option @if($f->payment_type === 0){{"selected"}}@endif disabled>Select a type</option>
                            @foreach($payment_type as $type_key => $type_val)
                                <option value="{{$type_key}}" @if($f->payment_type === $type_key){{"selected"}}@endif >{{$type_val}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control input-block-level input-with-pk input-paymentnumber" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/payment-number/{{ \Crypt::encrypt($f->id) }}" name="payment_number" value="" />
                    </td>
                    <td>
                        <input type="text" class="form-control input-block-level input-with-pk input-batchnumber" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/batch-number/{{ \Crypt::encrypt($f->id) }}" name="batch_number" value="" />
                    </td>
                    <td>
                        <select name="cheque_signator_id" class="form-control input-with-pk input-block-level input-cheque-signator-id" data-pk="0" data-prev-value="" data-url="/{{$rootURL}}/cheque-signator-id/{{ \Crypt::encrypt($f->id) }}">
                            <option selected disabled>Select a User</option>
                                @foreach($chequesign_users as $user_id => $user_name)
                                    <option value="{{$user_id}}">{{$user_name}}</option>
                                @endforeach
                        </select>
                    </td>
                    <td>
                        <a href="/{{ $rootURL }}/formapprovalaccounting/{{\Crypt::encrypt($f->id)}}" class="btn btn-default btn-single-publish" title="Publish Form" tabindex="-1"><i class="fa fa-check"></i></a>
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