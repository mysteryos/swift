@if($requests_present)
    <div class="smart-accordion-default panel-group panel-compressed">
        @if(count($pending_requests))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#aprequest_my_pending_requests" class="txt-color-orangeDark">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-clock-o"></i> Pending (<small class="num-of-tasks">{{ count($pending_requests) }}</small>)
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="aprequest_my_pending_requests">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">
                        @foreach($pending_requests as $papr)
                            <tr>
                                <td>
                                    <p>
                                        <a href="{{ Helper::generateUrl($papr) }}" class="pjax"><strong>{{ $papr->getReadableName() }}</strong></a> - <span class="{{ $papr->current_activity['status_class'] }}">{{ $papr->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($papr->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($papr->activity[0]->created_at))}}" data-livestamp="{{strtotime($papr->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(count($complete_requests))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#aprequest_my_complete_requests" class="txt-color-blueDark">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-check"></i> Complete
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="aprequest_my_complete_requests">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">        
                        @foreach($complete_requests as $capr)
                            <tr>
                                <td>
                                    <p><a href="{{ Helper::generateUrl($capr) }}" class="pjax"><strong>{{ $papr->getReadableName() }}</strong></a> - <span class="{{ $papr->current_activity['status_class'] }}">{{ $papr->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($papr->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($papr->activity[0]->created_at))}}" data-livestamp="{{strtotime($papr->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach
                       </table>
                    </div>
                </div>
            </div>
        @endif
@else
    <h1 class="text-align-center"><i class="fa fa-smile-o"></i> You have no A&P requests so far.</h1>
@endif