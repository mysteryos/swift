<?php

/* 
 * Name: Login Controller
 * Usage: Control all login logic
 */

Class LoginController extends Controller
{
    public function getIndex()
    {
        if(\Sentry::check())
        {
            //User is logged in; What are you doing here?
            \Session::forget('redirect_after_login');
            return \Redirect::to('/dashboard');
        }
        else
        {
            //Time to get our hands dirty with Oauth
            $code = \Input::get( 'code' );

            // get google service
            $goog = \Artdarek\OAuth\Facade\OAuth::consumer( 'Google' );
            if ( !empty( $code ) ) {

                // This was a callback request from google, get the token
                $goog->requestAccessToken( $code );

                // Send a request with it for basic info
                $result = json_decode( $goog->request( 'https://www.googleapis.com/oauth2/v1/userinfo' ), true );

                //Sentry, is that user registered already? Log him in.
                try
                {
                    $user = \Sentry::findUserByLogin($result['email']);
                    \Sentry::login($user, false);
                    //Gotcha, User Logged In - Sentry
                    
                }
                catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
                {
                    //Looks like we got a newcomer Sir - Sentry
                    //Create it, Ms Sentry
                    
                    //But first, is the domain of the google account allowed?
                    list($user, $domain) = explode('@', $result['email']);
                    if(in_array($domain,\Config::get('website.google_allowed_domains')))
                    {
                        \Sentry::register(array(
                            'email' => $result['email'],
                            'password' => "oauth".rand('10000','9999999999'),
                            'first_name' => $result['given_name'],
                            'last_name' => $result['family_name'],
                            'username'  => $result['given_name'][0].$result['family_name']
                        ),\Config::get('website.sentry_autoactivate',false));
                            
                        //Freshly registered users are not activated by default or is it...
                        if(!\Config::get('website.sentry_autoactivate'))
                        {
                            \Session::flash('login-email', $result['email']);
                            return self::getNotactivated();
                        }
                        else
                        {
                            //Sentry doesn't return user object on register, let's search for the obvious and login
                            $user = \Sentry::findUserByLogin($result['email']);
                            \Sentry::login($user, true);
                        }
                    }
                    else
                    {
                        \Session::flash('login-email', $result['email']);
                        return self::getDomainnotallowed();
                    }
                }
                catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e)
                {
                    //He's a somebody Sir, but not activated - Sentry
                    //On board to the 'not activated' page
                    \Session::flash('login-email', $result['email']);
                    return self::getNotactivated();
                }
                //Assign Avatar To User
                \Swift\Avatar::set();
                
                //To the dashboard, matey
                if(\Session::has('redirect_after_login') && strpos(\Session::get('redirect_after_login'),"pusher") === false)
                {
                    $temp = \Session::get('redirect_after_login');
                    \Session::forget('redirect_after_login');
                    return \Redirect::to($temp);
                }
                else
                {
                    return \Redirect::to('/dashboard');
                }

            }
            // if not ask for permission first
            else {
                // get googleService authorization
                $url = $goog->getAuthorizationUri();
                $this->data['googleAuthUrl'] = $url;
                
                return \View::make('login',$this->data);
            }
            
        }
    }
    
    public function getLogout()
    {
        if(\Sentry::check())
        {
            //Forget Avatar of User
            \Swift\Avatar::forget();
            //Remove Session
            \Sentry::logout();
        }
        //To the login page, matey
        return \Redirect::to('/login');
    }
    
    private function getNotactivated()
    {
        // get google service
        $goog = \Artdarek\OAuth\Facade\OAuth::consumer( 'Google' );
        $url = $goog->getAuthorizationUri();
        $this->data['googleAuthUrl'] = $url;
        $this->data['msgalert'] = array('status'=>2,'msg'=>"Your account ".(\Session::has('login-email') ? \Session::get('login-email')." " : "")."has not been activated yet. Please contact our administrator on ".\Config::get('website.webmaster_mail')) ;
        
        return \View::make('login',$this->data);
    }
    
    private function getDomainnotallowed()
    {
        // get google service
        $goog = \Artdarek\OAuth\Facade\OAuth::consumer( 'Google' );
        $url = $goog->getAuthorizationUri();
        $this->data['googleAuthUrl'] = $url;
        $this->data['msgalert'] = array('status'=>1,'msg'=>"Your email address ".(\Session::has('login-email') ? \Session::get('login-email')." " : "")."is not allowed on our system. If you believe this is an error, please contact our administrator on ".\Config::get('website.webmaster_mail')) ;
        
        return \View::make('login',$this->data);
    }    
}
