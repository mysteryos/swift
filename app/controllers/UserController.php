<?php

class UserController extends Controller {

    public $pageName;
    public $pageTitle;

    public $data = array();
        
	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
        
    public function __construct()
    {
        //Register Base JS/CSS files
        if (!Request::header('X-PJAX'))
        {
            $this->data['js'] = \Config::get('assets.js');
            $this->data['css'] = \Config::get('assets.css');

            /*
             * Cache Buster
             */
            array_walk($this->data['js'],function(&$v){
                if(strpos($v,'/js/') === 0)
                {
                    $v = \Bust::url($v);
                }
            });

            array_walk($this->data['css'],function(&$v){
                if(strpos($v,'/css/') === 0)
                {
                    $v = \Bust::url($v);
                }
            });
        }
        else
        {
            //If PJAX request, no need to load base JS libraries
            $this->data['js'] = array();
            $this->data['css'] = array();
        }

        $this->currentUser = \Sentry::getUser();
        $this->data['currentUser'] = $this->currentUser;

        //Check if current User is system. If it is, boot him!!
        if($this->currentUser === false)
        {
            $goog = \Artdarek\OAuth\Facade\OAuth::consumer( 'Google' );
            $url = $goog->getAuthorizationUri();

            return \View::make('login',['googleAuthUrl'=>$url]);
        }
    }

    /*
     * Combine Assets and Sets Data
     */
    public function makeView($view,$data=false)
    {
        if($data === false)
        {
            $data = $this->data;
        }

        $menu = new \Swift\Menu();
        $data['sidemenu'] =  $menu->generateHTML();
        if (\Request::header('X-PJAX')) {
            $data['before_js'] = $data['js'];
            $data['assets'] = "";
        }
        else
        {
            $data['assets'] = "\"".implode('", "', array_merge($data['css'],$data['js']))."\"";
        }
        if(isset($data['pageTitle']))
        {
            $data['pageTitle'] = $data['pageTitle']." - ".$this->pageName;
        }
        else
        {
            $data['pageTitle'] = ($this->pageTitle != "" ? $this->pageTitle." - " : "").$this->pageName;
        }
        $data['notifications'] = \SwiftNotification::getByUser($this->currentUser->id,10);
        $data['notification_unread_count'] = \SwiftNotification::getUnreadCountByUser($this->currentUser->id);
        return \View::make($view,$data);
    }

    /*
     * Add JS to Assets
     */
    public function addJs($js)
    {
        $this->data['js'] = array_merge($this->data['js'], $js);
    }

    /*
     * Add CSS to Assets
     */
    public function addCss($css)
    {
        $this->data['css'] = array_merge($this->data['css'], $css);
    }

    public function forbidden()
    {
        if (!Request::ajax())
        {
            $this->pageTitle = "Forbidden";
            $view = self::makeView('general.forbidden');
            return Response::make($view,403);
        }
        else
        {
            return Response::make("You don't have access to this resource",403);
        }
    }

    public function notfound()
    {
        if (!Request::ajax())
        {
            $this->pageTitle = "404 not found";
            $view = self::makeView('general.notfound');
            return Response::make($view,404);
        }
        else
        {
            return Response::make("We can't find the resource that you were looking for.",404);
        }
    }

    public function enableComment($commentable)
    {
        $this->data['commentKey'] = Comment::makeKey($commentable);
        $this->data['comments'] = $commentable->comments()->orderBy('created_at','DESC')->get();
    }

    public function enableSubscription($subscriptionable)
    {
        $this->data['subscriptionUrl'] = "/subscription/toggle-subscribe/".array_search(get_class($subscriptionable),\Config::get('context'))."/".$subscriptionable->getKey();
        $this->data['isSubscribed'] = \Subscription::has($subscriptionable);
    }

    public function adminList()
    {
        $adminPermission = \Config::get("permission.$this->context.admin");
        if($adminPermission)
        {
            $adminUsers = \Sentry::findAllUsersWithAccess($adminPermission);
            foreach($adminUsers as $k=>$u)
            {
                if($u->isSuperUser())
                {
                    unset($adminUsers[$k]);
                }
            }
            if(count($adminUsers))
            {
                $adminList = [];
                foreach($adminUsers as $u)
                {
                    $adminList[] = $u->first_name." ".$u->last_name;
                }
                $this->data['admin_list'] = implode(",",$adminList);
            }
        }

        $this->data['admin_list'] = false;
    }

    public function process($baseClass=false)
    {
        if($baseClass === false)
        {
            $baseClass = \Config::get("context.$this->context");
        }

        $class = new $baseClass;
        if($class instanceof \Illuminate\Database\Eloquent\Model)
        {
            $processClassName = "\Process\\$baseClass";
            $processClass = new $processClassName($this);
            if($processClass instanceof \Process\Process)
            {
                return $processClass;
            }
            else
            {
                throw new \RuntimeException("Process Class must be an instance of \Process\Process");
            }
        }
        else
        {
            throw new \RuntimeException("Base class must be an instance of \Illuminate\Database\Eloquent\Model");
        }
    }

    public function task($baseClass=false)
    {
        if($baseClass === false)
        {
            $baseClass = \Config::get("context.$this->context");
        }

        $class = new $baseClass;
        if($class instanceof \Illuminate\Database\Eloquent\Model)
        {
            $taskClassName = "\Task\\$baseClass";
            $taskClass = new $taskClassName($this);
            if($taskClass instanceof \Task\Task)
            {
                return $taskClass;
            }
            else
            {
                throw new \RuntimeException("Task Class must be an instance of \Task\Task");
            }
        }
        else
        {
            throw new \RuntimeException("Base class must be an instance of \Illuminate\Database\Eloquent\Model");
        }
    }

}
