<?php

/* 
 * Name: Permissions
 * Description: Handle logic permissions for sentry2
 */

namespace Swift;

use Sentry;

Class Permissions {
    public static function setPath($controllerpath)
    {
        $groups = Sentry::findAllGroups();
        if($groups)
        {
            //Loop through all groups and add permission
            foreach($groups as &$g)
            {
                $g->permissions = array($controllerpath=>-1);
                $g->save();
            }
            //TBD: Notify admin of new added permission
        }        
    }
}

