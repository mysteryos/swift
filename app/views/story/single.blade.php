<li class="timeline-post">
        <?php 
            switch($story->action)
            {
                case SwiftStory::ACTION_CREATE:
                    echo '<div class="smart-timeline-icon"><i class="fa fa-file-text"></i></div>';
                    break;
                case SwiftStory::ACTION_UPDATE:
                    echo '<div class="smart-timeline-icon"><i class="fa fa-pencil"></i></div>';
                    break;
                case SwiftStory::ACTION_CANCEL:
                    echo '<div class="smart-timeline-icon bg-color-red"><i class="fa fa-times"></i></div>';
                    break;
                case SwiftStory::ACTION_COMMENT:
                    echo '<div class="smart-timeline-icon"><i class="fa fa-comment-o"></i></div>';
                    break;
                case SwiftStory::ACTION_STATISTICS:
                    echo '<div class="smart-timeline-icon bg-color-darken"><i class="fa fa-bar-chart-o"></i></div>';
                    break;
                case SwiftStory::ACTION_COMPLETE:
                    echo '<div class="smart-timeline-icon bg-color-greenDark"><i class="fa fa-check"></i></div>';
                    break;
            }
        ?>    
        <div class="smart-timeline-time">
                <small><abbr title="{{date("Y/m/d H:i",strtotime($story->created_at))}}" data-livestamp="{{strtotime($story->created_at)}}"></abbr></small>
        </div>
        <div class="smart-timeline-content">
                <p>
                    {{ \Swift\Avatar::getHTML($story->by,true,"medium") }} {{ Helper::getUserName($story->by,Sentry::getUser()) }} <i>{{ $story->actionText() }}</i> {{ $story->contextText() }}
                </p>
        </div>
</li>