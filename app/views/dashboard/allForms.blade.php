@if($workflows !== false && !$workflows->isEmpty())
    @foreach($workflows as $row)
        <div class="row post @if($row->is_important){{"bg-color-light-orange"}}@endif" data-url="{{ \Helper::generateUrl($row->workflowable) }}">
            <div class="col-xs-3 cursor-pointer">
                {{ \Swift\Avatar::getHTML($row->activity[0]->user_id,false,"medium") }} <span class="hidden-mobile hidden-tablet">{{ \Helper::getUserName($row->activity[0]->user_id,Sentry::getUser()) }}</span>
            </div>
            <div class="col-xs-6 cursor-pointer">
                <div class="row">
                    <div class="col-xs-12">
                        {{ "<i class=\"fa ".$row->workflowable->getIcon()."\"></i> ".$row->workflowable->getReadableName() }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <span class="col-xs-6 col-md-4">{{$row->workflowable->readableName}}</span><span class="{{ $row->current_activity['status_class'] }} col-xs-6 col-md-8">{{ $row->current_activity['label'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-3 cursor-pointer text-align-right">
                <abbr title="{{date("Y/m/d H:i",strtotime($row->activity[0]->created_at))}}" data-livestamp="{{strtotime($row->activity[0]->created_at)}}"></abbr>
            </div>
        </div>
    @endforeach
    <?php
    $workflows->setBaseUrl('/dashboard/forms');
    ?>
    {{ $workflows->links() }}
@else
    <tr>
        <td class="text-center"><h2>No forms at all. <i class="fa fa-smile-o"></i></h2></td>
    </tr>
@endif