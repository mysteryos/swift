<?php
/*
 * Name: Pusher Controller
 * Description: Solely for pusher endpoints
 */

class PusherController extends UserController {
    
    public function __construct() {
        parent::__construct();
        $this->pusher = new Pusher(Config::get('pusher.app_key'), Config::get('pusher.app_secret'), Config::get('pusher.app_id'));
    }
    /**
     * Authenticates logged-in user in the Pusher JS app
     * For presence channels
     */
    public function postAuth()
    {
        if(Sentry::check())
        {
            $user = Sentry::getUser();
            $avatar = Swift\Avatar::get();
            $presence_data = array('name' => $user->first_name." ".$user->last_name,'avatarColor'=>$avatar['color'],'avatarLetter'=>$avatar['letter']);
            echo $this->pusher->presence_auth(Input::get('channel_name'), Input::get('socket_id'), $user->id, $presence_data);       
        }
        else
        {
            return Response::make('Forbidden',403);
        }
    }
}

