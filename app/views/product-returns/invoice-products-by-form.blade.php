<table class="table table-hover table-responsive table-bordered">
    <tr>
        <th><input type="checkbox" name="tick_all" value="0" id="product_tick_all" /></th>
        <th>Line</th>
        <th>Code</th>
        <th>Name</th>
        <th>Quantity</th>
    </tr>
    @foreach($lines as $l)
        <tr>
            <td><input type="checkbox" name="jde_itm[{{(int)$l->LNID}}]" value="{{$l->ITM}}" class="product_checkbox" /></td>
            <td class="pointable">{{(int)$l->LNID}}</td>
            <td class="pointable">{{$l->product->AITM}}</td>
            <td class="pointable">{{trim($l->product->DSC1)}}</td>
            <td class="pointable">{{$l->SOQS}}</td>
        </tr>
    @endforeach
</table>