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
Use Carbon;
use SwiftRecent;
use Sentry;
use Eloquent;

class Helper {
    
    public function loginSysUser()
    {
        $sysuser = Sentry::findUserByLogin(Config::get('website.system_mail'));
        if($sysuser)
        {
            Sentry::login($sysuser,false);
            if(Sentry::check())
            {
                return true;
            }
        }
        return false;
    }
    
    public function saveRecent($obj,$user)
    {
        $recent = $obj->recent()->where('user_id','=',$user->id)->first();
        if(count($recent))
        {
            $recent->updated_at = Carbon::now();
            $recent->save();
        }
        else {
            $recent = new SwiftRecent;
            $obj->recent()->save($recent);
        }
 
    }
    
    public function getProductPrice($job,$data)
    {
        if(!self::loginSysUser())
        {
            \Log::error('Unable to login system user');
            return;
        }
        
        if(isset($data['product']))
        {
            foreach($data['product'] as $p)
            {
                if($p['jde_itm'])
                {
                    $result = \JdeSales::getProductHighestPrice($p['jde_itm']);
                    if(count($result))
                    {
                        $prod = \SwiftAPProduct::find($p['id'])->first();
                        if(count($prod))
                        {
                            $prod->price = $result->UPRC;
                            $prod->save();                            
                        }
                    }
                }
            }
        }
        else
        {
            Log::error('No products were set');
        }
        
    }
    
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

        $relstack = array();
        
        foreach($arrayClass as $relation)
        {
            if(strpos($relation,".") === false)
            {
                $rel = $obj->{$relation}()->withTrashed()->get();
                $relexists = array_filter($arrayClass,function($val) use ($relation){
                                return (strpos($val,$relation.".") !== false);
                             });
                if($relexists !== false)
                {
                    //Stack relation for future usage
                    $relstack[$relation] = $rel;
                }
                
                foreach($rel as $r)
                {
                    $revision = array_merge($revision,$r->revisionHistory()->get()->all());
                }
            }
            else
            {
                foreach($relstack as $k=>$s)
                {
                    if(strpos($relation,$k) !== false)
                    {
                        foreach($s as $rel)
                        {
                            $relationships = $rel->{str_replace($k.".","",$relation)}()->withTrashed()->get();
                            foreach($relationships as $r)
                            {
                                $revision = array_merge($revision,$r->revisionHistory()->get()->all());
                            }   
                        }
                        break;
                    }
                }
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
                $url = "/aprequest/view/".Crypt::encrypt($obj->id);
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
    
    public function calculateStorageCost($storagestart,$numberOfContainers)
    {
        if($storagestart->diffInDays(Carbon::now(),false) <=0)
        {
            return 0;
        }
        else
        {
            $numberOfDays = Carbon::now()->diffInDaysFiltered(function(Carbon $date) {
                return $date->dayOfWeek != Carbon::SUNDAY;
            }, $storagestart);
            
            if($numberOfContainers > 1)
            {
                $oneToFour = "27.5";
                $fiveToNine = "36.7";
                $ten = "55.0";
            }
            else
            {
                $oneToFour = "13.8";
                $fiveToNine = "18.3";
                $ten = "27.5";
            }
            
            $storageCost = 0;
            if($numberOfDays >= 4)
            {
                $storageCost += (4 * $oneToFour);
                if($numberOfDays <= 9)
                {
                    //More than 4, less than 9
                    $remainderDays = $numberOfDays - 9;
                    $storageCost += ($remainderDays * $fiveToNine);
                }
                else
                {
                    //More than 9
                    $storageCost += (5 *$fiveToNine); 
                    $remainderDays = $numberOfDays - 9;
                    if($numberOfDays >=10)
                    {
                        //More than ten
                        $storageCost += ($remainderDays*$ten);
                    }
                }
            }
            else
            {
                //Less than 4
                $storageCost += $numberOfDays*$oneToFour;
            }
            return $storageCost;
        }
    }
    
}