<?php

namespace Permission;

abstract class Permission {
    protected $currentUser;

    protected $resource;

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

        $this->resource = $form;
    }
}