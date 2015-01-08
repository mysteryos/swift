<li>
        <div class="smart-timeline-icon bg-color-orange"><i class="fa fa-bell-o"></i></div>
        <div class="smart-timeline-time">
                <small><abbr title="{{Carbon::now()->format("Y/m/d H:i")}}" data-livestamp="{{strtotime(Carbon::now()->toDateTimeString())}}"></abbr></small>
        </div>
        <div class="smart-timeline-content">
                <p>
                    {{ $dynamicStory['actionText'] }}
                </p>
                @include('story.miniview')
        </div>
</li>