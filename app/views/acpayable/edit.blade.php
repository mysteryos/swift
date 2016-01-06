@extends('layout')

@section('content')

<!-- RIBBON -->
<div id="ribbon">

        <div class="ribbon-button-alignment hidden-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
            @if($permission->canCreate())
            <a href="/{{ $rootURL }}/create" class="btn btn-default pjax" rel="tooltip" data-original-title="Create" data-placement="bottom"><i class="fa fa-lg fa-file"></i></a>
            @endif
            @if($publishOwner)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/formapprovalowner/{{ $form->encrypted_id }}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishAccounting && $current_activity['status'] === SwiftWorkflowActivity::INPROGRESS && ($permission->isAccountingDept() || $permission->isAdmin()))<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/formapprovalaccounting/{{ $form->encrypted_id }}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            <a class="btn btn-default btn-ribbon-share colorbox-ajax" rel="tooltip" data-original-title="Share" data-placement="bottom" href="{{\Helper::generateShareUrl($form)}}"><i class="fa fa-lg fa-reply-all"></i></a>
            @if($edit)<a class="btn btn-default btn-help" data-href="/{{ $rootURL }}/help/{{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="Help" data-placement="bottom"><i class="fa fa-lg fa-question"></i></a>@endif
            @if($permission->isAdmin())<a class="btn btn-default btn-mark-important" href="/{{ $rootURL }}/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>@endif
            @if($currentUser->isSuperUser() || $permission->isAdmin())<a class="btn btn-default btn-force-update" data-href="/workflow/force-update/{{$context}}/{{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="Force Workflow Update" data-placement="bottom"><i class="fa fa-lg fa-rocket"></i></a>@endif
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && $permission->isAdmin())<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/{{ $rootURL }}/cancel/{{ $form->encrypted_id }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>
        <div class="pull-right hidden-xs whos-online"></div>
        <div class="ribbon-button-alignment-xs visible-xs">
            <a class="btn btn-default pjax-refresh" rel="tooltip" data-original-title="Refresh" data-placement="bottom" id="btn-ribbon-refresh" href="javascript:void(0);"><i class="fa fa-lg fa-refresh"></i></a>
            @if($permission->canCreate())
            <a href="/{{ $rootURL }}/create" class="btn btn-default pjax" rel="tooltip" data-original-title="Create" data-placement="bottom"><i class="fa fa-lg fa-file"></i></a>
            @endif
            @if($publishOwner && $current_activity['status'] === SwiftWorkflowActivity::INPROGRESS)<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/formapprovalowner/{{ $form->encrypted_id }}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            @if($publishAccounting && $current_activity['status'] === SwiftWorkflowActivity::INPROGRESS && ($permission->isAccountingDept() || $permission->isAdmin()))<a class="btn btn-default btn-publish" href="/{{ $rootURL }}/formapprovalaccounting/{{ $form->encrypted_id }}" rel="tooltip" data-original-title="Publish Form" data-placement="bottom"><i class="fa fa-share fa-lg"></i></a>@endif
            <a class="btn btn-default btn-ribbon-share colorbox-ajax" rel="tooltip" data-original-title="Share" data-placement="bottom" href="{{\Helper::generateShareUrl($form)}}"><i class="fa fa-lg fa-reply-all"></i></a>
            @if($edit)<a class="btn btn-default btn-help" data-href="/{{ $rootURL }}/help/{{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="Help" data-placement="bottom"><i class="fa fa-lg fa-question"></i></a>@endif
            @if($currentUser->isSuperUser() || $permission->isAdmin())<a class="btn btn-default btn-force-update" data-href="/workflow/force-update/{{$context}}/{{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="Force Workflow Update" data-placement="bottom"><i class="fa fa-lg fa-rocket"></i></a>@endif
            @if($permission->isAdmin())<a class="btn btn-default btn-mark-important" href="/{{ $rootURL }}/mark/{{ SwiftFlag::IMPORTANT }}?id={{ urlencode($form->encrypted_id) }}" rel="tooltip" data-original-title="@if($flag_important) {{ "Unmark as important" }} @else {{ "Mark as important" }} @endif" data-placement="bottom"><i class="fa fa-lg @if($flag_important) {{ "fa-exclamation-triangle" }} @else {{ "fa-exclamation" }} @endif"></i></a>@endif
            @if($current_activity['status']==SwiftWorkflowActivity::INPROGRESS && $permission->isAdmin())<a class="btn btn-default btn-ribbon-cancel" rel="tooltip" data-original-title="Cancel" data-placement="bottom" href="/{{ $rootURL }}/cancel/{{ $form->encrypted_id }}"><i class="fa fa-lg fa-times"></i></a>@endif
        </div>

</div>
<!-- END RIBBON -->

<!-- MAIN CONTENT -->
<div id="content" data-js="@if($edit){{"acp_edit"}}@else{{"acp_view"}}@endif" data-urljs="@if($edit){{Bust::url('/js/swift/swift.acp_edit.js')}}@else{{Bust::url('/js/swift/swift.acp_view.js')}}@endif">
    <input type="hidden" name="id" id="id" value="{{ $form->encrypted_id }}" />
    <input type="hidden" name="last_update" id="last_update" value="{{ $form->updated_at }}" />
    <input type="hidden" name="channel_name" id="channel_name" value="{{ $form->channelName() }}" />
    <input type="hidden" id="project-url" value="{{ URL::current() }}"/>
    <input type="hidden" id="project-name" value='<i class="fa-fw fa {{ $form->getIcon() }}"></i> {{ $form->getReadableName() }}'/>
    <input type="hidden" id="project-id" value='{{ $form->channelName() }}' />
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
                <i class="fa-fw fa {{$form->getIcon()}}"></i>
                    Accounts Payable
                <span>&gt;
                    Id: #{{ $form->getKey() }}
                </span>
                <span>
                    &nbsp;By {{ $owner }}
                </span>
                @if($form->payable)
                <br/>
                <span> Related To: <a href="{{Helper::generateURL($form->payable)}}" class="pjax"><i class="fa {{$form->payable->getIcon()}}"></i> {{$form->payable->getReadableName()}}</a></span>
                @endif
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

    @if(isset($message) && $message !== false)
        <div class="row">
            <article class="col-xs-12">
                @foreach($message as $m)
                    <?php
                        switch($m['type'])
                        {
                            case 'warning':
                                echo '<div class="alert alert-warning fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-warning"></i>
                                    <strong>Warning</strong> ';
                                break;
                            case 'success':
                                echo '<div class="alert alert-success fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-check"></i>
                                    <strong>Success</strong> ';
                                break;
                            case 'danger':
                                echo '<div class="alert alert-danger fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-times"></i>
                                    <strong>Error!</strong> ';
                                break;
                            default:
                            case 'info':
                                echo '<div class="alert alert-info fade in">
                                    <button data-dismiss="alert" class="close">
                                            ×
                                    </button>
                                    <i class="fa-fw fa fa-info"></i>
                                    <strong>Info!</strong> ';
                                break;
                        }
                        echo $m['msg']."</div>";
                    ?>
                @endforeach
            </article>
        </div>
    @endif

    <!-- widget grid -->
    <section id="widget-grid">

        <!-- START ROW -->

        <div class="row">

            <!-- NEW COL START -->
            <article class="col-lg-8 col-xs-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="apc-generalInfo" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i></span>
                        <h2>General Info </h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                @include('acpayable.edit_generalinfo',array('form'=>$form))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="acp-erporder" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-shopping-cart"></i> </span>
                        <h2>JDE Order </h2>
                        @if($edit && ($permission->isAccountingDept() || $permission->isAdmin()))
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
                                @if(count($form->purchaseOrder))
                                    @foreach($form->purchaseOrder as &$p)
                                        <?php $p->encrypted_id = \Crypt::encrypt($p->id); ?>
                                        @include('acpayable.edit_purchaseorder',array('p'=>$p))
                                    @endforeach
                                @else
                                    @include('acpayable.edit_purchaseorder')
                                @endif
                                @include('acpayable.edit_purchaseorder',array('dummy'=>true,'p'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->

                <div class="jarviswidget" id="acp-invoice" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-ticket"></i> </span>
                        <h2>Invoice</h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                    @if($form->invoice)
                                        <?php $form->invoice->encrypted_id = \Crypt::encrypt($form->invoice->id); ?>
                                        @include('acpayable.edit_invoice',array('i'=>$form->invoice))
                                    @else
                                        @include('acpayable.edit_invoice')
                                    @endif
                                    @include('acpayable.edit_invoice',array('dummy'=>true,'i'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>

                <div class="jarviswidget" id="acp-payment-suggestion" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-cog"></i> </span>
                        <h2>Payment Suggestion</h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                @if($form->paymentSuggestion)
                                    <?php $form->paymentSuggestion->encrypted_id = \Crypt::encrypt($form->paymentSuggestion->id); ?>
                                    @include('acpayable.edit_payment_suggestion',array('paySuggest'=>$form->paymentSuggestion))
                                @else
                                    @include('acpayable.edit_payment_suggestion')
                                @endif
                                @include('acpayable.edit_payment_suggestion',array('dummy'=>true,'paySuggest'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>

                <div class="jarviswidget" id="acp-hod-approval" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-check"></i> </span>
                        <h2>Approval HOD</h2>
                        @if($edit)
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
                                    @if(count($form->approvalHod))
                                        @foreach($form->approvalHod as &$approvalHod)
                                            <?php $approvalHod->encrypted_id = \Crypt::encrypt($approvalHod->id); ?>
                                            @include('acpayable.edit_approval_hod',array('approval'=>$approvalHod))
                                        @endforeach
                                    @else
                                        @include('acpayable.edit_approval_hod')
                                    @endif
                                    @include('acpayable.edit_approval_hod',array('dummy'=>true,'approval'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>

                <div class="jarviswidget" id="acp-creditnote" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-file"></i> </span>
                        <h2>Credit Note </h2>
                        @if($edit && ($permission->isAccountingDept() || $permission->isAdmin() || $form->isOwner() || $permission->isHOD()))
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
                                        <?php $c->encrypted_id = \Crypt::encrypt($c->id); ?>
                                        @include('acpayable.edit_creditnote',array('c'=>$c))
                                    @endforeach
                                @else
                                    @include('acpayable.edit_creditnote')
                                @endif
                                @include('acpayable.edit_creditnote',array('dummy'=>true,'c'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                @if(!$edit || $currentUser->isSuperUser() || ($savePaymentVoucher || $signCheque || $publishAccounting || $checkPayment) || $permission->isAdmin())
                <div class="jarviswidget" id="acp-paymentvoucher" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-file-archive-o"></i> </span>
                        <h2>Payment Voucher</h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            <form class="form-horizontal">
                                    @if($form->paymentVoucher)
                                        <?php $form->paymentVoucher->encrypted_id = \Crypt::encrypt($form->paymentVoucher->id); ?>
                                        @include('acpayable.edit_paymentvoucher',array('pv'=>$form->paymentVoucher))
                                    @else
                                        @include('acpayable.edit_paymentvoucher')
                                    @endif
                                    @include('acpayable.edit_paymentvoucher',array('dummy'=>true,'pv'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <div class="jarviswidget" id="acp-payment" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-money"></i> </span>
                        <h2>Payment</h2>
                        @if($edit && ($permission->isAccountingDept() || $permission->isAdmin()))
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
                                    @if(count($form->payment))
                                        @foreach($form->payment as &$p)
                                            <?php $p->encrypted_id = \Crypt::encrypt($p->id); ?>
                                            @include('acpayable.edit_payment',array('pay'=>$p))
                                        @endforeach
                                    @else
                                        @include('acpayable.edit_payment')
                                    @endif
                                    @include('acpayable.edit_payment',array('dummy'=>true,'pay'=>null))
                            </form>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
                @endif

            </article>
            <!-- NEW COL END -->

            <!-- NEW COL START -->
            <article class="col-lg-4 col-xs-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="acp-docs" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
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
                                @include('acpayable.upload',array('doc'=>$form->document,'tags'=>$tags,'dummy'=>true))
                            </div>
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
                @if($form->payable)
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="acp-associate" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-plus-circle"></i> </span>
                        <h2>Related </h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body">
                            @include('acpayable.edit_associate')
                        </div>
                        <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                </div>
                <!-- end widget -->
                @endif
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="acp-swiftchat" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
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
                <div class="jarviswidget" id="acp-activity" data-widget-deletebutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-history"></i> </span>
                        <h2>Activity </h2>
                    </header>
                    <!-- widget div-->
                    <div>
                        <!-- widget content -->
                        <div class="widget-body nopadding">
                            <div class="activity-container">
                                @include('acpayable.edit_activity',array('activity'=>$activity))
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