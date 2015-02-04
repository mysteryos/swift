<table class="table table-striped table-hover table-responsive">
    @if(!$todoList->isEmpty())
        @foreach($todoList as $row)
            <tr>
                <td>
                    <p><a href="{{ \Helper::generateUrl($row) }}" class="pjax">
                            <strong>{{ "<i class=\"fa $row->getIcon()\"></i> ".$row->getReadableName() }}</strong>
                        </a> - Last update on <abbr title="{{date("Y/m/d H:i",strtotime($row->updated_at))}}" data-livestamp="{{strtotime($row->updated_at)}}"></abbr></p>
                </td>
            </tr>            
        @endforeach
    @else
        <tr>
            <td class="text-center"><h2>No pending tasks. <i class="fa fa-smile-o"></i></h2></td>
        </tr>
    @endif
</table>