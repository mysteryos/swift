<?php

namespace Process;

class SwiftACPRequest extends Process
{
    protected $resourceName = "SwiftACPRequest";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function publishOwner($acp)
    {
        //Is owner or Is admin
        if($acp->owner_user_id === $this->controller->currentUser->id || $this->controller->permission->isAdmin() || $acp->isSharedWith($this->controller->currentUser->id,\SwiftShare::PERMISSION_EDIT_PUBLISH))
        {
            $workflow_progress = \WorkflowActivity::progress($acp);
            if($workflow_progress['status'] === \SwiftWorkflowActivity::INPROGRESS)
            {
                if(empty($workflow_progress['definition_obj']))
                {
                    \WorkflowActivity::update($acp);
                }

                foreach($workflow_progress['definition_obj'] as $def)
                {
                    if(isset($def->data->publishOwner))
                    {
                        if(count($acp->approvalRequester))
                        {
                            \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->controller->currentUser->id));
                            return \Response::make('Form already Published',400);
                        }
                        else
                        {
                            $returnReasonList = array();

                            /*
                             * Approvals
                             */
                            if(count($acp->approvalHod) === 0)
                            {
                                $returnReasonList['hodapproval_absent'] = "Enter HOD's details for approval";
                            }

                            /*
                             * Documents
                             */

                            if(count($acp->document) === 0)
                            {
                                $returnReasonList['document_absent'] = "Upload invoice document";
                            }

                            if(count($returnReasonList) !== 0)
                            {
                                return Response::make(implode(", ",$returnReasonList),400);
                            }

                            /*
                             * All great we proceed on
                             */

                            $approval = new \SwiftApproval([
                                'approval_user_id' => $this->controller->currentUser->id,
                                'approved' => \SwiftApproval::APPROVED,
                                'type'=> \SwiftApproval::APC_REQUESTER
                            ]);

                            $acp->approval()->save($approval);
                            \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->controller->currentUser->id));
                            return \Response::make('Success');
                        }
                        break;
                    }
                }

                return \Response::make("You can't publish the form at this time.");
            }
            else
            {
                return \Response::make('Workflow is either complete or rejected.');
            }
        }
        else
        {
            return parent::forbidden();
        }
    }
}