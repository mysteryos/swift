<div class="panel panel-default pv-row @if($first)pv-selected @endif" data-formId="{{$f->id}}">
    <form class="pv-form" action="/{{$rootURL}}/save-pv" method="POST">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a href="#form-{{$f->id}}" data-toggle="collapse">
                    <i class="fa fa-lg fa-angle-down pull-right"></i><i class="fa fa-lg fa-angle-up pull-right"></i>
                    #{{$f->id}} - {{$f->supplier_name}}
                </a>
            </h4>
        </div>
        <div id="form-{{$f->id}}" class="panel-collapse collapse in">
            <input type="hidden" name="id" class="form-id-val" value="{{\Crypt::encrypt($f->id)}}" />
            <input type="hidden" name="pv-id" class="pv-id-val" value="@if(count($f->paymentVoucher) > 0){{Crypt::encrypt($f->paymentVoucher->first()->id)}}@endif" />
            <div class="panel-body">
                <table class="table table-borderless">
                    <tr class="hide">
                        <td class="searchable">#{{$f->id}}</td>
                    </tr>
                    <tr>
                        <td><label>Supplier</label></td>
                        <td class="searchable">{{$f->supplier_name}}</td>
                    </tr>
                    <tr>
                        <td><label>Billable Company</label></td>
                        <td class="searchable">{{$f->company_name}}</td>
                    </tr>
                    @if(count($f->purchaseOrder) > 0)
                    <tr>
                        <td class="searchable"><label>Purchase Order</label></td>
                        <td class="searchable">@foreach($f->purchaseOrder as $po)
                        <p>{{$po->reference}} {{$po->type}} @if($po->validated === \SwiftPurchaseOrder::VALIDATION_FOUND)<a href="/jde-purchase-order/view/{{$po->order_id}}" class="purchase-order-view colorbox-ajax"><i class="fa fa-search"></i> View</a></p>@endif
                    @endforeach</td>
                    </tr>
                    @endif
                    <tr>
                        <td><label>Payment Voucher</label></td>
                        <td class="searchable"><input type="text" name="pv-number" class="payment-voucher-val form-control" autocomplete="off" value="" placeholder="Type in PV numbers" @if($first)autofocus @endif /></td>
                    </tr>
                </table>
                <div class="row">
                    <div class="col-xs-5 col-xs-offset-3">
                        <button class="btn btn-block btn-default btn-publish btn-sm" tabindex="-1" type="submit"><i class="fa fa-check hide"></i> Publish</button>
                    </div>
                </div>
            </div>
        </div>
        @if(count($f->document))
            <input type="hidden" value="/pdfviewer/viewer.html?file={{$f->document->first()->getAttachedfiles()['document']->url()}}" class="pv-doc" />
        @endif
    </form>
</div>