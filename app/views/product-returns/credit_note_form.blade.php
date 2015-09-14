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
                <p><i class="fa fa-shopping-cart"></i> Orders</p>
                <table class="table table-hover table-condensed">
                    <tr>
                        <th>
                            Number
                        </th>
                        <th>
                            Type
                        </th>
                        <th>
                            Status
                        </th>
                    </tr>
                    @foreach($form->order as $o)
                        @include('product-returns/credit_note_order')
                    @endforeach
                </table>
            </div>
            <div class="well data-container">
                <div class="row">
                    <div class="col-xs-12">
                        <span class="h2 pull-left"><i class="fa fa-file-archive-o row-space-right-2"></i>Credit Note </span>
                        @if($permission->isCreditor() || $permission->isAdmin())
                        <span class="h2 pull-left row-space-left-4">
                            <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                            <a class="btn btn-success btn-publish row-space-left-2" href="/{{ $rootURL }}/publish-credit-note/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i> Publish</a>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <form class="form-horizontal col-xs-12">
                        @if(count($form->creditnote))
                            @foreach($form->creditnote as &$c)
                                <?php $c->id = Crypt::encrypt($c->id); ?>
                                @include('product-returns.edit_credit_note',array('c'=>$c))
                            @endforeach
                        @else
                            @include('product-returns.edit_credit_note')
                        @endif
                        @include('product-returns.edit_credit_note',array('dummy'=>true,'c'=>null))
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>