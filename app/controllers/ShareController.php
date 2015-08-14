<?php
/*
 * Name: Share Controller
 * Description: Everything for sharing
 */

class ShareController extends UserController
{
    public function __construct(){
        parent::__construct();
        $this->pageName = "Workflow";
        $this->rootURL = "share";
    }

    public function postSave($context,$id)
    {
        if(\Input::has('user_id') && \Input::has('permission'))
        {
            $share = new \SwiftShare([
                'from_user_id' => $this->currentUser->id,
                'to_user_id' => \Input::get('user_id'),
                'permission' => \Input::get('permission')
            ]);

            if(\Input::has('notify_mail'))
            {
                $share->msg = \Input::get('msg');
            }

            $parentClass = \Config::get('context.'.$context);
            if($parentClass !== false)
            {
                $parentResource = (new $parentClass)->find(\Crypt::decrypt($id));
                if($parentResource)
                {
                    //Check if already exists
                    $count = $parentResource->share()->where('to_user_id','=',\Input::get('user_id'))->count();
                    if($count === 0)
                    {
                        if($parentResource->share()->save($share))
                        {
                            //Send Notification Mail
                            if(\Input::has('notify_mail'))
                            {
                                $this->sendMail($share);
                            }

                            return \Response::make("Success");
                        }
                        else
                        {
                            return \Response::make("Unable to save. Please retry later.",500);
                        }
                    }
                    else
                    {
                        return \Response::make("This form has already been shared with the specified person.",400);
                    }
                }
                else
                {
                    return \Response::make("Parent resource not found. Please contact your administrator.",400);
                }
            }
            else
            {
                return \Response::make("Context unrecognized. Please contact your administrator.",400);
            }
        }

        return \Response::make("Success");
    }

    public function postDelete($id)
    {
        $share = \SwiftShare::find(\Crypt::decrypt($id));

        if($share)
        {
            if($share->delete())
            {
                return \Response::make("Success");
            }
        }

        return \Response::make("Unable to complete your request.",400);
    }

    public function getView($context,$id)
    {
        $parentClass = \Config::get('context.'.$context);
        if($parentClass !== false)
        {
            $parentResource = (new $parentClass)->with('share')->find(\Crypt::decrypt($id));
            if($parentResource)
            {
                $this->data['form'] = $parentResource;
                $this->data['users'] = \User::where('activated','=',1)
                                        ->where('email','!=',\Config::get('website.system_mail'),'AND')
                                        ->where('id','!=',$this->currentUser->id,'AND')
                                        ->orderBy('first_name','ASC')
                                        ->orderBy('last_name','ASC')
                                        ->get();
                $this->data['permission'] = \SwiftShare::$permissions;
                return \View::make('share/shareable',$this->data);
            }
        }

        return \Response::make("An error has occured. Please try again later",500);
    }

    private function sendMail($share)
    {
        $share->load(['from_user','to_user','shareable']);
        $form = $share->shareable;
        if($form)
        {
            $workflowActivity = $form->workflow;
            $form->current_activity = \WorkflowActivity::progress($workflowActivity);
            $mailData = [
                    'name'=>$form->name,
                    'context' => $form->readableName,
                    'id'=>$form->id,
                    'current_activity'=>$form->current_activity,
                    'url'=>\Helper::generateUrl($form,true),
                    'msg'=> $share->msg,
                    'from_user_full_name' => $share->from_user->getFullName()
                    ];
            
            if($share->to_user)
            {
                if($share->to_user->activated)
                {
                    try
                    {
                        \Mail::queueOn('https://sqs.ap-southeast-1.amazonaws.com/731873422349/scott_swift_live_mail','emails.share.shared',array('form'=>$mailData,'user'=>$share->to_user),function($message) use ($share,$form){
                            $message->from($share->from_user->email,$share->from_user->getFullName());
                            $message->subject(\Config::get('website.name').' - A '.$form->readableName.' form has been shared with you: "'.$form->name.'" ID: '.$form->id);
                            $message->to($share->to_user->email);
                        });
                    }
                    catch (Exception $e)
                    {
                        \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        return \Response::make("Mail failed.",400);
                    }
                }
            }
        }
    }
}

