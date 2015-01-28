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
        \DB::reconnect();
        
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
    
    /*
     * Update last seen for user
     */
    public function updateUserLastSeen($user)
    {
        $last_seen = \Cache::get('last_seen',array());
        $last_seen[$user->email] = \Carbon::now();
        \Cache::forever('last_seen',$last_seen);
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
        
        if(isset($data['product_id']))
        {
            $prod = \SwiftAPProduct::find((int)$data['product_id']);
            if(count($prod) && isset($prod->jde_itm) && (int)$prod->jde_itm > 0)
            {
                $result = \JdeSales::getProductLatestCostPrice($prod->jde_itm);
                if(count($result))
                {
                    $prod->price = round($result->UPRC,2);
                }
                else
                {
                    $prod->price = 0;
                }
                $prod->save();
                $job->delete();
            }
        }
        else
        {
            \Log::error('No products were set');
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
    
    public function getUserName($user,$current_user,$me=true)
    {
//        if(!$user  instanceof \Cartalyst)
//        {
            $user = User::find($user);
//        }
        
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
            default:
                $url ="javascript:void(0);";
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
    
    //The function returns the no. of business days between two dates and it skips the holidays
    public function getWorkingDays($startDate,$endDate)
    {
        $holidays = \Config::get('holidays.days');
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
       $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
          $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }
    
    public function previousBusinessDay(\Carbon\Carbon $date)
    {
        if(!$date->isWeekday())
        {
            $date->subDay();
        }
        foreach(\Config::get('holidays.days') as $holiday)
        {
            $holidayDate = Carbon::createFromFormat('Y-m-d',$holiday);
            if($holidayDate->diffInDays($date) == 0)
            {
                $date->subDay();
                $date = self::previousBusinessDay($date);
            }
        }
        
        return $date;
    }
    
    public function nextBusinessDay(\Carbon\Carbon $date)
    {
        //is Weekend
        if(!$date->isWeekday())
        {
            $date->addDay();
        }
        foreach(\Config::get('holidays.days') as $holiday)
        {
            $holidayDate = Carbon::createFromFormat('Y-m-d',$holiday);
            if($holidayDate->diffInDays($date) == 0)
            {
                $date->addDay();
                $date = self::nextBusinessDay($date);
            }
        }
        
        return $date;        
    }
    
    public function systemHealth($lateCount,$totalCount)
    {
        if($totalCount == 0)
        {
            return "<span class='color-greenDark' title='No pending tasks at all'>Heavenly</span>";
        }
        else
        {
            //percent of nodes not overdue.
            $percentOfNodes = (($totalCount-$lateCount)/$totalCount) * 100;
            if($percentOfNodes >= 90)
            {
                return "<span class='color-greenDark' title='There is little or no late tasks on the system'>Awesome</span><span> | Overdue tasks: ".round(($lateCount/$totalCount)*100,2)."% out of $totalCount</span>";
            }
            elseif($percentOfNodes >= 75)
            {
                return "<span class='color-greenDark' title='There is some late tasks on the system'>Great</span><span> | Overdue tasks: ".round(($lateCount/$totalCount)*100,2)."% out of $totalCount</span>";
            }
            elseif($percentOfNodes >= 50)
            {
                return "<span class='color-orangeDark' title='Late tasks are starting to accumulate on the system'>Not Bad</span><span> | Overdue tasks: ".round(($lateCount/$totalCount)*100,2)."% out of $totalCount</span>";
            }
            elseif($percentOfNodes >= 25)
            {
                return "<span class='color-redDark' title='Late tasks are now a sore sight for our eyes'>Bad</span><span> | Overdue tasks: ".round(($lateCount/$totalCount)*100,2)."% out of $totalCount</span>";
            }
            else
            {
                return "<span class='color-red' title='R.I.P'>ICU</span><span> | Overdue tasks: ".round(($lateCount/$totalCount)*100,2)."% out of $totalCount</span>";
            }
        }
    }
    
}