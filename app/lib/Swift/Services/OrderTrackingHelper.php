<?php

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodePermission;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
Use \SwiftOrder;
Use \SwiftDocs;
Use \SwiftTag;
Use \SwiftFreight;
Use \Es;

class OrderTrackingHelper{
    
    public $message;
    public $chclIndex = 'chcl';
    public $chclType = 'storage';
    
    public function smartMessage($data)
    {
        //Early Node
        $this->earlyNode($data);
        
    }
    
    /*
     * Goes through elasticsearch
     */
    public function searchCHCLVessel($vessel,$voyage)
    {
        $params = array();
        $params['type'] = $this->chclType;
        $params['index'] = $this->chclIndex;
        $params['body']=array('query'=>array(
                                    'fuzzy_like_this_field'=>array(
                                            "vessel" => array('like_text'=>$vessel)
                                        ),
                                    'fuzzy_like_this_field'=>array(
                                            "voy" => array('like_text'=>$voyage)
                                        )
                                    )
                                );
        return Es::search($params);
    }
    
    public function dynamicStory($filters=array())
    {
        $nodes_inprogress_responsible_witheta = SwiftNodeActivity::orderBy('updated_at','asc')
                                                ->with('definition')
                                                ->with('workflowactivity')
                                                ->whereHas('workflowactivity',function($q) use ($filters){
                                                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')->where('workflowable_type','=','SwiftOrder');
                                                })
                                                ->where('user_id','=',0,'AND')
                                                ->whereHas('permission',function($q){
                                                    return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                            ->whereIn('permission_name',(array)array_keys(\Sentry::getUser()->getMergedPermissions()));
                                                })
                                                ->whereHas('definition',function($q){
                                                    return $q->where('eta','>',0);
                                                });
                                                
        if(!empty($filters))
        {
            $nodes_inprogress_responsible_witheta->with(['workflowactivity.workflowable' => function($q) use ($filters){
                foreach($filters as $f)
                {
                    $q->where($f[0],$f[1],$f[2]);
                }
                return $q;
            }]);
        }
        
        $nodes_inprogress_responsible_witheta->get();
                                                
        if(count($nodes_inprogress_responsible_witheta))
        {
            foreach($nodes_inprogress_responsible_witheta as $n)
            {
                $workingDaysSinceUpdate = \Helper::getWorkingDays($n->updated_at->toDateString(),\Carbon::now()->toDateString());
                if($workingDaysSinceUpdate > $n->definition->eta)
                {
                    $story = array(
                                'actionText' => "You are late at the '{$n->definition->label}' task since <b>".($workingDaysSinceUpdate - $n->definition->eta)."</b> day(s).",
                                'context'   =>$n->workflowactivity->workflowable,
                                
                    );
                    return $story;
                }
            }
        }

        return false;
        
    }
}