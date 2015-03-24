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
                    <td>{{ $form->Business_Unit }}</td>
                </tr>
                <tr>
                    <td>Incoterm</td>
                    <td>:</td>
                    <td>{{ $form->Incoterm }}</td>
                </tr>
                <tr>
                    <td>Term of Payment</td>
                    <td>:</td>
                    <td>{{ $form->Term_of_Payment }}</td>
                </tr>
                <tr>
                    <td>Delivery Date</td>
                    <td>:</td>
                    <td>{{ $form->Delivery_Date->format('Y/m/d') }}</td>
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
                    <td>{{ $form->Order_Date->format('Y/m/d') }}</td>
                </tr>
                <tr>
                    <td>Currency</td>
                    <td>:</td>
                    <td>{{ $form->Currency_Code }}</td>
                </tr>
                <tr>
                    <td>Order Taken By</td>
                    <td>:</td>
                    <td>{{ $form->Order_Taken_By }}</td>
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
                            <td>{{$i->product->LITM}}</td>
                            <td>{{$i->product->DSC1}}</td>
                            <td>{{$i->Quantity_Ordered}}</td>
                            <td>{{$i->Weight}}</td>
                            <td>{{$i->Volume}}</td>
                            <td>{{$i->UOM}}</td>
                            <td>@if($form->Order_Type == "OF"){{number_format($i->Unit_Cost_Foreign,2)}}@else{{number_format($i->Unit_Cost_Local,2)}}@endif</td>
                            <td>@if($form->Order_Type == "OF"){{number_format($i->Unit_Cost_Foreign*$i->Quantity_Ordered,2)}}@else{{number_format($i->Unit_Cost_Local*$i->Quantity_Ordered,2)}}@endif</td>
                        </tr>
                        <?php
                            $total_weight += $i->Weight;
                            $total_volume += $i->Volume;
                            $sub_total += (($form->Order_Type = "OF") ? ($i->Unit_Cost_Foreign*$i->Quantity_Ordered) : ($i->Unit_Cost_Local*$i->Quantity_Ordered));
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
                    <td>{{ number_format($sub_total) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>