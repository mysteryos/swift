<?php
/*
 * Name: Workflow Activity
 * Description:
 */

namespace Process;

class SwiftWorkflowActivity extends process
{

    protected $resourceName = "SwiftWorkflowActivity";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }
    
    public function cancel($parentResourceName, $parentResourceId, \Closure $callback = null)
    {
        $this->form = (new $parentResourceName)->find($parentResourceId);

        if($this->form)
        {
            //Call Event
            if($this->onCancel($callback))
            {
                if(\WorkflowActivity::cancel($this->form))
                {
                    return Response::make('Workflow has been cancelled',200);
                }
            }
        }

        return Response::make('Unable to cancel workflow',400);
    }
}