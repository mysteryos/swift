@if($latestWorkflows !== false && !$latestWorkflows->isEmpty())
    @foreach($latestWorkflows as $row)
        <tr data-url="{{ \Helper::generateUrl($row->workflowable) }}" class="post">
            <td>
                <abbr title="{{date("Y/m/d H:i",strtotime($row->activity[0]->created_at))}}" data-livestamp="{{strtotime($row->activity[0]->created_at)}}"></abbr></abbr>
            </td>
            <td>
                {{ \Swift\Avatar::getHTML($row->activity[0]->user_id,false,"medium") }} {{ \Helper::getUserName($row->activity[0]->user_id,Sentry::getUser()) }}                   
            </td>
            <td>
                {{ "<i class=\"fa ".$row->workflowable->getIcon()."\"></i> ".$row->workflowable->getReadableName() }}
            </td>
            <td>
                <span class="{{ $row->current_activity['status_class'] }}">{{ $row->current_activity['label'] }}</span>                    
            </td>
        </tr>
    @endforeach
    <?php 
        $latestWorkflows->setBaseUrl('/dashboard/latestworkflow');
    ?>
    {{ $latestWorkflows->links() }}    
@else
    <tr>
        <td class="text-center"><h2>No pending tasks. <i class="fa fa-smile-o"></i></h2></td>
    </tr>
@endif