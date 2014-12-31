<li>
        <span class="@if($notification->unread === \SwiftNotification::UNREAD) {{ "unread" }} @endif">
                <a href="{{ Helper::generateUrl($notification->notifiable) }}" class="msg pjax">
                    {{ \Swift\Avatar::getHTML($notification->from,false,"medium",array('air','air-top-left')) }}
                    <span class="from">{{ \Helper::getUserName($notification->from,\Sentry::getUser()) }}</span>
                    <abbr title="{{date("Y/m/d H:i",strtotime($notification->created_at))}}" data-livestamp="{{strtotime($notification->created_at)}}"></abbr>
                    <span class="subject"><i class="fa {{ $notification->notifiable->getIcon() }}"></i> {{ $notification->notifiable->getReadableName() }}</span>
                    <span class="msg-body">{{ $notification->msg }}</span>
                </a>
        </span>
</li>