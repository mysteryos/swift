<table id="inbox-table" class="table table-striped table-hover">
	<tbody>
                @if(count($orders) != 0)
                    @foreach($orders as $o)
                        <tr class="orderform @if(!$o->flag_read) {{ "unread" }} @endif" data-pk="{{ Crypt::encrypt($o->id) }}" data-view="/order-tracking/@if($edit_access){{ "edit" }}@else{{ "view" }}@endif/{{ Crypt::encrypt($o->id) }}">
                                <td class="inbox-table-icon">
                                        <div class="checkbox">
                                                <label>
                                                        <input type="checkbox" class="checkbox style-2">
                                                        <span></span> </label>
                                        </div>
                                </td>
                                <td class="inbox-table-icon">
                                    @if($o->flag_important)
                                    <span>
                                        <i class="fa fa-exclamation-triangle" title="Important"></i>
                                    </span>
                                    @endif
                                </td>
                                <td class="inbox-table-icon">
                                    <div>
                                        <label>
                                            <a href="/order-tracking/mark/{{ SwiftFlag::STARRED }}?id={{ urlencode(Crypt::encrypt($o->id)) }}" class="markstar"><i class="fa fa-lg @if($o->flag_starred) {{ "fa-star" }} @else {{ "fa-star-o"}} @endif "></i></a>
                                        </label>
                                    </div>
                                </td>
                                <td class="inbox-data-from hidden-xs hidden-sm">
                                        <div>
                                               <?php
                                                    if(count($o->revision_latest))
                                                    {
                                                        $lastedit_user = User::find($o->revision_latest[0]['user_id']);
                                                        $me = false;
                                                        $uniqueusers = array();
                                                        foreach($o->revision_latest as $r)
                                                        {
                                                            if($r['user_id'] == Sentry::getUser()->id)
                                                            {
                                                                $me = true;
                                                            }
                                                            if(!in_array($r['user_id'],$uniqueusers) && $lastedit_user['user_id'] != $r['user_id'])
                                                            {
                                                                $uniqueusers[] = $r['user_id'];
                                                            }
                                                        }
                                                        $countuniqueusers = count($uniqueusers);
                                                        echo ($lastedit_user->id == Sentry::getUser()->id ? "Me" : "{$lastedit_user->first_name} {$lastedit_user->last_name}").
                                                             ($me && $lastedit_user->id != Sentry::getUser()->id ? ", Me": "").($countuniqueusers ? ($me && $countuniqueusers - 1 ? " ($countuniqueusers)" : "" ) : "");
                                                    }
                                                    else
                                                    {
                                                        echo "(Unknown)";
                                                    }
                                               ?>
                                        </div>
                                </td>
                                <td class="inbox-data-message">
                                        <div>
                                            <span><i class="fa <?php
                                                switch($o->current_activity['status'])
                                                {
                                                    case SwiftWorkflowActivity::COMPLETE:
                                                        echo "fa-check";
                                                        break;
                                                    case SwiftWorkflowActivity::REJECTED:
                                                        echo "fa-times";
                                                        break;
                                                    case SwiftWorkflowActivity::INPROGRESS:
                                                        echo "fa-clock-o";
                                                        break;
                                                    default:
                                                        echo "fa-question";
                                                        break;
                                                    
                                                }
                                                switch($o->business_unit)
                                                {
                                                    case SwiftOrder::SCOTT_CONSUMER:
                                                        echo " txt-color-orangeDark";
                                                        break;
                                                    case SwiftOrder::SCOTT_HEALTH;
                                                        echo " txt-color-green";
                                                        break;
                                                    case SwiftOrder::SEBNA:
                                                        echo " txt-color-blue";
                                                        break;
                                                }
                                            ?>"></i></span> <i title="ID">{{ $o->id }}.</i></span> <span>{{ $o->name }}</span> - <span class="{{ $o->current_activity['status_class'] }}">{{ $o->current_activity['label'] }}</span>
                                        </div>
                                </td>
 
                                <td class="inbox-data-attachment hidden-xs">
                                        @if($o->document()->count() != 0)                                        
                                            <div>
                                                    <i class="fa fa-paperclip fa-lg"></i>
                                            </div>
                                        @endif                               
                                </td>

                                <td class="inbox-data-date hidden-xs">
                                        <span title="{{$o->updated_at->toDayDateTimeString()}}">
                                                @if(date_format(new DateTime($o->updated_at->toDateTimeString()),'d/m/Y') == date('d/m/Y'))
                                                    {{ $o->updated_at->format('g:i a') }}
                                                @elseif($o->updated_at->year != date('Y'))
                                                    {{ $o->updated_at->toFormattedDateString() }}
                                                @else
                                                    {{ $o->updated_at->format('M d') }}
                                                @endif
                                        </span>
                                </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="noorders" class="empty">
                        <td class="text-align-center">
                            <h1>
                                <i class="fa fa-smile-o"></i> <span>No forms at all. Clean & Shiny!</span>
                            </h1>
                        </td>
                    </tr>
                @endif
	</tbody>
</table>