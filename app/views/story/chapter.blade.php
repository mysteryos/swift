<div class="smart-timeline">
    <ul class="smart-timeline-list" id="timeline-list">
        @if($dynamicStory !== false)
            @include('story.dynamic')
        @endif
        @if(count($stories))
            @foreach($stories as $story)
                @include('story.single')
            @endforeach
        @else
        <li class="text-center"><h2>No posts yet</h2></li>
        @endif
    </ul>
</div> 