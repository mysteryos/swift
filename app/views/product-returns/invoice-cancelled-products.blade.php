<table class="table table-hover table-responsive table-bordered">
    <tr>
        <th rowspan="2">Line</th>
        <th rowspan="2">Code</th>
        <th rowspan="2">Name</th>
        <th rowspan="2">Reason</th>
        <th colspan="4" class="text-center">Quantity</th>
    </tr>
    <tr>
        <th>Client</th>
        <th>Pickup</th>
        <th>Picking</th>
        <th>Disposal</th>
    </tr>
    @foreach($lines as $l)
        <tr>
            <td>{{(int)$l->LNID}}</td>
            <td>{{$l->product->AITM}}</td>
            <td>{{trim($l->product->DSC1)}}</td>
            <td>Invoice Cancelled (At Scott)</td>
            <td>{{$l->SOQS}}</td>
            <td>{{$l->SOQS}}</td>
            <td>{{$l->SOQS}}</td>
            <td>0</td>
        </tr>
    @endforeach
</table>