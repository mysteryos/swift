<?php

Namespace Swift\Services;

/*
 * Name: Helper Classes
 * Description: Contain Useful Functions
 */

Use User;

class Helper {
    
    public function jsonobject_encode(array $array)
    {
        foreach($array as $k=>$v)
        {
            $converted_array[] = array('value'=>$k,'text'=>$v);
        }
        
        return $converted_array;
    }
    
    public function getUserName($user_id,$current_user,$me=true)
    {
        $user = User::find($user_id);
        if($user)
        {
            
            if($user->id == $current_user->id && $me)
            {
                return "Me";
            }
            else
            {
                return $user->first_name." ".$user->last_name;
            }
        }
        else
        {
            return "(Unknown)";
        }
    }
    
    public function getMergedRevision(array $arrayClass,&$obj)
    {
        $revision = array();
        $revision = array_merge($revision,$obj->revisionHistory()->get()->all());

        foreach($arrayClass as $relation)
        {
            $rel = $obj->{$relation}()->withTrashed()->get();
            foreach($rel as $r)
            {
                $revision = array_merge($revision,$r->revisionHistory()->get()->all());
            }
        }
        usort($revision,function($a,$b){
            return new \DateTime($b->created_at) > new \DateTime($a->created_at);
        });
        
        return $revision;
        
    }
    
    public function getOrderTrackingLink($order)
    {
        $html = "<a class=\"pjax\" href=\"/order-tracking/view/".(\Crypt::encrypt($order->id))."\" data-original-title=\"Click to view order process\" data-placement=\"placement\" rel=\"bottom\"><i class=\"fa fa-lg- fa-map-marker\"></i>&nbsp;";
        $html.= trim($order->name);
        $html.="</a>";
        
        return $html;
    }
    
}