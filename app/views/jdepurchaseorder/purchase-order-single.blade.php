<div class="container">
    <div class="row">
        <div class="col-xs-12 well well-sm">
            <span class="text-capitalize text-center h4 col-xs-12">Purchase Order</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4">
            <table class="table table-borderless">
                <tr>
                    <td>Business Unit</td>
                    <td>:</td>
                    <td>{{ $form->business_unit }}</td>
                </tr>
                <tr>
                    <td>Incoterm</td>
                    <td>:</td>
                    <td>{{ $form->incoterm }}</td>
                </tr>
                <tr>
                    <td>Term of Payment</td>
                    <td>:</td>
                    <td>{{ $form->terms_of_payment }}</td>
                </tr>
                <tr>
                    <td>Delivery Date</td>
                    <td>:</td>
                    <td>{{ $form->delivery_date->format('Y/m/d') }}</td>
                </tr>
            </table>
        </div>
        <div class="col-xs-4 col-xs-offset-4">
            <table class="table table-borderless">
                <tr>
                    <td>Order Number</td>
                    <td>:</td>
                    <td>{{ $form->name }}</td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td>:</td>
                    <td>{{ $form->order_date->format('Y/m/d') }}</td>
                </tr>
                <tr>
                    <td>Currency</td>
                    <td>:</td>
                    <td>{{ $form->currency_code }}</td>
                </tr>
                <tr>
                    <td>Currency Rate</td>
                    <td>:</td>
                    <td>{{ number_format($form->currency_rate,2) }}</td>
                </tr>
                <tr>
                    <td>Order Taken By</td>
                    <td>:</td>
                    <td>{{ $form->order_taken_by }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row row-space-left-1">
        <div class="row">
            <div class="col-xs-12 h4">
                <b>Supplier:</b>&nbsp;<span>{{$form->supplier->getReadableName()}}, {{$form->supplier->Supplier_Add1}},{{$form->supplier->Supplier_City}}</span>
            </div>
        </div>
        <div class="row row-space-top-1">
            <div class="col-xs-12 h4">
                <b>Ship To:</b>&nbsp;<span>{{$form->shipto->getReadableName()}}, {{$form->shipto->Supplier_Add1}}, {{$form->shipto->Supplier_City}}</span>
    </div>
        </div>
    </div>
    <div class="row row-space-top-2">
        <div class="col-xs-12">
            <table class="table table-hover">
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Qty Ordered</th>
                    <th>Weight</th>
                    <th>Volume</th>
                    <th>UOM</th>
                    <th>Unit Price</th>
                    <th>Total Amount Excl VAT</th>
                </tr>
                <?php $total_weight = $total_volume = $sub_total = 0; ?>
                @if(count($form->item))
                    @foreach($form->item as $i)
                        <tr>
                            <td>@if($i->product){{$i->product->LITM}}@else{{"N/A"}}@endif</td>
                            <td>@if($i->product){{$i->product->DSC1}}@else{{"N/A"}}@endif</td>
                            <td>{{$i->quantity_ordered}}</td>
                            <td>{{$i->weight}}</td>
                            <td>{{$i->volume}}</td>
                            <td>{{$i->UOM}}</td>
                            <td>@if($form->order_type == "OF"){{number_format($i->unit_cost_foreign,2)}}@else{{number_format($i->unit_cost_local,2)}}@endif</td>
                            <td>@if($form->order_type == "OF"){{number_format($i->unit_cost_foreign*$i->quantity_ordered,2)}}@else{{number_format($i->unit_cost_local*$i->quantity_ordered,2)}}@endif</td>
                        </tr>
                        <?php
                            $total_weight += $i->weight;
                            $total_volume += $i->volume;
                            $sub_total += (($form->order_type = "OF") ? ($i->unit_cost_foreign*$i->quantity_ordered) : ($i->unit_cost_local*$i->quantity_ordered));
                        ?>
                    @endforeach
                @else
                <tr>
                    <td colspan="8">
                        <h1 class="text-center">
                            No items.
                        </h1>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-xs-4">
            <table class="table table-borderless">
                <tr>
                    <td>Total Weight:</td>
                    <td>{{ $total_weight }}</td>
                    <td>KG</td>
                </tr>
                <tr>
                    <td>Total Volume</td>
                    <td>{{ $total_volume }}</td>
                    <td>M3</td>
                </tr>
            </table>
        </div>
        <div class="col-xs-4 col-xs-offset-4">
            <table class="table table-borderless">
                <tr>
                    <td>Sub Total</td>
                    <td>{{ $form->currency_code }} {{ number_format($sub_total) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>