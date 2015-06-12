<?php
/*
 * Name: Channel
 * Description: Handles all channel interactions with Pusher
 */
namespace Channel;

abstract class Channel
{
    protected $name;

    protected $provider;

    protected $resource;

    public function __construct($user=false)
    {
        $this->provider = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));

        if($user === false)
        {
            $this->user = \Sentry::getUser();
        }
        else
        {
            $this->user = $user;
        }
    }

    /*
     * Triggers Presence Channel
     *
     * @param string $name
     * @param array $data
     *
     * @return boolean
     */
    public function triggerPresence($name,array $data)
    {
        if(\Config::get('pusher.enabled'))
        {
            try
            {
                $this->provider->trigger('presence-'.$this->name, $name, $data);
                return true;
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
            }
        }
        
        return false;
    }

    /*
     * Triggers User's private channel
     *
     * @param string $name
     * @param array $data
     *
     * @return boolean
     */
    public function triggerUser($name,$data)
    {
        if(\Config::get('pusher.enabled'))
        {
            try
            {
                if($this->user)
                {
                    $this->provider->trigger('private-user-'.$this->user->id, $name, $data);
                    return true;
                }
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
            }
        }

        return false;
    }

    /*
     * Get list of responsible activated users for the node activity
     *
     * @param \SwiftNodeActivity $nodeActivity
     *
     * @return array
     */
    public function getNodeResponsibleUsers(\SwiftNodeActivity $nodeActivity)
    {
        $nodeActivity->load(['permission'=>function($q){
            return $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE);
        }]);
        if(count($nodeActivity->permission))
        {
            $permissions = $nodeActivity->permission->toArray();
            $permissionsArray = array();
            array_walk($permissions,function($v,$k) use (&$permissionsArray){
                $permissionsArray[] = $v['permission_name'];
            });

            $users = \Sentry::findAllUsersWithAccess($permissionsArray);
            foreach($users as $i => $u)
            {
               if($u->isSuperUser() || !$u->activated)
               {
                   unset($users[$i]);
               }
            }

            $userArray = [];

            array_walk($users, function($v) use (&$userArray){
                $userArray[] = $v->id;
            });

            return $userArray;
        }

        return array();
    }
}

