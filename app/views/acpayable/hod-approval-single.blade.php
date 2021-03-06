<div class="panel panel-default pv-row @if($first){{"pv-selected"}}@endif" data-formId="{{$f->id}}">
    <form class="pv-form" action="/{{$rootURL}}/save-hod-approval" method="POST">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a href="#form-{{$f->id}}" data-toggle="collapse" tabindex="-1">
                    <i class="fa fa-lg fa-angle-down pull-right"></i><i class="fa fa-lg fa-angle-up pull-right"></i>
                    #{{$f->id}} - {{$f->supplier_name}}
                </a>
            </h4>
        </div>
        <div id="form-{{$f->id}}" class="panel-collapse collapse in">
            <div class="panel-body">
                <table class="table table-borderless">
                    <tr>
                        <td><label>Form ID</label></td>
                        <td class="searchable"><a class="pjax" tabindex="-1" href="{{\Helper::generateURL($f)}}">#{{$f->id}}</a></td>
                    </tr>
                    <tr>
                        <td><label>Supplier</label></td>
                        <td class="searchable">{{$f->supplier_name}}</td>
                    </tr>
                    <tr>
                        <td><label>Billable Company</label></td>
                        <td class="searchable">{{$f->company_name}}</td>
                    </tr>
                    <tr>
                        <td><label>Purchase Order</label></td>
                        <td class="searchable">@foreach($f->purchaseOrder as $po)
                        <p>{{$po->reference}} {{$po->type}} @if($po->validated === \SwiftPurchaseOrder::VALIDATION_FOUND)<a href="/jde-purchase-order/view/{{$po->order_id}}" class="purchase-order-view colorbox-ajax"><i class="fa fa-search"></i> View</a></p>@endif
                    @endforeach</td>
                    </tr>
                    <tr>
                        <td><label>Invoice Number</label></td>
                        <td class="searchable">{{ $f->invoice->number or ""}}</td>
                    </tr>
                    <tr>
                        <td>Approval</td>
                        <td>
                            <?php
                                foreach($f->approvalHOD as $a)
                                {
                                    echo "<span title=\"";
                                    switch($a->approved)
                                    {
                                        case \SwiftApproval::PENDING:
                                            echo "Pending since {$a->created_at}\"><i class=\"fa fa-question color-yellow\"></i> ";
                                            break;
                                        case \SwiftApproval::APPROVED:
                                            echo "Approved on {$a->updated_at}\"><i class=\"fa fa-check color-green\"></i> ";
                                            break;
                                        case \SwiftApproval::REJECTED:
                                            echo "Rejected on {$a->updated_at}\"><i class=\"fa fa-times color-red\"></i> ";
                                            break;
                                    }
                                    echo $a->approval_user_name."</span> ";
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Comments</td>
                        <td>
                            @foreach($f->comments as $c)
                                <div>
                                    <span title="{{$c->getDate()}}">{{ $c->user->first_name." ".$c->user->last_name }}</span> -
                                    <span>{{ nl2br(htmlspecialchars($c->comment, null, 'UTF-8')) }}</span>
                                </div>
                                <hr/>
                            @endforeach
                        </td>
                    </tr>
                </table>
                <div class="row">
                    <div class="col-xs-12">
                        <a class="btn btn-default btn-approve btn-sm col-xs-3 col-xs-offset-1" tabindex="-1" href="/{{$rootURL}}/save-hod-approval/{{\Crypt::encrypt($f->id)}}/{{\SwiftApproval::APPROVED}}"><i class="fa fa-check"></i> Approve</a>
                        <a class="btn btn-default btn-reject btn-sm col-xs-3 col-xs-offset-1" tabindex="-1" href="/{{$rootURL}}/save-hod-approval/{{\Crypt::encrypt($f->id)}}/{{\SwiftApproval::REJECTED}}"><i class="fa fa-times"></i> Reject</a>
                        <a class="btn btn-default btn-suggest colorbox-ajax btn-sm col-xs-3 col-xs-offset-1" tabindex="-1" href="/{{$rootURL}}/hod-suggestion/{{\Crypt::encrypt($f->id)}}"><i class="fa fa-question"></i> Suggest</a>
                    </div>
                </div>
            </div>
        </div>
        @if(count($f->document))
            <ul class="hide doc-list" id="doc-list-{{$f->id}}">
                @foreach($f->document as $k => $doc)
                    <li data-href="/pdfviewer/viewer.html?file={{urlencode($doc->getAttachedfiles()['document']->url())}}" @if($k===0)class="doc-selected"@endif>
                        <div class="doc-icon">
                                <?php
                                switch($doc->getAttachedfiles()['document']->contentType())
                                {
                                    case "image/jpeg":
                                    case "image/png":
                                    case "image/bmp":
                                    case "image/jpg":
                                        echo '<i class="fa fa-file-image-o"></i>';
                                        break;
                                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                                    case "application/vnd.ms-excel":
                                        echo '<i class="fa fa-file-excel-o"></i>';
                                        break;
                                    case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                    case "application/msword":
                                        echo '<i class="fa fa-file-word-o"></i>';
                                        break;
                                    case "application/pdf":
                                        echo '<i class="fa fa-file-pdf-o"></i>';
                                        break;
                                    default:
                                        echo '<i class="fa fa-file-o"></i>';
                                        break;
                                }
                                ?>
                        </div>
                        <div class="doc-name">
                            {{$doc->getAttachedfiles()['document']->originalFilename()}}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </form>
</div>