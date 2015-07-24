<table id="inbox-table" class="table table-striped table-hover table-condensed">
	<tbody>
        @if(count($forms) != 0)
            <tr>
                <th colspan="2">&nbsp;</th>
                <th>Id</th>
                <th></th>
                <th>Due</th>
                <th>Payment Number</th>
                <th>Batch Number</th>
                <th>Supplier</th>
                <th>Billable Company</th>
                <th>Amount</th>
                <th>Acc Sign</th>
                <th>Exec Sign</th>
                <th>&nbsp;</th>
            </tr>
            @foreach($forms as $f)
                @foreach($f->payment as $pay)
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
                            <a href="{{\Helper::generateURL($f)}}" class="pjax btn btn-default" tabindex="-1">#{{ $f->id }}</a>
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
                                switch($pay->validated)
                                {
                                    case \SwiftACPPayment::VALIDATION_PENDING:
                                        echo '<i class="fa fa-question" title="Validation Pending"></i> ';
                                        break;
                                    case \SwiftACPPayment::VALIDATION_ERROR:
                                        echo '<i class="fa fa-times" title="Validation Error"></i> ';
                                        break;
                                    case \SwiftACPPayment::VALIDATION_COMPLETE:
                                        echo '<i class="fa fa-check" title="Validation Complete"></i> ';
                                        break;
                                }
                            ?>
                            {{$pay->payment_number}}
                        </td>
                        <td>
                            {{$pay->batch_number}}
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
                            @if($pay->chequeSignator){{$pay->chequeSignator->first_name." ".$pay->chequeSignator->last_name}}@endif
                        </td>
                        <td>
                            @if($pay->chequeExecSignator){{$pay->chequeExecSignator->first_name." ".$pay->chequeExecSignator->last_name}}@endif
                        </td>
                        <td>
                            <select name="dispatch" class="form-control input-with-pk input-block-level input-dispatch" data-pk="{{\Crypt::encrypt($pay->id)}}" data-prev-value="{{$pay->cheque_dispatch}}" data-url="/{{$rootURL}}/cheque-dispatch">
                                <option disabled selected>Select a method</option>
                                @foreach($dispatch_method as $dispatch_key => $dispatch_val)
                                    <option value="{{$dispatch_key}}" @if($pay->cheque_dispatch === $dispatch_key){{"selected"}}@endif>{{$dispatch_val}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <a href="/{{ $rootURL }}/cheque-dispatch/{{\Crypt::encrypt($pay->id)}}" class="btn btn-default btn-single-publish" title="Publish Form" tabindex="-1"><i class="fa fa-check"></i></a>
                        </td>
                    </tr>
                @endforeach
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