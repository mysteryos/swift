<div id="acp-list">
    @if(!empty($related_forms))
        <table class="table table-hover">
        @foreach($related_forms as $a)
            <tr data-url="{{ \Helper::generateUrl($a) }}" class="post">
                <td>
                    #{{$a->id}}
                </td>
                <td>
                    {{$a->supplierName}}
                </td>
                <td>
                    {{$a->amountDue}}
                </td>
                <td>
                    <span class="{{$a->current_activity['status_class'] }}">{{ $a->current_activity['label']}}</span>
                </td>
            </tr>
        @endforeach
        </table>
    @else
    <div class="row">
        <div class="col-xs-12">
            <h2><i class="fa fa-exclamation-triangle"></i> No accounts payable record.</h2>
        </div>
    </div>
    @endif
</div>