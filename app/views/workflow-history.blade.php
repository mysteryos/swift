<div class="container">
    <div class="row">
        <div class="col-xs-12 well well-sm">
            <span class="text-capitalize text-center h4 col-xs-12">Workflow History</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered">
                <tr>
                    <th>
                        Date Updated
                    </th>
                    <th>
                        Task Name
                    </th>
                    <th>
                        Completed By
                    </th>
                    <th>
                        Responsible Parties
                    </th>
                </tr>
                @foreach($workflow->nodes as $n)
                    <tr>
                        <td>
                            <abbr title="{{date("Y/m/d H:i",strtotime($n->updated_at))}}" data-livestamp="{{strtotime($n->updated_at)}}"></abbr>
                        </td>
                        <td>
                            <span class="@if($n->user_id === 0) color-orange @else color-green @endif">{{$n->definition->label}}</span>
                        </td>
                        <td>
                            @if($n->user_id !== 0) {{ \Swift\Avatar::getHTML($n->user_id,false,"medium") }} {{ \Helper::getUserName($n->user_id,Sentry::getUser()) }} @endif
                        </td>
                        <td>
                            {{$n->users or "(Nobody)"}}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>