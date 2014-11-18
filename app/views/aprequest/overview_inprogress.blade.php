@if($in_progress_present)
    <div class="smart-accordion-default panel-group panel-compressed">
        @if(count($inprogress_important_responsible))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#order_inprogress_important_responsible" class="txt-color-red">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-warning"></i> Important (<small class="num-of-tasks">{{ count($inprogress_important_responsible) }}</small>)
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="order_inprogress_important_responsible">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">
                        @foreach($inprogress_important_responsible as $ori)
                            <tr>
                                <td>
                                    <p><a href="/aprequest/view/{{ Crypt::encrypt($ori->id) }}" class="pjax"><strong>{{ $ori->name." (ID: ".$ori->id.")" }}</strong></a> - <span class="{{ $ori->current_activity['status_class'] }}">{{ $ori->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($ori->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($ori->activity[0]->created_at))}}" data-livestamp="{{strtotime($ori->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(count($inprogress_responsible))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#order_inprogress_responsible" class="txt-color-orangeDark">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-clock-o"></i> In Progress (<small class="num-of-tasks">{{ count($inprogress_responsible) }}</small>)
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="order_inprogress_responsible">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">        
                        @foreach($inprogress_responsible as $or)
                            <tr>
                                <td>
                                    <p><a href="/aprequest/view/{{ Crypt::encrypt($or->id) }}" class="pjax"><strong>{{ $or->name." (ID: ".$or->id.")" }}</strong></a> - <span class="{{ $or->current_activity['status_class'] }}">{{ $or->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($or->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($or->activity[0]->created_at))}}" data-livestamp="{{strtotime($or->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach
                       </table>
                    </div>
                </div>
            </div>
        @endif
        @if(count($inprogress_important))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#order_inprogress_important" class="txt-color-purple">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-exclamation"></i> Other Important (<small class="num-of-tasks">{{ count($inprogress_important) }}</small>)
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="order_inprogress_important">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">        
                        @foreach($inprogress_important as $oi)
                            <tr>
                                <td>
                                    <p><a href="/aprequest/view/{{ Crypt::encrypt($oi->id) }}" class="pjax"><strong>{{ $oi->name." (ID: ".$oi->id.")" }}</strong></a> - <span class="{{ $oi->current_activity['status_class'] }}">{{ $oi->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($oi->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($oi->activity[0]->created_at))}}" data-livestamp="{{strtotime($oi->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach
                       </table>
                    </div>
                </div>
            </div>
        @endif
        @if(count($inprogress))
            <div class="panel panel-default">
                <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#order_inprogress">
                                <i class="fa fa-lg fa-angle-down pull-right"></i>
                                <i class="fa fa-lg fa-angle-up pull-right"></i>
                                <i class="fa fa-map-marker"></i> Other
                            </a>
                        </h4>
                </div>
                <div class="panel-collapse collapse in" id="order_inprogress">
                    <div class="panel-body no-padding">
                        <table class="table table-striped table-hover table-responsive">        
                        @foreach($inprogress as $o)
                            <tr>
                                <td>
                                    <p><a href="/aprequest/view/{{ Crypt::encrypt($o->id) }}" class="pjax"><strong>{{ $o->name." (ID: ".$o->id.")" }}</strong></a> - <span class="{{ $o->current_activity['status_class'] }}">{{ $o->current_activity['label'] }}</span> - Last update by <?php echo Helper::getUserName($o->activity[0]->user_id,Sentry::getUser()); ?>, <abbr title="{{date("Y/m/d H:i",strtotime($o->activity[0]->created_at))}}" data-livestamp="{{strtotime($o->activity[0]->created_at)}}"></abbr></span></p>
                                </td>
                            </tr>            
                        @endforeach  
                       </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@else
    <h1 class="text-align-center"><i class="fa fa-smile-o"></i> No order process in progress right now.</h1>
@endif