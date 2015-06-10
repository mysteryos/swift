<div class="form panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a href="{{\Helper::generateURL($form)}}" class="pjax pull-left txt-color-orangeDark" rel="tooltip" data-original-title="Click to view form" data-placement="bottom">
                #{{$form->id}}
            </a>
            <a class="collapsed" href="#ap_form_{{$form->id}}" data-toggle="collapse">
                {{\Helper::getUserName($form->requester_user_id,$currentUser)}} : {{$form->customer->getReadableName()}} - {{$form->name}}
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
            </a>
        </h4>
    </div>
    <div id="ap_form_{{$form->id}}" class="panel-collapse collapse">
        <div class="panel-body no-padding">
            <table class="table table-hover table-condensed">
                @foreach($form->product as $p)
                    @include('aprequest.approval_product')
                @endforeach
            </table>
        </div>
    </div>
</div>