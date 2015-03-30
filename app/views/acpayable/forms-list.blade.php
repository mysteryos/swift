<table id="inbox-table" class="table table-striped table-hover">
	<tbody>
        @if(count($forms) != 0)
            <tr>
                <th colspan="3">&nbsp;</th>
                <th>Last Modified By</th>
                <th>Billable Company</th>
                <th>Supplier</th>
                <th>Current Step</th>
                <th>&nbsp;</th>
                <th>Last Modified On</th>
            </tr>
            @foreach($forms as $f)
                <tr class="orderform @if(!$f->flag_read) {{ "unread" }} @endif" data-pk="{{ Crypt::encrypt($f->id) }}" data-view="/{{ $rootURL }}/@if($edit_access){{ "edit" }}@else{{ "view" }}@endif/{{ Crypt::encrypt($f->id) }}">
                        <td class="inbox-table-icon">
                                <div class="checkbox">
                                        <label>
                                                <input type="checkbox" class="checkbox style-2" />
                                                <span></span>
                                        </label>
                                </div>
                        </td>
                        <td class="inbox-table-icon">
                            @if($f->flag_important)
                            <span>
                                <i class="fa fa-exclamation-triangle" title="Important"></i>
                            </span>
                            @endif
                        </td>
                        <td class="inbox-table-icon">
                            <div>
                                <label>
                                    <a href="/{{ $rootURL }}/mark/{{ SwiftFlag::STARRED }}?id={{ urlencode(Crypt::encrypt($f->id)) }}" class="markstar"><i class="fa fa-lg @if($f->flag_starred) {{ "fa-star" }} @else {{ "fa-star-o"}} @endif "></i></a>
                                </label>
                            </div>
                        </td>
                        <td class="inbox-data-from hidden-xs hidden-sm">
                                <div>
                                       <?php
                                            if(count($f->revision_latest))
                                            {
                                                $lastedit_user = User::find($f->revision_latest[0]['user_id']);
                                                $me = false;
                                                $uniqueusers = array();
                                                foreach($f->revision_latest as $r)
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
                                        switch($f->current_activity['status'])
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
                                    ?>"></i></span> <i title="ID">{{ $f->id }}.</i></span> <span>{{$f->company_name}}</span>
                                </div>
                        </td>
                        <td class="inbox-data-message">
                            <span>{{$f->supplier_name}}</span>
                        </td>
                        <td class="inbox-data-message">
                            <span class="{{ $f->current_activity['status_class'] }}">{{ $f->current_activity['label'] }}</span>
                        </td>
                        <td class="inbox-data-attachment hidden-xs">
                                @if($f->document()->count() != 0)
                                    <div>
                                        <i class="fa fa-paperclip fa-lg"></i>
                                    </div>
                                @endif                               
                        </td>

                        <td class="inbox-data-date hidden-xs">
                                <span title="{{$f->updated_at->toDayDateTimeString()}}">
                                        @if(date_format(new DateTime($f->updated_at->toDateTimeString()),'d/m/Y') == date('d/m/Y'))
                                            {{ $f->updated_at->format('g:i a') }}
                                        @elseif($f->updated_at->year != date('Y'))
                                            {{ $f->updated_at->toFormattedDateString() }}
                                        @else
                                            {{ $f->updated_at->format('M d') }}
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