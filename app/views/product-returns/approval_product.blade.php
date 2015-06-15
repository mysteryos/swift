<tr class="approval_product_row">
    <td class="row-space-left-2">
        <a class="btn btn-default btn-approve hover btn-accept
                @if($p->approvalretailman && $p->approvalretailman->approved === \SwiftApproval::APPROVED)
                    {{'on'}}
                @endif" title="approve" data-name="approval_approved" data-value="{{\SwiftApproval::APPROVED}}" data-pk="
                @if($p->approvalretailman)
                    {{\Crypt::encrypt($p->approvalretailman->id)}}
                @else
                    {{'0'}}
                @endif" url="{{"/$rootURL/product-approval/".\SwiftApproval::PR_RETAILMAN."/".Crypt::encrypt($p->id)}}" data-name="approval_approved"><i class="fa fa-lg fa-check"></i></a>
        <a class="btn btn-default btn-approve hover btn-reject
                @if($p->approvalretailman && $p->approvalretailman->approved === \SwiftApproval::REJECTED)
                    {{'on'}}
                @endif" title="reject" data-name="approval_approved" data-value="{{\SwiftApproval::REJECTED}}" data-pk="
                @if($p->approvalretailman)
                    {{\Crypt::encrypt($p->approvalretailman->id)}}
                @else
                    {{'0'}}
                @endif" url="{{"/$rootURL/product-approval/".\SwiftApproval::PR_RETAILMAN."/".Crypt::encrypt($p->id)}}" data-name="approval_approved"><i class="fa fa-lg fa-times-circle"></i></a>
    </td>
    <td class="row-space-left-2 pointable">
        {{$p->qty_client}} <i class="fa fa-times"></i> {{$p->name}} ({{$p->jdeproduct->AITM}})
    </td>
    <td>
        {{$p->reason_text}}@if($p->reason_others){{" - ".$p->reason_others}}@endif
    </td>
</tr>