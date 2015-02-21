@if(count($lastThreeMonthsCommission))
    <?php 
        $currentMonth = 0;
    ?>
    @foreach($lastThreeMonthsCommission as $com)
        @if($currentMonth != $com->date_start->month || $currentMonth == 0)
            @if($currentMonth !== 0)
                                </table>
                            </div>
                        </div>
                    </div>
                </div>            
            @endif
            <div class="smart-accordion-default panel-group panel-compressed">
                <div class="panel panel-default">
                    <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#commission_last_month_{{ $com->date_start->month }}_{{ $com->date_start->year }}" class="txt-color-darken">
                                    <i class="fa fa-lg fa-angle-down pull-right"></i>
                                    <i class="fa fa-lg fa-angle-up pull-right"></i>
                                    <i class="fa fa-calendar-o"></i>&nbsp;{{ $com->date_start->format('F Y') }}
                                </a>
                            </h4>
                    </div>
                    <div class="panel-collapse collapse in" id="commission_last_month_{{ $com->date_start->month }}_{{ $com->date_start->year }}">
                        <div class="panel-body no-padding">
                            <table class="table table-striped table-hover table-responsive">
                                <th>
                                    Salesman
                                </th>
                                <th>
                                    Commission
                                </th>
        @endif
        <?php $currentMonth = $com->date_start->month; ?>
                                <tr data-url="/{{ $rootURL }}/commission-view/{{ $com->salesman_id }}/{{ $com->date_start->toDateString() }}">
                                    <td>
                                        {{ \Helper::getUserName($com->salesman->user_id,$currentUser) }}
                                    </td>
                                    <td>
                                        Rs. {{ round($com->total,0) }}
                                    </td>
                                </tr>

    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>    
@else
    <h1 class="text-align-center"><i class="fa fa-clock-o"></i> No commission has been calculated so far.</h1>
@endif