<span id="activity" class="activity-dropdown">
    <i class="fa fa-user"></i>
    <b class="badge @if($notification_unread_count > 0) {{ "bg-color-red" }} @endif" id="activity-badge">{{ $notification_unread_count }}</b>
</span>

<!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
<div class="ajax-dropdown">
        <!-- notification content -->
        <div class="ajax-notifications custom-scroll">
            <ul class="notification-body" id="notification-list">
                @if(count($notifications))
                    @foreach($notifications as $n)
                        @include('notification.single',array('notification'=>$n))
                    @endforeach
                @endif
            </ul>
        </div>
        <!-- end notification content -->
</div>
<!-- END AJAX-DROPDOWN -->