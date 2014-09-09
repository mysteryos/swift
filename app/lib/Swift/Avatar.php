<?php

/* 
 * Name: Avatars
 * Description: Handle all avatars
 */

Namespace Swift;

use Cache;
use Sentry;

Class Avatar {
    
    public static function set($user=false)
    {
        $avatars = Cache::get('avatars',array());
        
        if(!is_object($user))
        {
            $user = Sentry::getUser();
        }
        
        //User doesn't have an avatar
        if(!array_key_exists($user->email,$avatars))
        {
            //Assign Avatar
            $randcolor = rand(1,18);
            if(in_array($randcolor,$avatars))
            {
                //Someone somewhere has our color, A finer drill down by name is required.
                $firstletter = $user->email[0];
                $colorarray = array();
                foreach($avatars as $k=>$v)
                {
                    if($k[0] == $firstletter)
                    {
                        $colorarray[] = $v;
                    }
                }
                if(count($colorarray)<18)
                {
                    //Someone's getting a unique color today :)
                    While(in_array($randcolor,$colorarray))
                    {
                        $randcolor = rand(1,18);
                    }
                }
            }
            //Assign Color
            $avatars[$user->email] = $randcolor;
            Cache::forever('avatars',$avatars);
        }
        
        return $avatars[$user->email];
    }
    
    public static function forget()
    {
        $avatars = (array)Cache::get('avatars');
        unset($avatars[Sentry::getUser()->email]);
        Cache::forever('avatars',$avatars);
    }
    
    public static function get($user=false)
    {
        if(is_object($user))
        {
            return array('letter'=>$user->email[0],'color'=>self::getColor($user));
        }
        else
        {
            //Get Current User Avatar
            return array('letter'=>self::getLetter(),'color'=>self::getColor());
        }

    }
    
    public static function getHTML($user=false,$tooltip=false)
    {
        if($user === false)
        {
            $user = \Sentry::getUser();
        }
        
        if(is_numeric($user))
        {
            $user = \Sentry::findUserById($user);
        }
        
        $avatar = self::get($user);
        if($tooltip)
        {
            $tooltip_html = "data-placement=\"bottom\" data-original-title=\"".(\Helper::getUserName($user->id,\Sentry::getUser()))."\" rel=\"tooltip\"";
        }
        $html = "<i class=\"avatar avatar-sm avatar-color-{$avatar['color']}\" ".($tooltip ? $tooltip_html : "").">{$avatar['letter']}</i>";
        return $html;
    }
    
    public static function getLetter()
    {
        $user = Sentry::getUser();
        return $user->email[0];
    }
    
    public static function getColor($user=false)
    {
        $avatarColor = self::set($user);
        return $avatarColor;
    }
}
