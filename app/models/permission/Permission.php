<?php

namespace Permission;

abstract class Permission {
    protected $currentUser;

    protected $form;

    public function __construct($form,$user_id)
    {
        if($user_id === false)
        {
            //Get currently logged in user
            $this->currentUser = \Sentry::getUser();
        }
        else
        {
            //Fetch user
            $this->currentUser = \Sentry::findUserById($user_id);
        }

        $this->form = $form;
    }

    public function checkAndShare($from_user_id,$userArray)
    {
        if(!is_array($userArray))
        {
            $userArray[] = $userArray;
        }

        foreach($userArray as $user_id)
        {
            $this->currentUser = \Sentry::findUserById($user_id);
            if($this->currentUser && $this->currentUser->isActivated() && !$this->checkAccess())
            {
                $share = new \SwiftShare([
                    'from_user_id' => $from_user_id,
                    'to_user_id' => $this->currentUser->id,
                    'permission' => \SwiftShare::PERMISSION_VIEW
                ]);

                $this->form->share()->save($share);
            }
        }
    }
}