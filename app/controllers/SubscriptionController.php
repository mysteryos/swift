<?php

class SubscriptionController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "My Subscriptions";
    }
    
    public function putToggleSubscribe($context_type,$context_id)
    {
        if(array_key_exists($context_type,\Config::get('context')))
        {
            $class = \Config::get('context')[$context_type];

            //check permission
            $viewPerm = \Config::get("permission.$context_type.view");
            $adminPerm = \Config::get("permission.$context_type.admin");
            $editPerm = \Config::get("permission.$context_type.edit");

            if(!$this->currentUser->hasAnyAccess([$viewPerm,$adminPerm,$editPerm]))
            {
                return \Response::make("Subscription failed: You don't have permission for this action");
            }
            
            //Check if object exists
            $obj = $class::find($context_id);
            if(!$obj)
            {
                return \Response::make("Subscription failed: No Context",500);
            }

            $subscriptionResult = \Subscription::toggleSubscribe($obj);
            if($subscriptionResult !== false)
            {
                return \Response::json(['result'=>$subscriptionResult]);
            }
            else
            {
                return \Response::make("Subscription failed: Please try again",500);
            }
            
        }
        else
        {
            return \Response::make('Subscription failed: Unknown Context',500);
        }
    }
    
//    public function deleteSubscribe($context_type,$context_id)
//    {
//        if(array_key_exists($context_type,\Config::get('context')))
//        {
//            $class = \Config::get('context')[$context_type];
//            $id = \Crypt::decrypt($context_id);
//
//            //check permission
//            $viewPerm = \Config::get("permission.$context_type.view");
//            $adminPerm = \Config::get("permission.$context_type.admin");
//            $editPerm = \Config::get("permission.$context_type.edit");
//
//            if(!$this->currentUser->hasAnyAccess([$viewPerm,$adminPerm,$editPerm]))
//            {
//                return \Response::make("Subscription failed: You don't have permission for this action");
//            }
//
//            //Check if object exists
//            $obj = $class::find($id);
//            if(!$obj)
//            {
//                return \Response::make("Subscription failed: No Context",500);
//            }
//
//            if(\Subscription::unsubscribe($obj))
//            {
//                return \Response::make("Subscription Successful");
//            }
//            else
//            {
//                return \Response::make("Subscription failed: Please try again",500);
//            }
//
//        }
//        else
//        {
//            return \Response::make('Subscription failed: Unknown Context',500);
//        }
//    }
}