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
				'user_id' => Sentry::getUser()->id,
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
                $this->checkAndGiveAccess($commentableType,$commentableId,$userMentions);
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

        return \View::make('comments_list',array('comments'=>$classObj->comments()->orderBy('created_at','DESC')->get()));

    }

    private function checkAndGiveAccess($commentableType,$commentableId,$userMentions)
    {
        if(is_array($userMentions))
        {
            switch($commentableType)
            {
                case "SwiftACPRequest":
                    $form = (new $commentableType)->find($commentableId);
                    if($form)
                    {
                        foreach($userMentions as $user_id)
                        {
                            if(!$form->permission($user_id)->checkAccess())
                            {
                                $share = new \SwiftShare([
                                    'from_user_id' => $this->currentUser->id,
                                    'to_user_id' => $user_id,
                                    'permission' => \SwiftShare::PERMISSION_VIEW
                                ]);

                                $form->share()->save($share);
                            }
                        }
                    }
                break;
            }
        }
    }

}