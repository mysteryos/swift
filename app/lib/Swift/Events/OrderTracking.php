<?php

Namespace \Swift\Events;

class OrderTracking extends BaseEvent {
    
    public function onNodeActivityComplete($node_activity_id,$user_id) {
        
    }
    
    public function onWorkflowActivityComplete($workflow_activity_id) {
        
    }
    
    public function onWorkflowActivityCancel($workflow_activity_id) {
        
    }    
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen('order_tracking.node_activity_complete', '\Swift\Events\OrderTracking@onNodeActivityComplete');

        $events->listen('order_tracking.workflow_activity_complete', '\Swift\Events\OrderTracking@onWorkflowActivityComplete');        
        
        $events->listen('order_tracking.workflow_activity_cancel', '\Swift\Events\OrderTracking@onWorkflowActivityCancel');        
    }
}