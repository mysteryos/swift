<?php

Namespace Swift\Services;

Use SwiftApproval;
Use SwiftComment;
Use WorkflowActivity;

class APRequestHelper{
    /*
     * Auto approves for exec if value exceeds rs 5000
     * For use with queues only
     */
    
    //limit for auto approval for exec
    private $execApprovalLimit = 5000;
    
    public function autoexecapproval($job,$data)
    {
        if(!\Helper::loginSysUser())
        {
            \Log::error('Unable to login system user');
            return;
        }
        
        if(isset($data['aprequest_id']))
        {
            $form = \SwiftAPRequest::getById($data['aprequest_id']);
            if($form && count($form->product))
            {
                /*
                 * Do a count
                 */
                $total = 0;
                $noexecyet = true;
                foreach($form->product as $p)
                {
                    if((int)$p->quantity > 0 && (float)$p->price > 0)
                    {
                        $total += $p->quantity * $p->price;
                    }
                    //Price not present, we cannot auto approve
                    if((int)$p->quantity > 0 && (int)$p->price === 0)
                    {
                        $comment = new SwiftComment([
				'comment' => "Unable to auto approve form - Price is missing for certain products",
				'user_id' => \Sentry::getUser()->id,                            
                        ]);
			$form->comments()->save($comment);
                        $job->delete();
                        return;
                    }
                    /*
                     * check if any of those products has been approved yet.
                     */
                    if(count($p->approvalexec))
                    {
                        $noexecyet = false;
                    }
                }
                /*
                 * We are in the clear
                 */
                if($total < $this->execApprovalLimit && $noexecyet)
                {
                    foreach($form->product as $p)
                    {
                        /*
                         * Add Exec Approval
                         */
                        $approval = new SwiftApproval(array('type'=>(int)SwiftApproval::APR_EXEC,
                                                            'approval_user_id'=>\Sentry::getUser()->id,
                                                            'approved' => SwiftApproval::APPROVED));
                        /*
                         * Add comment
                         */
                        if($p->approval()->save($approval))
                        {
                            $newcomment = new SwiftComment(['comment'=>"Auto approved by system",'user_id'=>\Sentry::getUser()->id]);
                            $approval->comments()->save($newcomment);
                        }
                    }
                    WorkflowActivity::update($form);
                }
                else
                {
                    $job->delete();
                    return;
                }
            }
        }
        else
        {
            \Log::error("APRequestHelper: No Ap Request ID is set");
            $job->delete();
        }
    }
}
