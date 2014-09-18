<?php

Namespace Swift\Services;

/*
 * Name: Helper Classes
 * Description: Contain Useful Functions
 */

Use User;
Use Crypt;
Use Session;
Use Config;

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
    
    public function generateUrl($obj,$absoluteaddress=false)
    {
        $class = get_class($obj);
        switch($class)
        {
            case "SwiftOrder":
                $url = "/order-tracking/view/".Crypt::encrypt($obj->id);
                break;
            case "SwiftAPRequest":
                $url = "/aprequest/view".Crypt::encrypt($obj->id);
                break;
        }
        
        if($absoluteaddress)
        {
           $url = Config::get('app.url').$url; 
        }
        return $url;
    }
    
    public function sessionHasFilter($session_variable,$filter_name,$filter_value='')
    {
        if($filter_value)
        {
            return Session::has($session_variable) && isset(Session::get($session_variable)[$filter_name]) && Session::get($session_variable)[$filter_name] == $filter_value;
        }
        else
        {
            return Session::has($session_variable) && isset(Session::get($session_variable)[$filter_name]);
        }
    }
    
}