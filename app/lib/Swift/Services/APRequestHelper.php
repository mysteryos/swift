<?php

Namespace Swift\Services;

Use SwiftApproval;
Use SwiftComment;

class APRequestHelper{
    /*
     * Auto approves for exec if value exceeds rs 5000
     * For use with queues only
     */
    
    //limit for auto approval for exec
    private $execApprovalLimit = 5000;

    /*
     * No longer in use
     */
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
                    \WorkflowActivity::update($form);
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

    public function autoExecApprovalBasedOnCatMan($apr)
    {
        if(!\Helper::loginSysUser())
        {
            \Log::error('Unable to login system user');
            return;
        }

        if($apr)
        {
            $apr->load(['product','product.approvalexec','product.approvalcatman']);
            //If we have products
            if(count($apr->product))
            {
                //Do a total count
                $total = 0;
                $noexecyet = true;
                //Do a sanity check first
                //Total must not be more than limit
                //Price mmust be present
                foreach($apr->product as $p)
                {
                    if((int)$p->quantity > 0 && (float)$p->price > 0)
                    {
                        $total += $p->quantity * $p->price;
                    }
                    //Price not present, we cannot auto approve
                    if((int)$p->quantity > 0 && (int)$p->price === 0)
                    {
                        $hasComment = (boolean)\SwiftComment::where('commentable_type','=',get_class($apr))
                                        ->where('commentable_id','=',$apr->id,'AND')
                                        ->where('user_id','=',\Sentry::getUser()->id,'AND')
                                        ->count();
                        if(!$hasComment)
                        {
                            $comment = new SwiftComment([
                                'comment' => "Unable to auto approve form - Price is missing for certain products",
                                'user_id' => \Sentry::getUser()->id,
                            ]);
                            $apr->comments()->save($comment);
                        }
                        return;
                    }
                }

                /*
                 * Total of products is less than limit
                 */
                if($total < $this->execApprovalLimit)
                {
                    foreach($apr->product as $p)
                    {
                        /*
                         * Only products that already have approval of cat man
                         */
                        if(count($p->approvalcatman))
                        {
                            /*
                             * Add Exec Approval - by replicating that of cat man
                             */
                            $approval = new \SwiftApproval(array('type'=>(int)\SwiftApproval::APR_EXEC,
                                                                'approval_user_id'=>\Sentry::getUser()->id,
                                                                'approved' => $p->approvalcatman->approved));
                            /*
                             * Add comment
                             */
                            if($p->approval()->save($approval))
                            {
                                $newcomment = new SwiftComment(['comment'=>"Completed by system",'user_id'=>\Sentry::getUser()->id]);
                                $approval->comments()->save($newcomment);
                            }
                        }
                    }
                }
            }
        }
    }
}
