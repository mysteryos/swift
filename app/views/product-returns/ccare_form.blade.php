<div class="form panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a href="{{\Helper::generateURL($form)}}" class="pjax pull-left txt-color-orangeDark" rel="tooltip" data-original-title="Click to view form" data-placement="bottom">
                #{{$form->id}}
            </a>
            <a class="collapsed" href="#pr_form_{{$form->id}}" data-toggle="collapse">
                {{\Helper::getUserName($form->owner_user_id,$currentUser)}} : {{$form->customer->getReadableName()}}
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
                        @include('product-returns/ccare_product')
                    @endforeach
                </table>
            </div>
            <div class="well data-container">
                <div class="row">
                    <div class="col-xs-12">
                        <span class="h2 pull-left"><i class="fa fa-shopping-cart row-space-right-2"></i>JDE Order </span>
                        @if($isCcare || $isAdmin)
                        <span class="h2 pull-left row-space-left-4">
                            <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="ro">
                    <form class="form-horizontal">
                        @if(count($form->order))
                            @foreach($form->order as &$e)
                                <?php $e->encrypted_id = \Crypt::encrypt($e->id); ?>
                                @include('product-returns.edit_erporder',array('e'=>$e))
                            @endforeach
                        @else
                            @include('product-returns.edit_erporder')
                        @endif
                        @include('product-returns.edit_erporder',array('dummy'=>true,'e'=>null))
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>