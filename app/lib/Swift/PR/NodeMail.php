<?php
namespace Swift\PR;

/**
 * Description of NodeMail
 *
 * @author kpudaruth
 */
class NodeMail
{
    public static function sendMail($workflowActivity,$permissions)
    {
        if($workflowActivity)
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                $pr = $workflowActivity->workflowable;
                $pr->current_activity = \WorkflowActivity::progress($workflowActivity);

                //Custom emails where necessary
                if(isset($acp->current_activity['definition_name']))
                {
                    if(count(array_intersect(['pr_approval_others','pr_approval_key_account','pr_approval_hospitality','pr_approval_system','pr_approval_van'],
                        $acp->current_activity['definition_name'])) > 0)
                    {
                        self::sendApprovalMail($workflowActivity,$permissions);
                        return;
                    }
                }

                $mailData = [
                        'name'=>$pr->name,
                        'id'=>$pr->id,
                        'current_activity'=>$pr->current_activity,
                        'url'=>\Helper::generateUrl($pr,true),
                        ];

                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        try
                        {
                            \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.pr.pending',array('form'=>$mailData,'user'=>$u),function($message) use ($u,$pr){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Status update on Product Returns "'.$pr->name.'" #'.$pr->id);
                                $message->to($u->email);
                            });
                        }
                        catch (\Exception $e)
                        {
                            \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        }
                    }
                }
            }
        }
        return true;
    }

    public static function sendCancelledMail($pr)
    {
        //Get owner of AP Request
        $owner_user = \Sentry::find($pr->owner_user_id);

        if($owner_user && $owner_user->activated)
        {
            $pr->current_activity = \WorkflowActivity::progress($workflowActivity);
            $mailData = [
                    'name'=>$pr->name,
                    'id'=>$pr->id,
                    'current_activity'=>$pr->current_activity,
                    'url'=>\Helper::generateUrl($pr,true),
                    ];
            
            try
            {
                \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.pr.cancelled',array('pr'=>$mailData,'user'=>$owner_user),function($message) use ($owner_user,$pr){
                    $message->from('swift@scott.mu','Scott Swift');
                    $message->subject(\Config::get('website.name').' - Product Returns Cancelled - "'.$pr->customer_name.'" #'.$pr->id);
                    $message->to($owner_user->email);
                });
            }
            catch (\Exception $e)
            {
                \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
            }
        }
    }

    public static function sendApprovalMail($workflowActivity,$permissions)
    {
        if($workflowActivity)
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                $pr = $workflowActivity->workflowable;
                $pr->current_activity = \WorkflowActivity::progress($workflowActivity);
                $pr->load('product');
                $mailData = [
                        'name'=>$pr->name,
                        'id'=>$pr->id,
                        'current_activity'=>$pr->current_activity,
                        'url'=> \Url::action('ProductReturnsController@getTasks'),
                        'products' => $pr->product->toArray()
                        ];

                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        try
                        {
                            \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.pr.pending',array('pr'=>$mailData,'user'=>$u),function($message) use ($u,$pr){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Approval pending for Product Returns "'.$pr->name.'" #'.$pr->id);
                                $message->to($u->email);
                            });
                        }
                        catch (\Exception $e)
                        {
                            \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        }
                    }
                }
            }
        }
    }
}