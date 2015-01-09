<?php

/*
 * This allows us to override the persist code which prevents users from logging in from two different computers simultaneouly.
 * Use: Allows Laravel Queues to impersonate Users during execution of tasks
 */

Namespace Swift;

use \Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser
{

    // Override the SentryUser getPersistCode method.

    public function getPersistCode()
    {
        if (!$this->persist_code)
        {
            $this->persist_code = $this->getRandomString();

            // Our code got hashed
            $persistCode = $this->persist_code;

            $this->save();

            return $persistCode;            
        }
        return $this->persist_code;
    }
    
    public function checkPersistCode($persistCode)
    {
        return true;
    }
}