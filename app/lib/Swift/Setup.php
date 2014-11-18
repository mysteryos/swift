<?php

/* 
 * Name: Setup
 * Description: Initial Setup
 */

Namespace Swift;

use Sentry;
use Config;
use Cartalyst;

Class Setup {
    public static function runAll()
    {
        self::setAdmin();
    }
    
    /*
     * Check if webmaster account is present & give appropriate rights
     */
    public static function setAdmin()
    {
        try
        {
            // Create the group
            $adminGroup = Sentry::createGroup(array(
                'name'        => 'admin',
                'permissions' => array(
                    'superuser' => 1,
                ),
            ));
        }
        catch (Cartalyst\Sentry\Groups\GroupExistsException $e)
        {
            $adminGroup = Sentry::findGroupByName('admin');
        }
        
        //Add webmaster to Group
        $webmasterUser = Sentry::findUserByLogin(Config::get('website.webmaster_mail'));
        if($webmasterUser)
        {
            $webmasterUser->addGroup($adminGroup);
        }
    }
    
}
