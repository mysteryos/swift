<tr class="salesmanform" data-pk="{{ Crypt::encrypt($s->id) }}" data-view="/salesman/@if($edit_access){{ "edit" }}@else{{ "view" }}@endif/{{ Crypt::encrypt($s->id) }}">
    <td class="inbox-table-icon @if($s->deleted_at !== null) bg-color-red color-white @endif">
            <div class="checkbox">
                    <label>
                            <input type="checkbox" class="checkbox style-2">
                            <span></span> </label>
            </div>
    </td>
    <td class="inbox-data-from hidden-xs hidden-sm @if($s->deleted_at !== null) bg-color-red color-white @endif">
            <div>
                   <?php
                        if(count($s->revision_latest))
                        {
                            $lastedit_user = User::find($s->revision_latest[0]['user_id']);
                            $me = false;
                            $uniqueusers = array();
                            foreach($s->revision_latest as $r)
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
    <td class="inbox-data-message @if($s->deleted_at !== null) bg-color-red color-white @endif">
                {{ $s->name }}
    </td>

    <td class="inbox-data-date hidden-xs @if($s->deleted_at !== null) bg-color-red color-white @endif">
            <span title="{{$s->updated_at->toDayDateTimeString()}}">
                    @if(date_format(new DateTime($s->updated_at->toDateTimeString()),'d/m/Y') == date('d/m/Y'))
                        {{ $s->updated_at->format('g:i a') }}
                    @elseif($s->updated_at->year != date('Y'))
                        {{ $s->updated_at->toFormattedDateString() }}
                    @else
                        {{ $s->updated_at->format('M d') }}
                    @endif
            </span>
    </td>
</tr>