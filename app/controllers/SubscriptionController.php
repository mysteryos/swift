<?php

class SubscriptionController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "My Subscriptions";
    }
    
    public function putSubscribe($context_type,$context_id)
    {
        if(array_key_exists($context_type,\Config::get('context')))
        {
            $class = \Config::get('context')[$context_type];
            $id = Crypt::decrypt($context_id);
            //Check if object exists
            if(!count($class::find($id)))
            {
                return Response::make("Subscription failed: No Context Object",500);
            }
            
            //Check if exists
            $subscription = SwiftSubscription::getByClassAndUser($class,$id,$this->currentUser->id);
            if(count($subscription))
            {
                $subscription->status = SwiftSubscription::ACTIVE;
                if($subscription->save())
                {
                    return Response::make("success");
                }
                else 
                {
                    return Response::make("Subscription failed to update",500);
                }
            }
            else
            {
                $subscription = (new SwiftSubscription([
                                    'user_id' => $this->currentUser->id,
                                    'subscriptionable_type' => $class,
                                    'subscriptionable_id' => $id,
                                    'status' => SwiftSubscription::ACTIVE
                                ]))->save();
                
                if($subscription)
                {
                    return Response::make("success");
                }
                else 
                {
                    return Response::make("Subscription failed to save",500);
                }
            }
            
        }
        else
        {
            return Response::make('Context is unknown',500);
        }
    }
    
    public function deleteSubscribe($context_type,$context_id)
    {
        
    }    
}