<table id="inbox-table" class="table table-striped table-hover">
	<tbody>
        @if(count($forms) != 0)
            @foreach($forms as $f)
            <tr class="supplierform" data-pk="{{ $f->Supplier_Code }}" data-view="/{{ $rootURL }}/supplier/@if($edit_access){{ "edit" }}@else{{ "view" }}@endif/{{ $f->Supplier_Code }}">
                        <td class="inbox-table-icon">
                            <div>
                                <label>
                                    {{ $f->Supplier_Code }}
                                </label>
                            </div>
                        </td>
                        <td class="inbox-table-icon">
                            <div>
                                <label>
                                    {{ $f->Supplier_Name }}
                                </label>
                            </div>
                        </td>
                        <td class="inbox-table-icon">
                            <div>
                                <label>
                                    {{ $f->Supplier_LongAddNo }}
                                </label>
                            </div>
                        </td>
                        <td class="inbox-data-from hidden-xs hidden-sm">
                                <div>
                                    {{ $f->Supplier_City }}
                                </div>
                        </td>
                        <td class="inbox-data-attachment hidden-xs">
                                @if(count($f->invoice) !== 0)
                                    <div>
                                        <i class="fa fa-money fa-lg"></i>
                                    </div>
                                @endif
                        </td>
                </tr>
            @endforeach
        @else
            <tr id="noorders" class="empty">
                <td class="text-align-center">
                    <h1>
                        <i class="fa fa-smile-o"></i> <span>No suppliers at all. Clean & Shiny!</span>
                    </h1>
                </td>
            </tr>
        @endif
	</tbody>
</table>