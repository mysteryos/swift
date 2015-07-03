<?php
/*
 * Name:
 * Description:
 */

NameSpace Swift\AccountsPayable;

class NodeMail {
    
    public static function sendMail($workflowActivity,$permissions)
    {
        if(count($workflowActivity))
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                $acp = $workflowActivity->workflowable;
                $acp->current_activity = \WorkflowActivity::progress($workflowActivity);
                if(isset($acp->current_activity['definition_name']) && in_array('acp_hodapproval',$acp->current_activity['definition_name']))
                {
                    //If HOD Approval Node
                    self::sendApprovalMail($workflowActivity, $permissions);
                    return;
                }

                //IfNormal Node
                $mailData = [
                        'name'=>$acp->name,
                        'id'=>$acp->id,
                        'current_activity'=>$acp->current_activity,
                        'url'=>\Helper::generateUrl($acp,true),
                        ];

                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        try
                        {
                            //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$acp,'user'=>$u))->render());
                            \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.acpayable.pending',array('form'=>$mailData,'user'=>$u),function($message) use ($u,$acp){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Status update on Accounts Payable "'.$acp->name.'" ID: '.$acp->id);
                                $message->to($u->email);
                            });
                        }
                        catch (Exception $e)
                        {
                            \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        }
                    }
                }
            }
        }
    }
    
    public static function sendCancelledMail($acp)
    {
        //Get owner of AP Request
        $owner_user = \Sentry::find($acp->requester_user_id);
        
        if($owner_user->activated)
        {
            \Mail::queueOn('sqs-mail','emails.acpayable.pending',array('form'=>$acp,'user'=>$owner_user),function($message) use ($owner_user,$acp){
                $message->from('swift@scott.mu','Scott Swift');
                $message->subject(\Config::get('website.name').' - Accounts Payable Cancelled"'.$acp->name.'" ID: '.$acp->id);
                $message->to($owner_user->email);
            });            
        }
    }

    public static function sendApprovalMail($workflowActivity,$permissions)
    {
        if(count($workflowActivity))
        {
            $acp = $workflowActivity->workflowable;
            $acp->load(['approvalHod'=>function($q){
                return $q->where('approved','=',\SwiftApproval::PENDING);
            },'approvalHod.approver']);
            

            if(count($acp->approvalHod))
            {
                $acp->current_activity = \WorkflowActivity::progress($workflowActivity);
                $mailData = [
                            'name'=>$acp->name,
                            'id'=>$acp->id,
                            'current_activity'=>$acp->current_activity,
                            'url'=>\Helper::generateUrl($acp,true),
                        ];

                foreach($acp->approvalHod as $approval)
                {
                    if($approval->approver->activated)
                    {
                        try
                        {
                            //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$acp,'user'=>$u))->render());
                            \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.acpayable.pending',array('form'=>$mailData,'user'=>$approval->approver),function($message) use ($approval,$acp){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Approval Pending on Accounts Payable "'.$acp->name.'" ID: '.$acp->id);
                                $message->to($approval->approver->email);
                            });
                        }
                        catch (Exception $e)
                        {
                            \Log::error(get_class().': Approval Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        }
                    }
                }
            }
        }
    }
}