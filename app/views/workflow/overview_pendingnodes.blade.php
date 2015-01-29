@if($pending_node_activity!==false)
    <table class="table table-hover">
        <tr>
            <th>Name</th>
            <th>Count</th>
            <th>Oldest from</th>
            <th>Responsible parties</th>
        </tr>
        @foreach($pending_node_activity as $p)
            <tr>
                <td>
                    {{ $p->label }}
                </td>
                <td>
                    {{ $p->count }}
                </td>
                <td>
                    <small><abbr title="{{date("Y/m/d H:i",strtotime($p->min_created_at))}}" data-livestamp="{{strtotime($p->min_created_at)}}"></abbr></small>
                </td>
                <td>
                    @if(isset($p->users))
                        <?php 
                            $userArray = array();
                            foreach($p->users as $u)
                            {
                                $userArray[] = \Helper::getUserName($u->id,\Sentry::getUser());
                            }
                            echo implode(", ",$userArray);
                        ?>
                    @else
                        {{ "Nobody as far as we know" }}
                    @endif
                </td>
            </tr>            
        @endforeach
    </table>
@else
    <h2 class="text-center">No pending nodes so far. Great Job!</h2>
@endif