<form action="/comment/create" method="POST" id="commentsContainer">
    <input type="hidden" name="commentable" id="commentable_key" value="{{ Crypt::encrypt($commentKey) }}"/>
    <!-- CHAT BODY -->
    <div id="chat-body" class="chat-body custom-scroll">
        @include('comments_list')
    </div>

    <!-- CHAT FOOTER -->
    <div class="chat-footer">

            <!-- CHAT TEXTAREA -->
            <div class="textarea-div">

                    <div class="typearea">
                        <div data-ph="Write a reply..." autocomplete="off" id="comment-textarea" name="comment" class="custom-scroll inputor" contenteditable="true"></div>
                    </div>

            </div>

            <!-- CHAT REPLY/SEND -->
            <span class="textarea-controls">
                    <p class="pull-left"><i>To tag your colleagues, type @, followed by their name</i></p>
                    <button class="btn btn-sm btn-primary pull-right" type="submit" id="comment-submit">
                            Reply
                    </button>
            </span>

    </div>

</form>