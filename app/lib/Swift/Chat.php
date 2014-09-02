<?php namespace Application\Chat;

use App;
use Config;

use Application\Models\ChatRoom;
use Application\Models\ChatRoomUser;
use Application\Models\ChatRoomMessage;
use Application\Models\File;

class Client {

    /**
     * Constructor & Destructor
     */

    public function __construct($connection = null)
    {
        if($connection)
        {
            $this->setConnection($connection);
        }
    }

    public function __destruct()
    {
        if($this->hasRoom())
        {
            $this->takenUserOfflineForRoomId($this->getRoom()->id);
        }
    }

    /**
     * Connection
     */

    protected $_connection = null;

    public function getConnection()
    {
        return $this->_connection;
    }

    public function setConnection($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Session
     */

    public function setSession($input)
    {
        Config::set('session.driver', 'database');

        $session_id = $input; 

        $session = App::make('session');
        $session->setDefaultDriver(Config::get('session.driver'));

        $session->driver()->setId($session_id);
        $session->driver()->start();

        $cartalyst_session = $session->driver()->get(
            Config::get('cartalyst/sentry::cookie.key')
        );

        if(!empty($cartalyst_session))
        {
            $this->setUserId($cartalyst_session[0]);
        }
        else
        {
            throw new \Exception('User not recognized.');
        }
    }

    /**
     * User id
     */

    private $_user_id = null;

    private function setUserId($id)
    {
        $this->_user_id = $id;
    }

    public function getUserId()
    {
        return $this->_user_id;
    }

    /**
     * Room
     */

    private $_room = null;

    public function getRoom()
    {
        return $this->_room;
    }

    public function setRoom($input)
    {
        if(empty($input) || empty($input['id']))
        {
            throw new \Exception('Invalid chat room.');
        }

        $this->_room = ChatRoom::find($input['id']);

        $this->takeUserOnlineForRoomId($this->getRoom()->id);
    }

    public function hasRoom()
    {
        if($this->_room)
        {
            return true;
        }

        return false;
    }

    /**
     * User room status
     */

    public function takeUserOnlineForRoomId($room_id)
    {
        $chat_room_user = ChatRoomUser::where('chat_room_id', '=', $room_id)
                                      ->where('user_id', '=', $this->getUserId())
                                      ->first();

        if($chat_room_user)
        {
            $chat_room_user->status = ChatRoomUser::STATUS_ONLINE;
            $chat_room_user->save();
        }
    }

    public function takenUserOfflineForRoomId($room_id)
    {
        $chat_room_user = ChatRoomUser::where('chat_room_id', '=', $room_id)
                                      ->where('user_id', '=', $this->getUserId())
                                      ->first();

        if($chat_room_user)
        {
            $chat_room_user->status = ChatRoomUser::STATUS_OFFLINE;
            $chat_room_user->save();
        }
    }

    /**
     * Message
     */

    public function message($input)
    {
        $message = new ChatRoomMessage();
        $message->user_id = $this->getUserId();
        $message->status  = ChatRoomMessage::STATUS_NEW;
        $message->content = $input['content'];

        $chat_room = $this->getRoom();
        $chat_room->messages()->save($message);

        $this->_attachInputFile($input, $message);

        $message->load('user', 'user.profile', 'user.profile.picture');

        return $message;
    }

    private function _attachInputFile($input, $message)
    {
        if(empty($input['file']) || empty($input['file']['id'])) return;

        $file = File::where('user_id', '=', $this->getUserId())
                    ->where('id', '=', $input['file']['id'])
                    ->first();

        if(!$file) return;

        $message->file()->save($file);
        $message->load('file');
    }

}