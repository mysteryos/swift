@if(count($comments))
<ul>
    @foreach($comments as $c)
        <li class="message row" id="C{{ $c->id }}">
            <div class="col-sm-2 hidden-mobile">
                {{ \Swift\Avatar::getHTML($c->user_id,false,'large') }}                
            </div>
            <div class="comment-text col-sm-10 col-xs-12">
                    <time>{{ $c->getDate() }}</time>
                    <a href="javascript:void(0);" class="username">{{{ $c->user->first_name." ".$c->user->last_name }}}</a>
                    <span>{{ nl2br(htmlspecialchars($c->comment, null, 'UTF-8')) }}</span>
            </div>
        </li>
    @endforeach
</ul>
@endif