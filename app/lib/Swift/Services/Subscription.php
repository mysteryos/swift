<?php

Namespace Swift\Services;
Use SwiftSubscription;
Use Sentry;

class Subscription {

    private $modelData;

    /*
     * @param: $obj
     * Subscribes current user from object
     */
    public function subscribe($obj)
    {
        if($this->modelling($obj))
        {
            $sub = SwiftSubscription::firstOrNew($this->modelData);
            $sub->status = \SwiftSubscription::ACTIVE;
            $sub->save();
            return true;
        }
        return false;
    }

    /*
     * @param: $obj
     * Unsubscribes current user from object
     */
    public function unsubscribe($obj)
    {
        if($this->modelling($obj))
        {
            \SwiftSubscription::where($this->modelData)->update([
                'status' => \SwiftSubscription::INACTIVE
            ]);

            return true;
        }
            
        return false;
    }

    public function toggleSubscribe($obj)
    {
        if($this->modelling($obj))
        {
            $sub = SwiftSubscription::where($this->modelData)->first();
            if($sub)
            {
                $sub->status = 1 - (int)$sub->status;
            }
            else
            {
                $sub = new SwiftSubscription($this->modelData);
                $sub->status = SwiftSubscription::ACTIVE;
            }

            $sub->save();

            return $sub->status;
        }

        return false;
    }

    /*
     * @param: $obj
     * Checks if $obj has subscription
     */
    public function has($obj)
    {
        if($this->modelling($obj))
        {
            $sub = \SwiftSubscription::where($this->modelData)->where('status','=',\SwiftSubscription::ACTIVE,'AND')->first();
            if($sub)
            {
                return true;
            }
        }
        
        return false;
    }


    /*
     * @param: $obj
     * Creates the model data for the object
     */
    private function modelling($obj)
    {
        if(!Sentry::check() || (Sentry::check() && Sentry::getUser()->email === \Config::get('website.system_mail')))
        {
            return false;
        }

        $this->modelData = ['user_id' => \Sentry::getUser()->id];

        switch(get_class($obj))
        {
            case "SwiftWorkflowActivity":
                $this->modelData['subscriptionable_type'] = $obj->workflowable_type;
                $this->modelData['subscriptionable_id'] = $obj->workflowable_id;
                break;
            default:
                $this->modelData['subscriptionable_type'] = get_class($obj);
                $this->modelData['subscriptionable_id'] = $obj->getKey();
                break;
        }

        return true;
    }
}
