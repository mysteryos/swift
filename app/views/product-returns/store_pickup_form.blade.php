<div class="form panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a href="{{\Helper::generateURL($form)}}" class="pjax pull-left txt-color-orangeDark" rel="tooltip" data-original-title="Click to view form" data-placement="bottom">
                #{{$form->id}}
            </a>
            <a class="collapsed" href="#pr_form_{{$form->id}}" data-toggle="collapse">
                {{$form->type_name}} - {{\Helper::getUserName($form->owner_user_id,$currentUser)}} : {{$form->customer->getReadableName()}}
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
            </a>
        </h4>
    </div>
    <div id="pr_form_{{$form->id}}" class="panel-collapse collapse">
        <div class="panel-body no-padding">
            <div class="well">
                <h2><i class="fa fa-beer"></i> Products</h2>
                <table class="table table-hover table-condensed">
                    @foreach($form->product as $p)
                        @include('product-returns/store_pickup_product')
                    @endforeach
                </table>
            </div>
            <div class="well data-container">
                <div class="row">
                   <div class="col-xs-12">
                        <span class="h2 pull-left"><i class="fa fa-truck"></i> Pickup</span>
                        @if($isStorePickup || $isAdmin)
                            <span class="h2 pull-left row-space-left-4">
                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                            </span>
                            <span class="h2 pull-left row-space-left-4">
                                <a class="btn btn-default btn-print" href="/{{$rootURL}}/print-pickup/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Print Pickup List" data-placement="bottom" target="_blank"><i class="fa fa-print fa-lg"></i> Print</a>
                            </span>
                            <span class="h2 pull-left row-space-left-4">
                                <a class="btn btn-success btn-publish" href="/{{ $rootURL }}/publish-pickup/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"> Publish</i></a>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <form class="form-horizontal col-xs-12">
                        @if(count($form->pickup))
                            @foreach($form->pickup as &$pickup)
                                <?php $pickup->encrypted_id = $pickup->id; ?>
                                @include('product-returns.edit_pickup',array('pickup'=>$pickup))
                            @endforeach
                        @else
                            @include('product-returns.edit_pickup')
                        @endif
                        @include('product-returns.edit_pickup',array('dummy'=>true,'pickup'=>null))
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>