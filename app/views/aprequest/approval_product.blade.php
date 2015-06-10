<tr class="approval_product_row">
    <td class="row-space-left-2">
        <a class="btn btn-default btn-approve hover btn-accept
           @if($form->approval_level === \SwiftApproval::APR_CATMAN)
                @if($p->approvalcatman && $p->approvalcatman->approved === \SwiftApproval::APPROVED)
                    {{'on'}}
                @endif
            @else
                @if($p->approvalexec && $p->approvalexec->approved === \SwiftApproval::APPROVED)
                    {{'on'}}
                @endif
           @endif" title="approve" data-name="approval_approved" data-value="{{\SwiftApproval::APPROVED}}" data-pk="
            @if($form->approval_level === \SwiftApproval::APR_CATMAN)
                @if($p->approvalcatman)
                    {{\Crypt::encrypt($p->approvalcatman->id)}}
                @else
                    {{'0'}}
                @endif
            @else
                @if($p->approvalexec)
                    {{\Crypt::encrypt($p->approvalexec->id)}}
                @else
                    {{'0'}}
                @endif
           @endif" url="{{"/$rootURL/productapproval/$form->approval_level/".\Crypt::encrypt($p->id)}}" data-name="approval_approved"><i class="fa fa-lg fa-check"></i></a>
        <a class="btn btn-default btn-approve hover btn-reject @if($form->approval_level === \SwiftApproval::APR_CATMAN)
                @if($p->approvalcatman && $p->approvalcatman->approved === \SwiftApproval::REJECTED)
                    {{'on'}}
                @endif
            @else
                @if($p->approvalexec && $p->approvalexec->approved === \SwiftApproval::REJECTED)
                    {{'on'}}
                @endif
           @endif" title="reject" data-name="approval_approved" data-value="{{\SwiftApproval::REJECTED}}" data-pk="
           @if($form->approval_level === \SwiftApproval::APR_CATMAN)
                @if($p->approvalcatman)
                    {{\Crypt::encrypt($p->approvalcatman->id)}}
                @else
                    {{'0'}}
                @endif
            @else
                @if($p->approvalexec)
                    {{\Crypt::encrypt($p->approvalexec->id)}}
                @else
                    {{'0'}}
                @endif
            @endif" url="{{"/$rootURL/productapproval/$form->approval_level/".\Crypt::encrypt($p->id)}}" data-name="approval_approved"><i class="fa fa-lg fa-times-circle"></i></a>
    </td>
    <td class="row-space-left-2 pointable">
        {{$p->quantity}} <i class="fa fa-times"></i> {{$p->name}} ({{$p->jdeproduct->AITM}})
    </td>
    <td>
        {{$p->reason_text}}@if($p->reason_others){{" - ".$p->reason_others}}@endif
    </td>
</tr>