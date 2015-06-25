<?php

/*
 * Name: Helper Classes
 * Description: Contain Useful Functions
 */

Namespace Swift\Services;

Use User;
Use Crypt;
Use Session;
Use Config;
Use Carbon;
use SwiftRecent;
use Sentry;
use Eloquent;

class Helper {
    /*
     * Finds system user and logs him in.
     * Used mostly for laravel queue workers
     *
     * @return boolean
     */
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

    /*
     * Saves history of a recently viewed form
     *
     * @param \Illuminate\Database\Eloquent\Model $obj
     * @param \Sentry $user
     *
     */
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

    /*
     * Fetches product price and saves it for the product in question.
     * Used in queues solely
     *
     * @param mixed $job
     * @param array $data
     *
     * @return null
     */
    public function getProductPrice($job,$data)
    {
        if(!self::loginSysUser())
        {
            \Log::error('Unable to login system user');
            return;
        }
        
        if(isset($data['product_id']))
        {
            $prod = $data['class']::find((int)$data['product_id']);
            if($prod && isset($prod->jde_itm) && (int)$prod->jde_itm > 0)
            {
                $result = \JdeSales::getProductLatestCostPrice($prod->jde_itm);
                if($result)
                {
                    $prod->price = round(abs($result->ECST/$result->SOQS),4);
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

    /*
     * Encodes an array into an array compatible format for use with X-editable's select lists as source of data
     *
     * @param array $array
     *
     * @return array
     */
    public function jsonobject_encode(array $array)
    {
        $converted_array = array();
        
        foreach($array as $k=>$v)
        {
            $converted_array[] = array('value'=>$k,'text'=>$v);
        }
        
        return $converted_array;
    }

    /*
     * Helper Function: Fetches User Name of current logged in user
     *
     * @param integer $user
     * @param \Sentry $current_user
     * @param boolean $me
     *
     * @return string
     */
    public function getUserName($user,$current_user,$me=true)
    {
        $user = User::find($user);
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

    /*
     * Iterates through all objects which implements the trait `revisionable` and compiles a list of revision changes
     * Param $only_relationships when set to true, returns only revisions for the relationships and omits the main forms' revisions.
     *
     * @param array $arrayClass
     * @param \Illuminate\Database\Eloquent\Model $obj
     * @param boolean $only_relationships
     *
     * @return array
     */
    public function getMergedRevision(array $arrayClass,&$obj,$only_relationships=false)
    {
        $revision = array();
        if(!$only_relationships)
        {
            $revision = array_merge($revision,$obj->revisionHistory()->get()->all());
        }

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

    /*
     * Generates an order process link.
     * Used in `freight company` form - the ticker widget
     *
     * @param \Illuminate\Database\Eloquent\Model $order
     *
     * @return string
     */
    public function getOrderTrackingLink($order)
    {
        $html = "<a class=\"pjax\" href=\"/order-tracking/view/".(\Crypt::encrypt($order->id))."\" data-original-title=\"Click to view order process\" data-placement=\"placement\" rel=\"bottom\"><i class=\"fa fa-lg- fa-map-marker\"></i>&nbsp;";
        $html.= trim($order->name);
        $html.="</a>";
        
        return $html;
    }

    /*
     * Generates URL for viewing main forms.
     * Used in many places
     *
     * @param \Illuminate\Database\Eloquent\Model $obj
     * @param boolean $absoluteaddress
     *
     * @return string
     */
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
            case "SwiftACPRequest":
                $url = "/accounts-payable/view/".Crypt::encrypt($obj->id);
                break;
            case "SwiftPR":
                $url = "/product-returns/view/".Crypt::encrypt($obj->id);
                break;
            case "JdeSupplierMaster":
                $url = "/accounts-payable/supplier/view/".trim($obj->Supplier_Code);
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

    /*
     * Check if session has a filter value set
     *
     * @param string $session_variable
     * @param string $filter_name
     * @param mixed $filter_value
     *
     * @return boolean
     */
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

    /*
     * Calculates the number of storage days for a container
     * Used for order-process
     *
     * @param \Carbon\Carbon $storagestart
     *
     * @return integer
     */
    public function calculateStorageNumberOfDays(\Carbon\Carbon $storagestart)
    {
        $holidays = \Holidays::getAllDates();
        $nextSunday = Carbon::parse('next sunday');
        $numberOfDays = Carbon::now()->diffInDaysFiltered(function(Carbon $date) use ($holidays,$nextSunday) {
            //Is first sunday, we omit
            if($date->dayOfWeek === Carbon::SUNDAY && $date->diffInDays($nextSunday,false) === 0)
            {
                return false;
            }

            //Is a holiday, we omit
            if(in_array($date->format('Y-m-d'),$holidays))
            {
                return false;
            }

            return true;
        }, $storagestart);

        return $numberOfDays;
    }

    /*
     * Calculates the number of demurrage days for a container
     * Not Used.
     *
     * @param \Carbon\Carbon $demurrageStart
     *
     * @return integer
     */
    public function calculateDemurrageNumberOfDays(\Carbon\Carbon $demurrageStart)
    {
        return Carbon::now()->diffInDays($demurrageStart);
    }

    /*
     * Calculate storage cost of containers in USD
     *
     * @param \Carbon\Carbon $storagestart
     * @param integer $containers
     *
     * @return float
     */
    public function calculateStorageCost(\Carbon\Carbon $storagestart, $containers)
    {
        if($storagestart->diffInDays(Carbon::now(),false) <=0)
        {
            return 0;
        }
        else
        {
            $numberOfContainers = count($containers);
            $numberOfDays = \Helper::calculateStorageNumberOfDays($storagestart);
            
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
            if($numberOfDays > 4)
            {
                //From 1st to 4th day period
                $storageCost += (4 * $oneToFour);
                $remainderDays = $numberOfDays - 4;
                if($numberOfDays <= 9)
                {
                    //From 5th to 9th day period
                    $storageCost += ($remainderDays * $fiveToNine);
                }
                else
                {
                    if($numberOfDays >=10)
                    {
                        //From 5 to 8
                        $remainderDays -= 5;
                        $storageCost += (5 *$fiveToNine);
                        //More than ten
                        $storageCost += ($remainderDays*$ten);
                    }
                    else
                    {
                        //Less than 10
                        $storageCost += ($remainderDays *$fiveToNine);
                    }
                }
            }
            else
            {
                //Less than 4
                $storageCost += $numberOfDays*$oneToFour;
            }
            return $storageCost*$numberOfContainers;
        }
    }

    /*
     * Calculate demurrage cost of container in USD
     *
     * @param \Carbon\Carbon $demurrageStart
     * @param integer $containers
     *
     * @return float
     */
    public function calculateDemurrageCost(\Carbon\Carbon $demurrageStart, $containers)
    {
        $demurrageCost = 0;
        if($demurrageStart->diffInDays(Carbon::now(),false) <=0)
        {
            return 0;
        }
        else
        {
            /*
             * Cost Declaration
             */

            $twentyCharges = ['oneToFourteen'=>15,'FifteenToTwentyOne'=>25,'TwentyTwoAndUpwards'=>40];
            $fourtyCharges = ['oneToFourteen'=>30,'FifteenToTwentyOne'=>50,'TwentyTwoAndUpwards'=>80];


            foreach($containers as $c)
            {
                if(array_key_exists($c->type,\SwiftShipment::$type))
                {
                    $numberOfDays = \Helper::calculateDemurrageNumberOfDays($demurrageStart);
                    /*
                     * 20" container
                     */
                    if($c->type === \SwiftShipment::FCL_20)
                    {
                        if($numberOfDays > 14)
                        {
                            $demurrageCost += $twentyCharges['oneToFourteen']*14;
                            $remainderDays = $numberOfDays - 14;
                            if($numberOfDays > 21)
                            {
                                //from 15th to 21st days = 7 days
                                $remainderDays -= 7;
                                $demurrageCost += $twentyCharges['FifteenToTwentyOne']*7;
                                
                                //22 and upwards
                                $demurrageCost += $twentyCharges['TwentyTwoAndUpwards'] * $remainderDays;
                            }
                            else
                            {
                                //between 14 and 21
                                $demurrageCost += $twentyCharges['FifteenToTwentyOne']*$remainderDays;
                            }
                        }
                        else
                        {
                            //14 days or less
                            $demurrageCost += $twentyCharges['oneToFourteen']*$numberOfDays;
                        }
                    }
                    /*
                     * 40" Container
                     */
                    if($c->type === \SwiftShipment::FCL_40)
                    {
                        if($numberOfDays > 14)
                        {
                            $demurrageCost += $fourtyCharges['oneToFourteen']*14;
                            $remainderDays = $numberOfDays - 14;
                            if($numberOfDays > 21)
                            {
                                //from 15th to 21st days = 7 days
                                $remainderDays -= 7;
                                $demurrageCost += $fourtyCharges['FifteenToTwentyOne']*7;

                                //22 and upwards
                                $demurrageCost += $fourtyCharges['TwentyTwoAndUpwards'] * $remainderDays;
                            }
                            else
                            {
                                //between 14 and 21
                                $demurrageCost += $fourtyCharges['FifteenToTwentyOne']*$remainderDays;
                            }
                        }
                        else
                        {
                            //14 days or less
                            $demurrageCost += $fourtyCharges['oneToFourteen']*$numberOfDays;
                        }
                    }
                }
            }

            return $demurrageCost*count($containers);
        }
    }
    
    /*
     * Calculates the number of business days between two dates and it skips the holidays
     *
     * @param string $startDate
     * @param strinng $endDate
     *
     * @return integer
     */
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

    public function dueInDays(\Carbon\Carbon $date)
    {
        $diff = Carbon::now()->diffInDays($date,false);
        if($diff < -1)
        {
            return abs($diff)." days ago";
        }
        if($diff === -1)
        {
            return "yesterday";
        }

        if($diff === 0)
        {
            return "today";
        }
        if($diff === 1)
        {
            return "tomorrow";
        }
        if($diff > 1)
        {
            return $diff. " days from now";
        }
        return "(Unknown)";

    }
    
    public function systemHealth($lateCount,$totalCount)
    {
        if($totalCount == 0 || $lateCount == 0)
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

    /*
     * Save a Children relationship of a main model
     * @param Object $main
     * @param Class  $model
     * @param String $relationName
     * @param \Sentry\User $current_user
     * @param Bool $workflow_update
     * @return \Illuminate\Support\Facades\Response
     */
    public function saveChildModel($main,$model,$relationName,$current_user,$workflow_update=false)
    {
        if(is_numeric(\Input::get('pk')))
        {
            //All Validation Passed, let's save
            $model_obj = new $model();
            $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
            if($main->{$relationName}()->save($model_obj))
            {
                if($workflow_update)
                {
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($main),'id'=>$main->id,'user_id'=>$current_user->id));
                }
                return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($model_obj->id),'id'=>$model_obj->id]));
            }
            else
            {
                return \Response::make('Failed to save. Please retry',400);
            }

        }
        else
        {
            $model_obj = $model::find(\Crypt::decrypt(\Input::get('pk')));
            if($model_obj)
            {
                $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                if($model_obj->save())
                {
                    if($workflow_update)
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($main),'id'=>$main->id,'user_id'=>$current_user->id));
                    }
                    return \Response::make('Success');
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }
            }
            else
            {
                return \Response::make('Error saving: Invalid PK',400);
            }
        }
    }


    /*
     * Delete a child model - X-editable
     *
     * @param string $childClass
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteChildModel($childClass)
    {
        $child = $childClass::find(\Crypt::decrypt(\Input::get('pk')));

        if($child)
        {
            if($child->delete())
            {
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Unable to delete',400);
            }
        }
        else
        {
            return \Response::make('Entry not found',404);
        }
    }

    /*
     * Reconciliate Swift Purchase Order with JDE Po Header
     */

    public function validatePendingPurchaseOrder($poArray=false)
    {
        
        $orphanPending = \SwiftPurchaseOrder::whereNotNull('reference')
                        ->whereNotNull('type')
                        ->where('validated','=',\SwiftPurchaseOrder::VALIDATION_PENDING)
                        ->get();

        foreach($orphanPending as $o)
        {
            $po = \JdePurchaseOrder::findByNumberAndType($o->reference, $o->type);
            if($po)
            {
                $o->order_id = $po->id;
                $o->validated = \SwiftPurchaseOrder::VALIDATION_FOUND;
                $o->validated_on = Carbon::now();
                $o->save();
            }
            else
            {
                $o->validated = \SwiftPurchaseOrder::VALIDATION_NOTFOUND;
                $o->validated_on = Carbon::now();
                $o->save();
            }
        }
    }


    /*
     * Try to find Purchase Order Again
     */
    public function validateNotFoundPurchaseOrder()
    {
        $orphanNotFound = \SwiftPurchaseOrder::whereNotNull('reference')
                        ->whereNotNull('type')
                        ->where('validated','=',\SwiftPurchaseOrder::VALIDATION_NOTFOUND)
                        ->get();

        foreach($orphanNotFound as $o)
        {
            $po = \JdePurchaseOrder::findByNumberAndType($o->reference, $o->type);
            if($po)
            {
                $o->order_id = $po->id;
                $o->validated = \SwiftPurchaseOrder::VALIDATION_FOUND;
                $o->validated_on = Carbon::now();
                $o->save();
            }
            else
            {
                //If still not found after 2 days, mark as permanently not found.
                if($o->validated_on->diffInDays(Carbon::now(),false) >= 2)
                {
                    $o->validated_on = Carbon::now();
                    $o->validated = \SwiftPurchaseOrder::VALIDATION_NOTFOUND_PERMANENT;
                }
                else
                {
                    $o->validated_on = Carbon::now();
                }
                $o->save();
            }
        }
    }

    public function filterQueryParam($url,$query_param)
    {
        $parseUrl = parse_url($url);
        if($parseUrl !== false)
        {
            parse_str($parseUrl['query'],$params);
            if($params !== false)
            {
                if(array_key_exists($query_param,$params))
                {
                    unset($params[$query_param]);
                    if(count($params) > 0)
                    {
                        return "?".http_build_query($params);
                    }
                }

            }
        }
        return "";
    }

    public function saveInvoiceCancelled($job,$data)
    {
        $lines = \JdeSales::getProducts((int)$data['invoice_id']);

        $invoiceCancelledId = \SwiftPRReason::getInvoiceCancelledScottId();
        //No products
        if(!count($lines))
        {
            \Log::error("No products found for invoice id: ".$data['invoice_id']);
            $job->delete();
            return false;
        }
        
        //Create Form
        $pr = new \SwiftPR([
                'customer_code' => $lines->first()->AN8,
                'owner_user_id' => $data['user_id'],
                'type' => \SwiftPR::INVOICE_CANCELLED
                ]);
        $pr->save();

        $pr->approval()->save(new \SwiftApproval([
            'approved' => \SwiftApproval::APPROVED,
            'type' => \SwiftApproval::PR_REQUESTER,
            'approval_user_id' => $data['user_id']
        ]));

        $sysuser = \Sentry::findUserByLogin(\Config::get('website.system_mail'));

        $approval = new \SwiftApproval([
            'approved' => \SwiftApproval::APPROVED,
            'type' => \SwiftApproval::PR_RETAILMAN,
            'approval_user_id' => $sysuser->id
        ]);

        //Add Products

        foreach($lines as $l)
        {
            $product = new \SwiftPRProduct([
                'pr_id' => $pr->id,
                'jde_itm' => $l->ITM,
                'qty_client' => $l->SOQS,
                'qty_pickup' => $l->SOQS,
                'qty_triage_picking' => $l->SOQS,
                'qty_triage_disposal' => 0,
                'invoice_id' => $l->DOC,
                'invoice_recognition' => \SwiftPRProduct::INVOICE_AUTO,
                'price' => $l->AEXP,
                'reason_id' => $invoiceCancelledId,
                'pickup' => \SwiftPRProduct::NO_PICKUP
            ]);

            $product->save();

            /*
             * Add Approval by System
             */

            $product->approvalretailman()->save($approval);
        }

        if(\WorkflowActivity::update($pr,$data['context']))
        {
            //Story Relate
            \Queue::push('Story@relateTask',array('obj_class'=>get_class($pr),
                                                 'obj_id'=>$pr->id,
                                                 'action'=>\SwiftStory::ACTION_CREATE,
                                                 'user_id'=>$data['user_id'],
                                                 'context'=>get_class($pr)));
            //Notification
            \Notification::send(\SwiftNotification::TYPE_INFO,$pr);
        }
        else
        {
            \Log::error("Failed to save workflow for PR id: ".$pr->id);
        }
        
        $job->delete();
    }

    /*
     * Generates Current Financial Year Start Based on Today's Date
     *
     * @return \Carbon\Carbon
     */
    public function getFinancialYearStart()
    {
        $financial_year_start = explode('-',\Config::get('company.financial_year_start'));
        $financial_year_end = explode('-',\Config::get('company.financial_year_end'));

        if((int)date("m") <= (int)$financial_year_end[1])
        {
             $startdate = date((date('Y')-1).'-'.$financial_year_start[1].'-'.$financial_year_start[0]);
        }
        else
        {
            $startdate = date('Y-'.$financial_year_start[1].'-'.$financial_year_start[0]);
        }

        return \Carbon::createFromFormat('Y-m-d H:i',$startdate." 00:00");
    }

    /*
     * Generates Current Financial Year End Based on Today's Date
     *
     * @return \Carbon\Carbon
     */
    public function getFinancialYearEnd()
    {
        $financial_year_start = explode('-',\Config::get('company.financial_year_start'));
        $financial_year_end = explode('-',\Config::get('company.financial_year_end'));

        if((int)date("m")>=(int)$financial_year_start)
        {
            $enddate = date((date('Y')+1).'-'.$financial_year_end[1].'-'.$financial_year_end[0]);
        }
        else
        {
            $enddate = date('Y-'.$financial_year_end[1].'-'.$financial_year_end[0]);
        }

        return \Carbon::createFromFormat('Y-m-d H:i',$enddate." 00:00");
    }

    /*
     * Resolve Context From class name
     *
     * @param string $className
     * @return string|boolean
     */

    public function resolveContext($className)
    {
        $allContext = \Config::get('context');

        return array_search($className,$allContext);
    }
    
}