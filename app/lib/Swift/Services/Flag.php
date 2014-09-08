<?php

Namespace Swift\Services;

/*
 * Name: Flag
 * Description: Useful functions for use with flags
 */

Use SwiftFlag;
Use Sentry;

class Flag {
    
    public function isImportant($obj)
    {
        $obj->load('flag');
        if(count($obj->flag))
        {
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::IMPORTANT)
                {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public function isStarred($obj,$user=false)
    {
        $obj->load('flag');
        if(count($obj->flag))
        {
            if($user === false)
            {
                $user = Sentry::getUser();
            }
            
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::STARRED && $f->user_id == $user->id)
                {
                    return (bool) $f->active;
                }
            }
        }

        return false;
    }
    
    public function isRead($obj,$user=false)
    {
        $obj->load('flag');
        if(count($obj->flag))
        {
            if($user === false)
            {
                $user = Sentry::getUser();
            }
            
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::READ && $f->user_id == $user->id)
                {
                    return (bool)$f->active;
                }
            }
        }

        return false;        
    }
    
    public function toggleStarred($obj,$user=false)
    {
        if($user === false)
        {
            $user = Sentry::getUser();
        }
        
        $obj->load('flag');
        if(count($obj->flag))
        {
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::STARRED && $f->user_id == $user->id)
                {
                    $f->active = !$f->active;
                    if($f->save())
                    {
                        return true;
                    }
                    else
                    {
                        throw new \Exception('Failed to save starred flag');
                    }
                }
            }            
        }
        
        $flag = new SwiftFlag([
           'user_id' => $user->id,
           'type'    => SwiftFlag::STARRED,
           'active'  => SwiftFlag::ACTIVE
        ]);
        
        $obj->flag()->save($flag);
        return true;
    }
    
    public function toggleImportant($obj,$user=false)
    {
        if($user === false)
        {
            $user = Sentry::getUser();
        }
        
        $obj->load('flag');
        if(count($obj->flag))
        {
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::IMPORTANT)
                {
                    if($f->delete())
                    {
                        return true;
                    }
                    else
                    {
                        throw new \Exception('Failed to delete important flag');
                    }
                }
            }
        }
        
        $flag = new SwiftFlag([
           'user_id' => $user->id,
           'type'    => SwiftFlag::IMPORTANT,
           'active'  => SwiftFlag::ACTIVE
        ]);
        
        $obj->flag()->save($flag);
        return true;        
    }
    
    public function toggleRead($obj,$user=false)
    {
        if($user === false)
        {
            $user = Sentry::getUser();
        }
        
        $obj->load('flag');
        if(count($obj->flag))
        {
            foreach($obj->flag as $f)
            {
                if($f->type == SwiftFlag::READ  && $f->user_id == $user->id)
                {
                    $f->active = !$f->active;
                    if($f->save())
                    {
                        return true;
                    }
                    else
                    {
                        throw new \Exception('Failed to delete important flag');
                    }
                }
            }
        }        
        
        /*
         * No Read records found, set one for this user
         */
        $flag = new SwiftFlag([
           'user_id' => $user->id,
           'type'    => SwiftFlag::READ,
           'active'  => SwiftFlag::ACTIVE
        ]);
        
        $obj->flag()->save($flag);
        return true;
    }
    
}