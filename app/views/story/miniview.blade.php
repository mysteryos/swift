<div class="well well-sm">
    <div class="h3">
        <a href="{{Helper::generateUrl($dynamicStory['context'])}}" class="pjax"><i class="fa {{$dynamicStory['context']->getIcon()}}"></i> {{$dynamicStory['context']->getReadableName()}}</a>
    </div>
    <table class="table table-borderless table-responsive">
        <tr>
            <td><b>Description:</b></td>
            <td>{{$dynamicStory['context']->description or "No Description provided"}}</td>
        </tr>
        <tr>
            <td><b>Last updated:</b></td>
            <td>{{$dynamicStory['context']->updated_at->toDayDateTimeString()}}</td>
        </tr>
    </table>
</div>