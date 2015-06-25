<table id="cheque-issue-table" class="table table-striped table-hover table-condensed">
	<tbody>
        @if(count($forms) != 0)
            <tr>
                <th>&nbsp;</th>
                <th>Id</th>
                <th>Due</th>
                <th>Supplier</th>
                <th>Billable Company</th>
                <th>Amount Due</th>
                <th>Current Step</th>
            </tr>
            @foreach($forms as $f)
                <tr class="pvform @if(!$f->flag_read) {{ "unread" }} @endif" data-pk="{{ \Crypt::encrypt($f->id) }}" data-id="{{$f->id}}">
                    <td class="inbox-table-icon">
                        @if($f->flag_important)
                        <span>
                            <i class="fa fa-exclamation-triangle" title="Important"></i>
                        </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{Helper::generateURL($f)}}" class="pjax" tabindex="-1">#{{ $f->id }}</a>
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
                        <div>
                            <span>{{$f->company_name}}</span>
                        </div>
                    </td>
                    <td>
                        <span>{{$f->supplier_name}}</span>
                    </td>
                    <td>
                        <span>{{$f->invoice->currencyRelation->code}} {{number_format($f->due_amount,2)}}</span>
                    </td>
                    <td>
                        <span class="{{ $f->current_activity['status_class'] }}">{{ $f->current_activity['label'] }}</span>
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