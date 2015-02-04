<table id="summary_table" class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Id/Name</th>
            @if($business_unit === false)<th>BU</th>@endif
            <th>Status</th>
            <th>PO number</th>
            <th>Freight</th>
            <th>Vessel Name</th>
            <th>ETD</th>
            <th>ETA</th>
            <th>Storage Start</th>
            <th>Demurrage Start</th>
            <th>File to Clearing Dpt</th>
            <th>BOE</th>
            <th>BOE sent to customs</th>
            <th>Clearance Obtained</th>
            <th>Container No</th>
            <th>GRN</th>
            <th>Last Update</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary_datatable as $row)
            <tr>
                <td><a href="{{ \Helper::generateUrl($row) }}" class="pjax">{{ '#'.$row->id." ".$row->name }}</a></td>
                @if($business_unit === false)<td>{{ $row->getBusinessUnitRevisionAttribute($row->business_unit) }}</td>@endif
                <td class="{{ $row->current_activity['status_class'] }}">{{ $row->current_activity['label'] }}</td>
                <td>{{ $row->data_purchaseOrder }}</td>
                <td>{{ $row->data_freight_name }}</td>
                <td>{{ $row->data_vessel_name }}</td>
                <td>{{ $row->data_freight_etd }}</td>
                <td>{{ $row->data_freight_eta }}</td>
                <td>{{ $row->data_storage_start }}</td>
                <td>{{ $row->data_demurrage_start }}</td>
                <td>{{ $row->data_customsDeclaration_customs_filled_at }}</td>
                <td>{{ $row->data_customsDeclaration_customs_reference }}</td>
                <td>{{ $row->data_customsDeclaration_customs_processed_at }}</td>
                <td>{{ $row->data_customsDeclaration_customs_cleared_at }}</td>
                <td>{{ $row->data_shipment_container_no }}</td>
                <td>{{ $row->data_reception_grn }}</td>
                <td>{{ $row->updated_at->toDateString() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
