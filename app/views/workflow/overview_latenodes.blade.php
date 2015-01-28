@if($late_node_forms!==false)
    <table class="table table-hover">
        <tr>
            <th>Name</th>
            <th>Task</th>
            <th>Due Since</th>
            <th>Responsible parties</th>
        </tr>
        @foreach($late_node_forms as $l)
            <tr>
                <td>
                    <a href="{{ \Helper::generateUrl($l->workflowactivity->workflowable) }}" class="pjax">{{ $l->workflowactivity->workflowable->getReadableName() }}</a>
                </td>
                <td>
                    {{ $l->definition->label }}
                </td>
                <td>
                    <small><abbr title="{{date("Y/m/d H:i",strtotime($l->dueSince))}}" data-livestamp="{{strtotime($l->dueSince)}}"></abbr></small>
                </td>
                <td>
                    @if(isset($l->users))
                        <?php
                            $userArray = array();
                            foreach($l->users as $u)
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
    <p class="text-right">Total: {{ $late_node_forms_count }}</p>
@else
    <h2 class="text-center">No late nodes. Great Job!</h2>
@endif