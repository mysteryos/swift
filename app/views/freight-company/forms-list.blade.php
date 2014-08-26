<table id="inbox-table" class="table table-striped table-hover">
	<tbody>
                @if(count($companies) != 0)
                    @foreach($companies as $c)
                        <tr class="orderform" data-pk="{{ Crypt::encrypt($c->id) }}" data-view="/order-tracking/freightcompanyform/{{ Crypt::encrypt($c->id) }}">
                                <td class="inbox-table-icon">
                                        <div class="checkbox">
                                                <label>
                                                        <input type="checkbox" class="checkbox style-2">
                                                        <span></span> </label>
                                        </div>
                                </td>
                                <td class="inbox-table-icon">
                                    <div>
                                        <label>
                                            <a href="/order-tracking/freightcompanymarkstar/{{ Crypt::encrypt($c->id) }}" class="markstar"><i class="fa fa-lg fa-star-o"></i></a>
                                        </label>
                                    </div>
                                </td>
                                <td class="inbox-data-from hidden-xs hidden-sm">
                                        <div>
                                            <span>{{ $c->name }}</span>
                                        </div>
                                </td>
                                <td class="inbox-data-message">
                                        <div>
                                            <span>{{ SwiftFreightCompany::$type[$c->type] || "Unknown" }}</span>
                                        </div>
                                </td>

                                <td class="inbox-data-date hidden-xs">
                                        <span title="{{$c->updated_at->toDayDateTimeString()}}">
                                                @if(date_format(new DateTime($c->updated_at->toDateTimeString()),'d/m/Y') == date('d/m/Y'))
                                                    {{ $c->updated_at->format('g:i a') }}
                                                @elseif($c->updated_at->year != date('Y'))
                                                    {{ $c->updated_at->toFormattedDateString() }}
                                                @else
                                                    {{ $c->updated_at->format('M d') }}
                                                @endif
                                        </span>
                                </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="noorders" class="empty">
                        <td class="text-align-center">
                            <h1>
                                <i class="fa fa-smile-o"></i> <span>No freight companies at all. Clean & Shiny!</span>
                            </h1>
                        </td>
                    </tr>
                @endif
	</tbody>
</table>