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
            <div class="well data-container">
                <h2><i class="fa fa-beer"></i> Products <a class="btn btn-success btn-publish row-space-left-2" href="/{{ $rootURL }}/publish-reception/{{$form->encrypted_id}}"><i class="fa fa-share fa-lg"></i> Publish</a></h2>
                @include('product-returns/edit_product_table')
            </div>
        </div>
    </div>
</div>