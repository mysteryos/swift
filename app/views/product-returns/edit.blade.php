@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">
        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            @if($publishOwner)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-owner/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishPickup)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-pickup/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishReception)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-reception/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishCreditNote)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-creditnote/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if(isset($isSubscribed))
                <button class="btn btn-default btn-togglesubscribe" data-href="{{$subscriptionUrl}}">
                    <i class="fa fa-lg fa-heart-o" rel="tooltip" data-original-title="Subscribe" data-placement="bottom" @if($isSubscribed)style="display:none;"@endif></i>
                    <i class="fa fa-lg fa-heart" rel="tooltip" data-original-title="Unsubscribe" data-placement="bottom" @if(!$isSubscribed)style="display:none;"@endif></i>
                </button>
            @endif
            <a class="btn btn-default btn-mark-important" href="/{{ $rootURL }}/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>
            @if($edit)<a class="btn btn-default btn-help" data-href="/{{$rootURL}}/help/{{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="Help" data-placement="bottom"><i class="fa fa-lg fa-question"></i></a>@endif
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && $isAdmin)<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/{{ $rootURL }}/cancel/{{$form->encrypted_id}}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax" href="{{ URL::previous() }}" rel="tooltip" data-original-title="Back" data-placement="bottom"><i class="fa fa-lg fa-arrow-left"></i></a>
            <a class="btn btn-default pjax btn-ribbon-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" href="{{ URL::current() }}"><i class="fa fa-lg fa-refresh"></i></a>
            @if($publishOwner)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-owner/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishPickup)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-pickup/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishReception)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-reception/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishCreditNote)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/publish-creditnote/{{$form->encrypted_id}}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if(isset($isSubscribed))
                <button class="btn btn-default btn-togglesubscribe" data-href="{{$subscriptionUrl}}">
                    <i class="fa fa-lg fa-heart-o" rel="tooltip" data-original-title="Subscribe" data-placement="bottom" @if($isSubscribed)style="display:none;"@endif></i>
                    <i class="fa fa-lg fa-heart" rel="tooltip" data-original-title="Unsubscribe" data-placement="bottom" @if(!$isSubscribed)style="display:none;"@endif></i>
                </button>
            @endif
            <a class="btn btn-default btn-mark-important" href="/{{ $rootURL }}/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && $isAdmin)<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/{{ $rootURL }}/cancel/{{$form->encrypted_id}}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>
</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="@if($edit){{"pr_edit"}}@else{{"pr_view"}}@endif" data-urljs="@if($edit){{Bust::url('/js/swift/swift.pr_edit.js')}}@else{{Bust::url('/js/swift/swift.pr_view.js')}}@endif">
    <input type="hidden" name="id" id="id" value="{{$form->encrypted_id}}" />
    <input type="hidden" name="last_update" id="last_update" value="{{ $form->updated_at }}" />
    <input type="hidden" name="channel_name" id="channel_name" value="{{$form->channelName()}}" />
    <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
    <input type="hidden" id="project-name" value='<i class="fa-fw fa fa-reply"></i> {{ $form->name }} (ID: #{{ $form->id }})'/>
    <input type="hidden" id="project-id" value='pr_{{ $form->id }}' />
    <div id="draghover" class="text-align-center">
        <div class="circle bg-color-blue">
            <i class="fa fa-cloud-upload fa-4x"></i><br>
            <h2 class="text-align-center ">Incoming!</h2>
            <p class="text-align-center">Drop your files instantly to upload it!</p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <!-- PAGE HEADER -->
                <i class="fa-fw fa fa-reply"></i>
                    Product Returns: {{$form->type_name}}
                <span>&gt;
                    ID: #{{ $form->id }}
                </span>
                <span>
                    &nbsp;By {{ $owner }}
                </span>
            </h1>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <span id="workflow_status">Current Step: <span class="{{$current_activity['status_class']}}">{{ $current_activity['label'] }}</span></span> <a href="/workflow/by-form/{{get_class($form)}}/{{$form->encrypted_id}}" class="colorbox-ajax" rel="tooltip" data-placement="bottom" data-original-title="Workflow History"><i class="fa fa-history"></i></a>
            </h1>
        </div>
        <div class="hidden-xs hidden-sm col-md-4 col-lg-4">
            <h1 class="page-title">
                <span>Last update was by <?php echo Helper::getUserName($activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($activity[0]->created_at))}}" data-livestamp="{{strtotime($activity[0]->created_at)}}"></abbr></span>
            </h1>
        </div>
    </div>
    
    <!-- widget grid -->
    <section id="widget-grid">

        <!-- START ROW -->
        <div class="row">
            <!-- NEW COL START -->
            <article class="col-lg-12 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="pr-generalInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>General Info </h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                @include('product-returns.edit_generalinfo',array('form'=>$form))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="pr-products" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-beer"></i> </span>
                        <h2>Products</h2>
                        @if(($edit && $addProduct) || $isAdmin)
                            <div class="widget-toolbar" role="menu">
                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                            </div>
                        @endif
                        <div class="widget-toolbar">
                                <!-- add: non-hidden - to disable auto hide -->
                                <div class="btn-group">
                                        <button class="btn dropdown-toggle btn-xs btn-default" data-toggle="dropdown">
                                                Showing <i class="fa fa-caret-down"></i>
                                        </button>
                                        <ul class="dropdown-menu product-filter pull-right">
                                                <li>
                                                        <a href="javascript:void(0);" id="product_filter_all">All</a>
                                                </li>
                                                <li>
                                                        <a href="javascript:void(0);" id="product_filter_approved" data-approvalstatus="1">Approved</a>
                                                </li>
                                                <li>
                                                        <a href="javascript:void(0);" id="product_filter_rejected" data-approvalstatus="-1">Rejected</a>
                                                </li>
                                                <li>
                                                        <a href="javascript:void(0);" id="product_filter_pending" data-approvalstatus="0">Pending</a>
                                                </li>
                                        </ul>
                                </div>
                        </div>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                <table class="table table-bordered table-responsive">
                                    <tr>
                                        <th rowspan='2'>
                                            Id
                                        </th>
                                        <th rowspan='2'>
                                            Product
                                        </th>
                                        @if(!$addProduct)
                                            <th rowspan='2'>
                                                Approval
                                            </th>
                                            <th rowspan='2'>
                                                Comment
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
                                            Comment
                                        </th>
                                        <th colspan='@if($form->type === \SwiftPR::SALESMAN && !$addProduct){{5}}@else{{1}}@endif' class="text-center">
                                            Quantity
                                        </th>
                                        <th rowspan='2'>
                                            &nbsp;
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>
                                            Client
                                        </th>
                                        @if($form->type === \SwiftPR::SALESMAN && !$addProduct)
                                            <th>
                                                Pickup
                                            </th>
                                            <th>
                                                Store
                                            </th>
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
                                            <?php $p->id = Crypt::encrypt($p->id); ?>
                                            @include('product-returns.edit_product',array('p'=>$p))
                                        @endforeach
                                    @else
                                        @include('product-returns.edit_product')
                                    @endif
                                    @include('product-returns.edit_product',array('dummy'=>true,'p'=>null))
                                </table>
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
            </article>
            <!-- NEW COL END -->
            
            <!-- NEW COL START -->
            <article class="col-lg-8 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="pr-erporder" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-shopping-cart"></i> </span>
                        <h2>JDE Order </h2>
                        @if($edit && ($isCcare || $isAdmin))
                            <div class="widget-toolbar" role="menu">
                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                            </div>
                        @endif
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                    @if(count($form->order))
                                        @foreach($form->order as &$e)
                                            <?php $e->id = Crypt::encrypt($e->id); ?>
                                            @include('product-returns.edit_erporder',array('e'=>$e))
                                        @endforeach
                                    @else
                                        @include('product-returns.edit_erporder')
                                    @endif
                                    @include('product-returns.edit_erporder',array('dummy'=>true,'e'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

                @if($form->type !== \SwiftPR::INVOICE_CANCELLED)
                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="pr-pickup" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-truck"></i> </span>
                            <h2>Pickup </h2>
                            @if($edit && ($isStorePickup || $isAdmin))
                                <div class="widget-toolbar" role="menu">
                                    <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                                </div>
                            @endif
                        </header>
                        <!-- widget div-->
                        <div>
                            <!-- widget content -->
                            <div class="widget-body">
                                <form class="form-horizontal">
                                        @if(count($form->pickup))
                                            @foreach($form->pickup as &$pickup)
                                                <?php $pickup->id = Crypt::encrypt($pickup->id); ?>
                                                @include('product-returns.edit_pickup',array('pickup'=>$pickup))
                                            @endforeach
                                        @else
                                            @include('product-returns.edit_pickup')
                                        @endif
                                        @include('product-returns.edit_pickup',array('dummy'=>true,'pickup'=>null))
                                </form>
                            </div>
                            <!-- end widget content -->
                        </div>
                        <!-- end widget div -->
                    </div>
                    <!-- end widget -->
                @endif
                
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="pr-credit-note" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-file-archive-o"></i> </span>
                        <h2>Credit Note </h2>
                        @if($edit && ($isCreditor || $isAdmin))
                            <div class="widget-toolbar" role="menu">
                                <a class="btn btn-primary btn-add-new" href="javascript:void(0);"><i class="fa fa-plus"></i> Add</a>
                            </div>
                        @endif
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                    @if(count($form->creditNote))
                                        @foreach($form->creditNote as &$c)
                                            <?php $c->id = Crypt::encrypt($c->id); ?>
                                            @include('product-returns.edit_credit_note',array('c'=>$c))
                                        @endforeach
                                    @else
                                        @include('product-returns.edit_credit_note')
                                    @endif
                                    @include('product-returns.edit_credit_note',array('dummy'=>true,'c'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
            </article>
            <!-- NEW COL END -->
                
            <!-- NEW COL START -->
            <article class="col-lg-4 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="pr-docs" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-file-o"></i> </span>
                        <h2>Docs </h2>
                        @if($edit)
                            <div class="widget-toolbar" role="menu">
                                <a class="btn btn-primary" id="btn-upload" href="javascript:void(0);"><i class="fa fa-plus"></i> Upload</a>
                            </div>
                        @endif
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <div id="upload-preview">
                                @include('product-returns.upload',array('doc'=>$form->document,'tags'=>$tags,'dummy'=>true))
                            </div>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

                <!-- Widget ID (each widget will need unique ID)-->
               <div class="jarviswidget" id="apr-swiftchat" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                   <header>
                       <span class="widget-icon"> <i class="fa fa-comment"></i> </span>
                        <h2>Chat </h2>
                   </header>
                    <!-- widget div-->
                   <div>
                       <!-- widget content -->
                       <div class="widget-body widget-hide-overflow no-padding">
                           @include('comments', array('commentable' => $form, 'comments' => $comments))
                       </div>
                        <!-- end widget content -->
                   </div>
                    <!-- end widget div -->
               </div>
               <!-- end widget -->

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="apr-activity" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                        <h2>Activity </h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body nopadding">
                            <div class="activity-container">
                                @include('product-returns.edit_activity',array('activity'=>$activity))
                            </div>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
            </article>
            <!-- NEW COL END -->
        </div>
        <!-- END ROW -->

    </section>
    <!-- WIDGET GRID END -->

</div>

@stop                