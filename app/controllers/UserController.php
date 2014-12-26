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
            if (!Request::header('X-PJAX')) {
                $this->data['js'] = Config::get('assets.js');
                $this->data['css'] = Config::get('assets.css');                
            }
            else
            {
                //If PJAX request, no need to load base JS libraries
                $this->data['js'] = array();
                $this->data['css'] = array();
            }

            $this->currentUser = Sentry::getUser();
            
        }
        
        /*
         * Combine Assets and Sets Data
         */
        public function makeView($view)
        {
            $menu = new Swift\Menu();
            $this->data['sidemenu'] =  $menu->generateHTML();
            if (Request::header('X-PJAX')) {
                $this->data['before_js'] = $this->data['js'];
            }
            else
            {
                $this->data['assets'] = "\"".implode('", "', array_merge($this->data['css'],$this->data['js']))."\"";
            }
            $this->data['pageTitle'] = ($this->pageTitle != "" ? $this->pageTitle." - " : "").$this->pageName;
            $this->data['notifications'] = SwiftNotification::getByUser($this->currentUser->id,10);
//            dd($this->data['notifications']);
            $this->data['notification_unread_count'] = SwiftNotification::getUnreadCountByUser($this->currentUser->id);
            return View::make($view,$this->data);
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
            $this->pageTitle = "Forbidden";
            $view = self::makeView('general.forbidden');
            return Response::make($view,403);
        }
        
        public function notfound()
        {
            $this->pageTitle = "404 not found";
            $view = self::makeView('general.notfound');
            return Response::make($view,404);
        }
        
        public function enableComment($commentable)
        {
            $this->data['commentKey'] = Comment::makeKey($commentable);
            $this->data['comments'] = $commentable->comments()->orderBy('created_at','DESC')->get();
        }

}
