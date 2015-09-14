@if($permission->canCreate())
    <div class="btn-group hidden-tablet hidden-mobile">
        <button class="btn btn-default txt-color-darken disabled">Create: </button>
        @if($permission->canCreateSalesman())
            <a href="/{{ $rootURL }}/create/{{ \SwiftPR::SALESMAN }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create Salesman" data-placement="bottom"><i class="fa fa-lg fa-user"></i></a>
        @endif
        @if($permission->canCreateOnDelivery())
            <a href="/{{ $rootURL }}/create/{{ \SwiftPR::ON_DELIVERY }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create On Delivery" data-placement="bottom"><i class="fa fa-lg fa-truck"></i></a>
        @endif
        @if($permission->canCreateInvoiceCancelled())
            <a href="/{{ $rootURL }}/create/{{ \SwiftPR::INVOICE_CANCELLED }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create Invoice Cancelled" data-placement="bottom"><i class="fa fa-lg fa-times"></i></a>
        @endif
    </div>
    <div class="btn-group visible-mobile visible-tablet hidden-lg">
        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
            <i class="fa fa-lg fa-file-o"></i> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            @if($permission->canCreateSalesman())
                <li>
                    <a href="/{{ $rootURL }}/create/{{ \SwiftPR::SALESMAN }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create Salesman" data-placement="bottom"><i class="fa fa-lg fa-user"></i></a>
                </li>
            @endif
            @if($permission->canCreateOnDelivery())
                <li>
                    <a href="/{{ $rootURL }}/create/{{ \SwiftPR::ON_DELIVERY }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create On Delivery" data-placement="bottom"><i class="fa fa-lg fa-truck"></i></a>
                </li>
            @endif
            @if($permission->canCreateInvoiceCancelled())
                <li>
                    <a href="/{{ $rootURL }}/create/{{ \SwiftPR::INVOICE_CANCELLED }}" class="btn btn-default pjax" rel="tooltip" data-original-title="Create Invoice Cancelled" data-placement="bottom"><i class="fa fa-lg fa-times"></i></a>
                </li>
            @endif
        </ul>
    </div>
@endif