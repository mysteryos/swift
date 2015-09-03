<?php

class CommentController extends UserController {

	/**
	 * Saves a comment
	 *
	 * @throws Exception
	 * @return \Redirect
	 */
	public function postCreate()
	{
		try {
			list($commentableType, $commentableId) = Comment::getKey(Input::get('commentable'));
                        
			$data = array(
				'commentable_type' => $commentableType,
				'commentable_id' => $commentableId,
				'comment' => Input::get('comment'),
				'user_id' => $this->currentUser->id,
			);
            $comment = new SwiftComment;
			$comment->fill($data);
			$comment->save();
			$newCommentId = $comment->id;
            
            //alert users if they have been tagged
            if(trim(Input::get('usermention')) != "")
            {
                $userMentions = explode(',',\Input::get('usermention'));
                $userMentions = array_unique((array)$userMentions);
                
                //Give Access to form if needed
                $form = $commentableType::find($commentableId);
                $form->permission()->checkAndShare($this->currentUser->id,$userMentions);
                
                \Comment::mailNotify($comment,$userMentions);
            }
			return Response::make($newCommentId);

		} catch (\Exception $e) {

            return Response::Make($e->getMessage());
		}

	}
        
    public function getListcomment($commentable)
    {
        list($commentableType, $commentableId) = Comment::getKey($commentable);

        $classObj = new $commentableType;
        $classObj = $classObj::find($commentableId);

        return \View::make('comments_list',array('comments'=>$classObj->comments()->orderBy('created_at','DESC')->get(),'currentUser'=>$this->currentUser));

    }

    public function deleteEntry($id)
    {
        if($this->currentUser->isSuperUser())
        {
            $comment = \SwiftComment::find($id);
            if($comment)
            {
                if($comment->delete())
                {
                    return \Response::make("Success");
                }
            }

            return \Response::make("Operation failed",400);
        }

        return parent::forbidden();
    }

}