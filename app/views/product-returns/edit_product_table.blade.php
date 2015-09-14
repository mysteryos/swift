<table class="table table-bordered table-responsive">
    <tr>
        <th rowspan='2'>
            Id
        </th>
        <th rowspan='2'>
            Product
        </th>
        <th rowspan='2'>
            Invoice No
        </th>
        @if(!$publishOwner || $permission->isAdmin())
        <th rowspan='2'>
            Approval
        </th>
        <th rowspan='2'>
            Approval Comment
        </th>
        @endif
        @if($form->type === \SwiftPR::SALESMAN)
            <th rowspan='2'>
                Pickup
            </th>
        @endif
        <th rowspan='2'>
            Reason
        </th>
        <th rowspan='2'>
            Reason Comment
        </th>
        <th colspan='@if(!$edit || $publishReception || $publishStoreValidation || $permission->isAdmin()){{5}}@elseif($form->type === \SwiftPR::ON_DELIVERY && $publishOwner){{4}}@else{{1}}@endif' class="text-center">
            Quantity
        </th>
        @if(($addProduct && $isOwner) || $permission->isAdmin())
        <th rowspan='2'>
            &nbsp;
        </th>
        @endif
    </tr>
    <tr>
        <th>
            Client
        </th>
        @if(!$edit || $publishReception || $publishStoreValidation || ($form->type === \SwiftPR::ON_DELIVERY && $publishOwner) || $permission->isAdmin())
            <th>
                Pickup
            </th>
            @if(!$publishReception && !($form->type === \SwiftPR::ON_DELIVERY && $publishOwner))
            <th>
                Store
            </th>
            @endif
            <th>
                Picking
            </th>
            <th>
                Disposal
            </th>
        @endif
    </tr>
    @if(count($form->product))
        @foreach($form->product as &$p)
            <?php $p->encrypted_id = \Crypt::encrypt($p->id); ?>
            @include('product-returns.edit_product',array('p'=>$p))
        @endforeach
    @else
        @include('product-returns.edit_product')
    @endif
    @include('product-returns.edit_product',array('dummy'=>true,'p'=>null))
</table>