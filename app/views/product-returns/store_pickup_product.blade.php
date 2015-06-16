<tr>
    <td>
        <span  class="row-space-left-2">{{$p->qty_client}} <i class="fa fa-times"></i> {{$p->name}} ({{$p->jdeproduct->AITM}})</span>
    </td>
    <td>
        Invoice No: {{$p->invoice_id or "N/A"}}
    </td>
    <td>
        {{$p->reason_text}}@if($p->reason_others){{" - ".$p->reason_others}}@endif
    </td>
    <td>
        @if($p->approvalretailman) Approved By {{$p->approvalretailman->approval_user_name}} <abbr title="{{date("Y/m/d H:i",strtotime($p->approvalretailman->updated_at))}}" data-livestamp="{{strtotime($p->approvalretailman->updated_at)}}"></abbr> @endif
    </td>
</tr>