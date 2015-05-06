<?php

class OrderTrackingController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Order Process";
        $this->rootURL = $this->data['rootURL'] = $this->context = $this->data['context'] = "order-tracking";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
        $this->editPermission = \Config::get("permission.{$this->context}.edit");
        $this->createPermission = \Config::get("permission.{$this->context}.create");
    }
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->context.'/overview');
    }
    
    /*
     * Overview
     */    
    
    public function getOverview($business_unit=false)
    {
        //Fetch preference from session for business unit filter
        if($business_unit === false)
        {
            $business_unit = Session::get('order_tracking_overview_business_unit',function(){return false;});
        }
        else
        {
            if($business_unit != 0)
            {
                Session::put('order_tracking_overview_business_unit',$business_unit);
            }
            else
            {
                Session::forget('order_tracking_overview_business_unit');
                $business_unit = false;
            }
        }
        
        $this->pageTitle = 'Overview';
        $this->data['inprogress_limit'] = 15;
        
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes('order-tracking');
        //All pending nodes with Eta
        $this->data['pending_node_count'] = SwiftNodeActivity::countPendingNodesWithEta('order-tracking');
        
        /*
         * Order in Progress
         */
        
        $order_inprogress = $order_inprogress_important = $order_inprogress_responsible = $order_inprogress_important_responsible = array();
        
        $order_inprogress = SwiftOrder::getInProgress($this->data['inprogress_limit'],false,$business_unit);                            
        
        $order_inprogress_important = SwiftOrder::getInProgress(0,true,$business_unit);
                            
        $order_inprogress_responsible = SwiftOrder::getInProgressResponsible(0,false,$business_unit); 
                            
        $order_inprogress_important_responsible = SwiftOrder::getInProgressResponsible(0,true,$business_unit);                           
        
        $order_inprogress = $order_inprogress->diff($order_inprogress_responsible);
        $order_inprogress_important = $order_inprogress_important->diff($order_inprogress_important_responsible);
        $order_inprogress_count = count($order_inprogress);
                            
        if(count($order_inprogress) == 0 || count($order_inprogress_important) == 0 || count($order_inprogress_responsible) == 0 || count($order_inprogress_important_responsible) == 0)
        {
            $this->data['in_progress_present'] = true;
        }
        else
        {
            $this->data['in_progress_present'] = false;
        }
        
        foreach(array($order_inprogress,$order_inprogress_responsible,$order_inprogress_important,$order_inprogress_important_responsible) as $orderarray)
        {
            foreach($orderarray as &$o)
            {
                $o->current_activity = WorkflowActivity::progress($o);
                $o->activity = Helper::getMergedRevision($o->revisionRelations,$o);
            }
        }
        
        /*
         * Storage Tracking - SEA
         */
        
        /*$order_storage = SwiftOrder::has('reception','=',0)->with('freight','document','document.tag')->whereHas('workflow',function($q){
                            return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                        })->whereHas('freight',function($q){
                            return $q->where('freight_type','=',SwiftFreight::TYPE_SEA)->where('vessel_name','<>','""')->where('vessel_voyage','<>','""')->where('freight_eta','!=','""');
                        })->get();
        
        $storage_array = array();
                        
        foreach($order_storage as $o)
        {
            $closestFreightByEta = false;
            foreach($o->freight as $f)
            {
                    if($closestFreightByEta === false)
                    {
                        $closestFreightByEta = $f;
                    }
                    else
                    {
                        $diff = $f->freight_eta->diffInDays($closestFreightByEta->freight_eta,false);
                        if($diff > 0)
                        {
                            //If freight eta is greater than closest eta, we save it. We take the biggest eta by sea, which SHOULD be the last sea freight.
                            $closestFreightByEta = $f;
                        }
                    }
            }
            //So we have the freight. Search vessel on elastic
            $searchresults = OrderTrackingHelper::searchCHCLVessel($closestFreightByEta->vessel_name,$closestFreightByEta->vessel_voyage);
            if($searchresults['hits']['max_score'] > 1 && $searchresults['hits']['total'] > 0)
            {
                $relevantSearch = false;
                foreach($searchresults['hits']['hits'] as $s)
                {
                    if($relevantSearch === false)
                    {
                        $relevantSearch = $s['_source'];
                    }
                    else
                    {
                        $searchdate = new Carbon($s['_source']['date_start']['date']);
                        if(Carbon($relevantSearch['date_start']['date'])->diffInDays($closestFreightByEta->freight_eta) > $searchdate->diffIndays($closestFreightByEta->freight_eta))
                        {
                            $relevantSearch = $s['_source'];
                        }
                    }
                }
                
                //Got our relevant search, based on date closest to Freight Eta
                $storage_array[] = array(
                                    'order'=>$o,
                                    'storage_start'=> new Carbon($relevantSearch['storage']['date']),
                                    'rate' => $relevantSearch['storage_rate'],
                                    'chcl_record' => $relevantSearch,
                                    'freight' => $closestFreightByEta
                                    );
            }
        }
        */
        
        /*
         * Stories
         */
        
        $this->data['business_unit'] = $business_unit;
        $this->data['rootURL'] = $this->rootURL;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));
        $this->data['order_inprogress'] = $order_inprogress;
        $this->data['order_inprogress_responsible'] = $order_inprogress_responsible;
        $this->data['order_inprogress_important'] = $order_inprogress_important;
        $this->data['order_inprogress_important_responsible'] = $order_inprogress_important_responsible;
        /*$this->data['order_storage'] = $storage_array*/
        $this->data['isAdmin'] = $this->currentUser->hasAccess(array($this->adminPermission));
        
        return $this->makeView('order-tracking/overview');
    }
    
    public function getSummary($business_unit=false)
    {
        $this->pageTitle = 'Summary';
        
        $query = SwiftOrder::query();
        if($business_unit !== false)
        {
            $query->whereBusinessUnit($business_unit);
        }
        
        $result = $query->with(['freight','freight.company','customsDeclaration','storage','shipment','purchaseOrder','reception'])->remember(60)->get();
        
        foreach($result as &$r)
        {
            $r->current_activity = WorkflowActivity::progress($r,$this->context);
            
            //define all data variables
            $r->data_freight_name = 
            $r->data_vessel_name =
            $r->data_freight_etd = 
            $r->data_freight_eta =
            $r->data_purchaseOrder = 
            $r->data_storage_start =
            $r->data_demurrage_start =
            $r->data_customsDeclaration_customs_filled_at =
            $r->data_customsDeclaration_customs_reference =
            $r->data_customsDeclaration_customs_processed_at =
            $r->data_customsDeclaration_customs_cleared_at =
            $r->data_shipment_container_no =
            $r->data_reception_grn = "N/A";
            
            //Freight
            if(count($r->freight))
            {
                $freight =  implode(",",array_filter(array_map(function($v){
                                        if(array_key_exists('freight_type',$v) && in_array($v['freight_type'],array(\SwiftFreight::TYPE_AIR,\SwiftFreight::TYPE_SEA)))
                                        {
                                            return $v['freight_company'];
                                        }
                                        else
                                        {
                                            return false;
                                        }
                                    },$r->freight->toArray()),function($v){
                                        return $v !== false;
                                    }));
                if($freight!=="")
                {
                    $r->data_freight_name = $freight;
                }
                
                $vessel_name = implode(",",
                                    array_filter(
                                        array_map(
                                            function($v){
                                                if(array_key_exists('freight_type',$v) && in_array($v['freight_type'],array(\SwiftFreight::TYPE_AIR,\SwiftFreight::TYPE_SEA)))
                                                {
                                                    return $v['vessel_name'];
                                                }
                                                else
                                                {
                                                    return false;
                                                }
                                            },$r->freight->toArray()
                                        ),
                                        function($v)
                                        {
                                            return $v !== false;
                                        }
                                    )
                                );
                if($vessel_name !== "")
                {
                    $r->data_vessel_name = $vessel_name;
                }
                
                $etd = implode(",",
                            array_filter(
                                array_map(function($v)
                                {
                                    if(array_key_exists('freight_type',$v) && in_array($v['freight_type'],array(\SwiftFreight::TYPE_AIR,\SwiftFreight::TYPE_SEA)))
                                    {
                                        return (new Carbon($v['freight_etd']))->toDateString();
                                    }
                                    else
                                    {
                                        return false;
                                    }
                                },$r->freight->toArray()),
                                function($v)
                                {
                                    return $v !== false;
                                }
                            )
                        );

                if($etd !== "")
                {
                    $r->data_freight_etd = $etd;
                }
                
                $eta = implode(",",
                            array_filter(
                                array_map(function($v)
                                {
                                    if(array_key_exists('freight_type',$v) && in_array($v['freight_type'],array(\SwiftFreight::TYPE_AIR,\SwiftFreight::TYPE_SEA)))
                                    {
                                        return (new Carbon($v['freight_eta']))->toDateString();
                                    }
                                    else
                                    {
                                        return false;
                                    }
                                },$r->freight->toArray()),
                                function($v)
                                {
                                    return $v !== false;
                                }
                            )
                        );
                if($eta !== "")
                {
                    $r->data_freight_eta = $eta;
                }                
                
            }
            
             //purchaseOrder           
            if(count($r->purchaseOrder))
            {
                $po =   implode(",",
                            array_filter(
                                array_map(
                                    function($v){
                                        return $v['reference'];
                                    },$r->purchaseOrder->toArray()
                                ),
                                function($v){
                                    return $v !== "";
                                }
                            )
                        );
                if(!empty($po))
                {
                    $r->data_purchaseOrder = $po;
                }
            }
            else
            {
                $r->data_purchaseOrder = "N/A";
            }
            
            //Storage
            if(count($r->storage))
            {
                $storage = $r->storage->first();
                if($storage->storage_start instanceof \Carbon\Carbon)
                {
                    $r->data_storage_start = $storage->storage_start->toDateString();
                }
                
                if($storage->demurrage_start instanceof \Carbon\Carbon)
                {
                    $r->data_demurrage_start = $storage->demurrage_start->toDateString();
                }
            }

            //customsDeclaration
            if(count($r->customsDeclaration))
            {
                $customs = $r->customsDeclaration->first();
                if($customs->customs_filled_at instanceof \Carbon\Carbon)
                {
                    $r->data_customsDeclaration_customs_filled_at = $customs->customs_filled_at->toDateString();
                }
                
                if($customs->customs_reference !== "")
                {
                    $r->data_customsDeclaration_customs_reference = $customs->customs_reference;
                }
                
                if($customs->customs_processed_at instanceof \Carbon\Carbon)
                {
                    $r->data_customsDeclaration_customs_processed_at = $customs->customs_processed_at->toDateString();
                }
                
                if($customs->customs_cleared_at instanceof \Carbon\Carbon)
                {
                    $r->data_customsDeclaration_customs_cleared_at = $customs->customs_cleared_at->toDateString();
                }
            }
            
            //shipment
            if(count($r->shipment))
            {
                $shipment_container_no =    implode(",",
                                                array_filter(
                                                        array_map(function($v)
                                                        {
                                                            return $v['container_no'];
                                                        },$r->shipment->toArray()),
                                                        function($v)
                                                        {
                                                            return $v !== "";
                                                        }
                                                )
                                            );
                                                
                if(!empty($shipment_container_no))
                {
                    $r->data_shipment_container_no = $shipment_container_no;
                }
            }
            
            //reception
            if(count($r->reception))
            {
                $reception_grn =    implode(",",
                                        array_filter(
                                            array_map(function($v){
                                                        return $v['grn'];
                                                },$r->reception->toArray()
                                            ),
                                            function($v)
                                            {
                                                return $v !== "";
                                            }
                                        )
                                    );
                                    
                if(!empty($reception_grn))
                {
                    $r->data_reception_grn = $reception_grn;
                }
            }
        }
        
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));
        $this->data['business_unit'] = $business_unit;
        $this->data['rootURL'] = $this->rootURL;
        $this->data['summary_datatable'] = $result;
        
        return $this->makeView('order-tracking/summary');
    }

    public function getTransitCalendar($business_unit=false)
    {
        if($business_unit === false)
        {
            $business_unit = Session::get('order_tracking_transit_calendar_business_unit',function(){return false;});
        }
        else
        {
            if($business_unit != 0)
            {
                Session::put('order_tracking_transit_calendar_business_unit',$business_unit);
            }
            else
            {
                Session::forget('order_tracking_transit_calendar_business_unit');
                $business_unit = false;
            }
        }

        $freightToday = SwiftFreight::where('freight_eta','=',Carbon::now()->format('Y-m-d'),'and')
                    ->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,SwiftFreight::TYPE_AIR])
                    ->with(['order','order.workflow'])
                    ->whereHas('order',function($q) use ($business_unit){
                        $q->whereHas('workflow',function($q2){
                            return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                        });
                        if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                        {
                            $q->where('business_unit','=',$business_unit);
                        }
                        return $q;
                    })->get();
                    
        $freightTomorrow = SwiftFreight::where('freight_eta','=',Carbon::now()->addDay()->format('Y-m-d'),'and')
                            ->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,SwiftFreight::TYPE_AIR])
                            ->with(['order','order.workflow'])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })->get();
        

        $this->pageTitle = 'Transit Foreign Calendar';

        $this->data['business_unit'] = $business_unit;
        $this->data['rootURL'] = $this->rootURL;
        $this->data['freightToday'] = $freightToday;
        $this->data['freightTomorrow'] = $freightTomorrow;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));
        
        return $this->makeView('order-tracking/transit_foreign');
    }

    public function getPickupCalendar($business_unit=false)
    {
        if($business_unit === false)
        {
            $business_unit = Session::get('order_tracking_pickup_calendar_business_unit',function(){return false;});
        }
        else
        {
            if($business_unit != 0)
            {
                Session::put('order_tracking_pickup_calendar_business_unit',$business_unit);
            }
            else
            {
                Session::forget('order_tracking_pickup_calendar_business_unit');
                $business_unit = false;
            }
        }

        $freight= SwiftFreight::query();

        $freightToday = $freight->where('freight_eta','=',Carbon::now()->format('Y-m-d'),'and')
                    ->whereFreightType(SwiftFreight::TYPE_LAND)
                    ->with(['order','order.workflow','company','order.shipment'])
                    ->whereHas('order',function($q) use ($business_unit){
                        $q->whereHas('workflow',function($q2){
                            return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                        });
                        if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                        {
                            $q->where('business_unit','=',$business_unit);
                        }
                        return $q;
                    })->get();

        $freightTomorrow = $freight->where('freight_eta','=',Carbon::now()->addDay()->format('Y-m-d'),'and')
                            ->whereFreightType(SwiftFreight::TYPE_LAND)
                            ->with(['order','order.workflow','company','order.shipment'])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })->get();

        $this->pageTitle = 'Pickup Calendar';

        $this->data['business_unit'] = $business_unit;
        $this->data['freightToday'] = $freightToday;
        $this->data['freightTomorrow'] = $freightTomorrow;
        $this->data['rootURL'] = $this->rootURL;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));

        return $this->makeView('order-tracking/transit_local');
    }

    public function getStorageDemurrageCalendar($business_unit=false)
    {
        if($business_unit === false)
        {
            $business_unit = \Session::get('order_tracking_storage_demurrage_calendar_business_unit',function(){return \SwiftOrder::SCOTT_CONSUMER;});
        }
        else
        {
            if($business_unit != 0)
            {
                \Session::put('order_tracking_storage_demurrage_calendar_business_unit',$business_unit);
            }
            else
            {
                \Session::forget('order_tracking_storage_demurrage_calendar_business_unit');
                $business_unit = \SwiftOrder::SCOTT_CONSUMER;
            }
        }

        $storageToday = SwiftStorage::where('storage_start','=',Carbon::now()->format('Y-m-d'),'and')
                        ->with(['order','order.workflow','order.freight'=>function($q){
                            return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                        }])
                        ->whereHas('order',function($q) use ($business_unit){
                            $q->whereHas('workflow',function($q2){
                                return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                            });
                            if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                            {
                                $q->where('business_unit','=',$business_unit);
                            }
                            return $q;
                        })
                        ->get();

        $storageTomorrow = SwiftStorage::where('storage_start','=',Carbon::now()->addDay()->format('Y-m-d'),'and')
                            ->with(['order','order.workflow','order.freight'=>function($q){
                                return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                            }])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })
                            ->get();

        $demurrageToday = SwiftStorage::where('demurrage_start','=',Carbon::now()->format('Y-m-d'),'and')
                            ->with(['order','order.workflow','order.freight'=>function($q){
                                return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                            }])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })
                            ->get();

        $demurrageTomorrow = SwiftStorage::where('demurrage_start','=',Carbon::now()->addDay()->format('Y-m-d'),'and')
                            ->with(['order','order.workflow','order.freight'=>function($q){
                                return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                            }])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })
                            ->get();

        foreach([$storageToday,$storageTomorrow,$demurrageToday,$demurrageTomorrow] as $collection)
        {
            foreach($collection as &$row)
            {
                $row->order->activity = WorkflowActivity::progress($row->order,$this->context);
            }
        }


        $this->pageTitle = 'Storage/Demurrage Calendar';

        $this->data['business_unit'] = $business_unit;
        $this->data['rootURL'] = $this->rootURL;
        $this->data['storageToday'] = $storageToday;
        $this->data['storageTomorrow'] = $storageTomorrow;
        $this->data['demurrageToday'] = $demurrageToday;
        $this->data['demurrageTomorrow'] = $demurrageTomorrow;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));

        return $this->makeView('order-tracking/storage_demurrage');
    }

    public function getActiveCharges($business_unit=false)
    {
        if($business_unit === false)
        {
            $business_unit = \Session::get('order_tracking_active_charges_business_unit',function(){return \SwiftOrder::SCOTT_CONSUMER;});
        }
        else
        {
            if($business_unit != 0)
            {
                \Session::put('order_tracking_active_charges_business_unit',$business_unit);
            }
            else
            {
                \Session::forget('order_tracking_active_charges_business_unit');
                $business_unit = \SwiftOrder::SCOTT_CONSUMER;
            }
        }

        $activeDemurrage = SwiftStorage::whereNotNull('demurrage_start')
                            ->with(['order','order.workflow','order.shipment','order.freight'=>function($q){
                                return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                            }])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })
                            ->remember(60)
                            ->get();

        $activeStorage = SwiftStorage::whereNotNull('storage_start')
                            ->with(['order','order.workflow','order.shipment','order.freight'=>function($q){
                                return $q->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,\SwiftFreight::TYPE_AIR]);
                            }])
                            ->whereHas('order',function($q) use ($business_unit){
                                $q->whereHas('workflow',function($q2){
                                    return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                                });
                                if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                                {
                                    $q->where('business_unit','=',$business_unit);
                                }
                                return $q;
                            })
                            ->remember(60)
                            ->get();

        $totalStorage = $totalDemurrage = 0;

        foreach(['demurrage'=>$activeDemurrage,'storage'=>$activeStorage] as $type => $list)
        {
            foreach($list as &$row)
            {
                $row->order->activity = \WorkflowActivity::progress($row->order,$this->context);
                switch($type)
                {
                    case 'storage':
                        $row->numberOfDays = \Helper::calculateStorageNumberOfDays($row->storage_start);
                        if(count($row->order->shipment))
                        {
                            $row->cost = \Helper::calculateStorageCost($row->storage_start,$row->order->shipment);
                            $totalStorage += $row->cost;
                        }
                        else
                        {
                            $row->cost = "No containers listed";
                        }
                        break;
                    case 'demurrage':
                        $row->numberOfDays = \Helper::calculateDemurrageNumberOfDays($row->demurrage_start);
                        if(count($row->order->shipment))
                        {
                            $row->cost = \Helper::calculateDemurrageCost($row->demurrage_start,$row->order->shipment);
                            $totalDemurrage += $row->cost;
                        }
                        else
                        {
                            $row->cost = "No containers listed";
                        }
                        break;
                }
            }
        }

        //Get latest storage rates
        $this->data['rate'] = \CHCLStorage::getLatestRate();

        //Sort By highest cost first

        $activeDemurrage->sort(function($a,$b){
            if(!is_numeric($b->cost) || !is_numeric($a->cost))
            {
                return 0;
            }
            return $a->cost < $b->cost ? 1 : -1;
        });

        $activeStorage->sort(function($a,$b){
            if(!is_numeric($b->cost) || !is_numeric($a->cost))
            {
                return 0;
            }
            return $a->cost < $b->cost ? 1 : -1;
        });

        $this->data['pageTitle'] = "Active Charges";
        $this->data['totalStorage'] = $totalStorage;
        $this->data['totalDemurrage'] = $totalDemurrage;
        $this->data['activeStorage'] = $activeStorage;
        $this->data['activeDemurrage'] = $activeDemurrage;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));
        $this->data['business_unit'] = $business_unit;
        $this->data['rootURL'] = $this->rootURL;

        return $this->makeView('order-tracking/active_charges');
    }

    /*
     * Private Functions
     */
    
    /*
     * Name: Form
     * Description: Fills in 
     */
    private function form($id,$edit=false)
    {
        $order_id = Crypt::decrypt($id);
        $order = SwiftOrder::getById($order_id);
        if(count($order))
        {
            /*
             * Set Read
             */
            
            if(!Flag::isRead($order))
            {
                Flag::toggleRead($order);
            }
            
            /*
             * Enable Commenting
             */
            $this->enableComment($order);

            /*
             * Enable Subscriptions
             */

            $this->enableSubscription($order);

            /*
             * Accounts Payable
             */

            if(count($order->payable))
            {
                foreach($order->payable as &$acp)
                {
                    $acp->current_activity = WorkflowActivity::progress($acp,'acpayable');
                }
            }
            
            /*
             * Data
             */
            $this->data['activity'] = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight','shipment','document'),$order);
            $this->pageTitle = "{$order->name} (ID: $order->id)";
            $this->data['incoterms'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$incoterms));
            $this->data['freight_type'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$type));
            $this->data['business_unit'] = json_encode(Helper::jsonobject_encode(SwiftOrder::$business_unit));
            $this->data['customs_status'] = json_encode(Helper::jsonobject_encode(SwiftCustomsDeclaration::$status));
            $this->data['shipment_type'] = json_encode(Helper::jsonobject_encode(SwiftShipment::$type));
            $this->data['po_validation'] = json_encode(Helper::jsonobject_encode(SwiftPurchaseOrder::$validation));
            $this->data['po_type'] = json_encode(Helper::jsonobject_encode(SwiftPurchaseOrder::$types));
            $this->data['payable_charges'] = \SwiftACPRequest::$order;
            $this->data['order'] = $order;
            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$orderTrackingTags));
            $this->data['current_activity'] = WorkflowActivity::progress($order,$this->context);
            $this->data['edit'] = $edit;
            $this->data['flag_important'] = Flag::isImportant($order);
            $this->data['flag_starred'] = Flag::isStarred($order);
            $this->data['isAdmin'] = $this->currentUser->hasAccess(array($this->adminPermission));
            $this->data['isCreator'] = $this->currentUser->id == $order->revisionHistory()->orderBy('created_at','ASC')->first()->user_id ? true : false;
            //$this->data['message'] = OrderTracking::smartMessage($this->data);
            
            Helper::saveRecent($order,$this->currentUser);
            
            return $this->makeView('order-tracking/edit');
        }
        else
        {
            return parent::notfound();
        }
    }
    
    /*
     * GET Pages
     */
    public function getCreate()
    {
        //Check Permission
        if($this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission)))
        {
            $this->pageTitle = 'Create';
            return $this->makeView('order-tracking/create');
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getView($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return Redirect::action('OrderTrackingController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess(['ot-view']))
        {
            return $this->form($id,false);
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getEdit($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif($this->currentUser->hasAnyAccess(['ot-view']))
        {
            return Redirect::action('OrderTrackingController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getActivity($id)
    {
        $order_id = Crypt::decrypt($id);
        $order = SwiftOrder::getById($order_id);
        if(count($order))
        {
            $this->data['activity'] = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight','shipment','document'),$order);
            return $this->makeView('order-tracking/edit_activity');
        }
        else
        {
            return parent::notfound();
        }        
    }
    
    public function getInbox()
    {
        $this->pageTitle = 'Inbox';
        
        //Fetch list of inbox items
    }
    
    /*
     * Lists all forms
     */
    public function getForms($type='inprogress',$page=1)
    {
        $limitPerPage = 15;
        $this->pageTitle = 'Forms';
        
        //Check Edit Access
        $this->data['edit_access'] = $this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]);        
        
        //Check user group
        if(!$this->data['edit_access'] && $type='inprogress')
        {
            $type='all';
        }
        
        /*
         * Let's Start Order Query
         */
        $orderquery = SwiftOrder::query();
        
        if($type != 'inprogress')
        {
            //Get node definition list
            $node_definition_result = \SwiftNodeDefinition::getByWorkflowType(SwiftWorkflowType::where('name','=',$this->context)->first()->id)->all();
            $node_definition_list = array();
            foreach($node_definition_result as $v)
            {
                $node_definition_list[$v->id] = $v->label;
            }
            $this->data['node_definition_list'] = $node_definition_list;            
        }
        
        switch($type)
        {
            case 'inprogress':
                $orderquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS); 
                });
                break;
            case 'rejected':
                $orderquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::REJECTED); 
                });
                break;
            case 'completed':
                $orderquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::COMPLETE); 
                });
                break;
            case 'starred':
                $orderquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::STARRED,'AND')->where('user_id','=',$this->currentUser->id,'AND')->where('active','=',SwiftFlag::ACTIVE); 
                });
                break;
            case 'important':
                $orderquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::IMPORTANT,'AND'); 
                });
                break;
            case 'recent':
                $orderquery->join('swift_recent',function($join) use ($orderquery){
                    $join->on('swift_recent.recentable_type','=',DB::raw('"SwiftOrder"'));
                    $join->on('swift_recent.recentable_id','=','swift_order.id');
                })->orderBy('swift_recent.updated_at','DESC')->select('swift_order.*');
                break;
            case 'all':
                $orderquery->orderBy('updated_at','desc');
                break;
        }
        
        //Filters
        if(Input::has('filter'))
        {
            
            if(Session::has('ot_form_filter'))
            {
                $filter = Session::get('ot_form_filter');
            }
            else
            {
                $filter = array();
            }
            
            $filter[Input::get('filter_name')] = Input::get('filter_value');
            
            /*
             * loop & Apply all filters
             */
            foreach($filter as $f_name => $f_val)
            {
                switch($f_name)
                {
                    case 'business_unit':
                        $orderquery->where('business_unit','=',$f_val);
                        break;
                    case 'node_definition_id':
                        $orderquery->whereHas('workflow',function($q) use($f_val){
                           return $q->whereHas('nodes',function($q) use($f_val){
                               return $q->whereRaw("(node_definition_id = $f_val AND user_id=0)");
                           });
                        });
                        break;
                }
            }
            Session::flash('ot_form_filter',$filter);
        }
        else
        {
            Session::forget('ot_form_filter');
        }
        
        
        $orderCount = $orderquery->count();
        if($type != 'inprogress')
        {
            /*
             * If not in progress, we limit rows
             */
            $orderquery->take($limitPerPage);
            if($page > 1)
            {
                $orderquery->offset(($page-1)*$limitPerPage);
            }
        }
        $orders = $orderquery->get();
        
        
        /*
         * Fetch latest history;
         */
        foreach($orders as $k => &$o)
        {
            //Set Current Workflow Activity
            $o->current_activity = WorkflowActivity::progress($o);
            
            //If in progress, we filter
            if($type == 'inprogress')
            {
                $hasAccess = false;
                /*
                 * Loop through node definition and check access
                 */
                foreach($o->current_activity['definition'] as $d)
                {
                    if(NodeActivity::hasAccess($d,SwiftNodePermission::RESPONSIBLE))
                    {
                        $hasAccess = true;
                        break;
                    }
                }
                
                /*
                 * No Access : We Remove order from list
                 */
                if(!$hasAccess)
                {
                    unset($orders[$k]);
                    $orderCount--;
                    continue;
                }
            }
            else
            {
                if(isset($filter) && isset($filter['node_definition_id']))
                {
                    if(!isset($o->current_activity['definition']) || !in_array((int)$filter['node_definition_id'],$o->current_activity['definition']))
                    {
                        unset($orders[$k]);
                        $orderCount--;
                        break;
                    }
                }
            }

           //Set Revision
            $o->revision_latest = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight','shipment','document'),$o);            
                        
            //Set Starred/important
            $o->flag_starred = Flag::isStarred($o);
            $o->flag_important = Flag::isImportant($o);
            $o->flag_read = Flag::isRead($o);

        }
        
        //The Data
        $this->data['type'] = $type;
        $this->data['isAdmin'] = $this->currentUser->hasAccess([$this->adminPermission]);
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess(array($this->createPermission,$this->adminPermission));
        
        $this->data['orders'] = $orders;
        $this->data['count'] = isset($filter) ? $orderCount : SwiftOrder::count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['filter'] = Input::has('filter') ? "?filter=1" : "";
        $this->data['rootURL'] = $this->rootURL;
        
        return $this->makeView('order-tracking/forms');
    }
    
    /*
     * Lists all freight companies
     */
    
    public function getFreightcompany($type='all',$page=1)
    {
        $limitPerPage = 30;
        
        $this->pageTitle = 'Freight Company';  
        
        $companyquery = SwiftFreightCompany::take($limitPerPage)->orderBy('updated_at','desc');
        
        if($page > 1)
        {
            $companyquery->offset(($page-1)*$limitPerPage);
        }
                
        
        switch($type)
        {
            case "local":
                $companyquery->where('type','=',SwiftFreightCompany::LOCAL);
                break;
            case "foreign":
                $companyquery->where('type','=',SwiftFreightCompany::FOREIGN);
                break;
            case "international":
                $companyquery->where('type','=',SwiftFreightCompany::INTERNATIONAL);
                break;
        }
        
        $companies = $companyquery->get();
        
        $this->data['companies'] = $companies;
        $this->data['count'] = $companyquery->count();        
        $this->data['page'] = $page;
        $this->data['type'] = $type;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]);
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);    
        
        return $this->makeView('freight-company/forms');
    }
    
    public function getCreatefreightcompanyform()
    {
        $this->pageTitle = 'Create';
        return $this->makeView('freight-company/create');        
    }
    
    public function postFreightcompanyform()
    {
        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAnyAccess(array($this->adminPermission,$this->editPermission)))
        {
            return parent::forbidden();
        }        
        
        //Saving new freight companies
        $validator = Validator::make(Input::all(),
                    array('name'=>'required',
                          'type'=>array('required','in:'.implode(',',array_keys(SwiftFreightCompany::$type)))
                        )
                );
        
        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $fc = new SwiftFreightCompany(Input::All());
            if($fc->save())
            {
                $fc_id = Crypt::encrypt($fc->id);
                //Success
                echo json_encode(['success'=>1,'url'=>"/order-tracking/freightcompanyform/$fc_id"]);
            }
            else
            {
                echo "";
                return false;                
            }
        }
    }
    
    public function putFreightcompanyform()
    {
        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAnyAccess(array($this->adminPermission,$this->editPermission)))
        {
            return parent::forbidden();
        }        
        
        $fc_id = Crypt::decrypt(Input::get('pk'));
        $fc = SwiftFreightCompany::find($fc_id);
        if(count($fc))
        {
            /*
             * Manual Validation
             */
            
            //Name
            if(Input::get('name') == 'name' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a name',400);
            }
            
            //Business Unit
            if(Input::get('name') == 'type' && !array_key_exists((int)Input::get('value'),SwiftFreightCompany::$type))
            {
                return Response::make('Please select a valid business unit',400);
            }
            
            //Email
            if(Input::get('name') == 'email' && Input::get('value') != "" && filter_var(Input::get('value'), FILTER_VALIDATE_EMAIL))
            {
                return Response::make('Please enter a valid email address',400);
            }
            
            /*
             * Save
             */
            $fc->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
            if($fc->save())
            {
                return Response::make('Success', 200);
            }
            else
            {
                return Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return Response::make('Freight Company not found',404);
        }        
    }
    
    public function deleteFreightcompanyform($fc_id)
    {
        
    }
    
    public function getFreightcompanyform($id)
    {
        $fc_id = Crypt::decrypt($id);
        $fc = SwiftFreightCompany::getById($fc_id);
        if(count($fc))
        {
            $this->data['activity'] = $fc->revisionHistory()->orderBy('created_at','desc')->get()->all();
            $this->pageTitle = "{$fc->name} (ID: $fc->id) ";
            $this->data['type'] = json_encode(Helper::jsonobject_encode(SwiftFreightCompany::$type));
            $this->data['fc'] = $fc;
            $this->data['ticker'] = $fc->freight;
            
            return $this->makeView('freight-company/edit');
        }
        else
        {
            return parent::notfound();
        }        
    }
    
    /*
     * POST Create Form
     */
    
    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->createPermission]) || !NodeActivity::hasStartAccess($this->context))
        {
            return parent::forbidden();
        }
        
        $validator = Validator::make(Input::all(),
                    array('name'=>'required',
                          'business_unit'=>array('required','in:'.implode(',',array_keys(SwiftOrder::$business_unit))),
                          'email'=>'email'
                        )
                );
        
        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $order = new SwiftOrder;
            $order->name = Input::get('name');
            $order->business_unit = Input::get('business_unit');
            $order->description = Input::get('description');
            if($order->save())
            {
                //Start the Workflow
                if(\WorkflowActivity::update($order,$this->context))
                {
                    //Story Relate
                    Queue::push('Story@relateTask',array('obj_class'=>get_class($order),
                                                         'obj_id'=>$order->id,
                                                         'action'=>SwiftStory::ACTION_CREATE,
                                                         'user_id'=>$this->currentUser->id,
                                                         'context'=>get_class($order)));
                    $order_id = Crypt::encrypt($order->id);
                    //Success
                    echo json_encode(['success'=>1,'url'=>"/order-tracking/edit/$order_id"]);
                }
                else
                {
                    return Response::make("Failed to save workflow",400);
                }
            }
            else
            {
                echo "";
                return false;
            }
        }
    }
    
    /*
     * General Info: REST
     */
    public function putGeneralinfo()
    {
        /*
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }
        
        $order_id = Crypt::decrypt(Input::get('pk'));
        $order = SwiftOrder::find($order_id);
        if(count($order))
        {
            /*
             * Manual Validation
             */
            
            //Name
            if(Input::get('name') == 'name' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a name',400);
            }
            
            //Business Unit
            if(Input::get('name') == 'business_unit' && !array_key_exists((int)Input::get('value'),SwiftOrder::$business_unit))
            {
                return Response::make('Please select a valid business unit',400);
            }
            
            /*
             * Save
             */
            $order->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
            if($order->save())
            {
                return Response::make('Success', 200);
            }
            else
            {
                return Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    /*
     * Customs Declaration: REST
     */
    public function putCustomsdeclaration($order_id)
    {
        /*
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        
        /*
         * Manual Validation
         */
        if(count($order))
        {
            switch(Input::get('name'))
            {
                case 'customs_status':
                    if(!array_key_exists(Input::get('value'),SwiftCustomsDeclaration::$status))
                    {
                        return Response::make('Please select a valid status',400);
                    }
                    break;
                case 'customs_reference':
                    if(!is_numeric(Input::get('value')) && Input::get('value') != "")
                    {
                        return Response::make('Please enter a numeric value',400);
                    }
                    break;
            }       

            /*
             * New Customs Declaration
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $customsDeclaration = new SwiftCustomsDeclaration();
                $customsDeclaration->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($order->customsDeclaration()->save($customsDeclaration))
                {
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($customsDeclaration->id),'id'=>$customsDeclaration->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else 
            {
                $customsDeclaration = SwiftCustomsDeclaration::find(Crypt::decrypt(Input::get('pk')));
                if($customsDeclaration)
                {
                    $customsDeclaration->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($customsDeclaration->save())
                    {
                        Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving customs information: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    public function deleteCustomsdeclaration()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $customs_id = Crypt::decrypt(Input::get('pk'));
        $customsDeclaration = SwiftCustomsDeclaration::find($customs_id);
        if(count($customsDeclaration))
        {
            $order_id = $customsDeclaration->order_id;
            if($customsDeclaration->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Customs entry not found',404);
        }
    }
    
    /*
     * Freight: REST
     */
    public function putFreight($order_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        if(count($order))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'freight_type':
                    if(!array_key_exists(Input::get('value'),SwiftFreight::$type))
                    {
                        return Response::make('Please select a valid freight type',400);
                    }
                    break;
                case 'incoterms':
                    if(!array_key_exists(Input::get('value'),SwiftFreight::$incoterms))
                    {
                        return Response::make('Please select a valid incoterm',400);
                    }
                    break;
                case 'freight_etd':
                case 'freight_eta':
                    $d = DateTime::createFromFormat('d/m/Y', Input::get('value'));
                    if($d && $d->format('d/m/Y') != Input::get('value'))
                    {
                        return Response::make('Please enter a valid date',400);
                    }
                    break;
            }       

            /*
             * New Freight
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $freight = new SwiftFreight();
                $freight->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($order->freight()->save($freight))
                {
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($freight->id),'id'=>$freight->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $freight = SwiftFreight::find(Crypt::decrypt(Input::get('pk')));
                if($freight)
                {
                    
                    /*
                     * Manual validation
                     */
                    
                    switch(Input::get('name'))
                    {
                        case "freight_etd":
                            if($freight->freight_eta != "" && 
                                DateTime::createFromFormat("d/m/Y", Input::get('value')) > DateTime::createFromFormat("Y-m-d", $freight->freight_eta))
                            {
                                return Response::make('ETD cannot be more than ETA',400);
                            }
                            break;
                        case "freight_eta":
                            if($freight->freight_etd != "" && 
                                DateTime::createFromFormat("d/m/Y", Input::get('value')) < DateTime::createFromFormat("Y-m-d", $freight->freight_etd))
                            {
                                return Response::make('ETA cannot be less than ETD',400);
                            }
                            break;
                    }                    
                    
                    $freight->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($freight->save())
                    {
                        Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving freight: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    public function deleteFreight()
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $freight_id = Crypt::decrypt(Input::get('pk'));
        $freight = SwiftFreight::find($freight_id);
        if(count($freight))
        {
            $order_id = $freight->order_id;
            if($freight->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Freight entry not found',400);
        }
    }
    
    /*
     * Shipment: REST
     */
    
    public function putShipment($order_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        if(count($order))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'type':
                    if(!array_key_exists(Input::get('value'),SwiftShipment::$type))
                    {
                        return Response::make('Please select a valid shipment type',400);
                    }
                    break;
                case 'volume':
                    if(Input::get('value') != "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter a valid volume',400);
                    }
                    
                    if(is_numeric(Input::get('value')) && (int)Input::get('value') < 0)
                    {
                        return Response::make('Please enter a positive value',400);
                    }
                    break;                    
            }       

            /*
             * New Freight
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $shipment = new SwiftShipment();
                $shipment->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($order->shipment()->save($shipment))
                {
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($shipment->id),'id'=>$shipment->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $shipment = SwiftShipment::find(Crypt::decrypt(Input::get('pk')));
                if($shipment)
                {
                    $shipment->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($shipment->save())
                    {
                        Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving shipment: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deleteShipment()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $shipment_id = Crypt::decrypt(Input::get('pk'));
        $shipment = SwiftShipment::find($shipment_id);
        if(count($shipment))
        {
            if($shipment->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Freight entry not found',400);
        }        
    }
    
    public function putStorage($order_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        
        if(count($order))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'storage_charges':
                case 'demurrage_charges':
                    if(Input::get('value') != "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter only numbers.',400);
                    }
                    
                    if(is_numeric(Input::get('value')) && (int)Input::get('value') < 0)
                    {
                        return Response::make('Please enter a positive value',400);
                    }                    
                    break;
            }

            /*
             * New Freight
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $storage = new SwiftStorage();
                $storage->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($order->storage()->save($storage))
                {
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($storage->id),'id'=>$storage->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $storage = SwiftStorage::find(Crypt::decrypt(Input::get('pk')));
                if($storage)
                {
                    $storage->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($storage->save())
                    {
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving shipment: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deleteStorage()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $storage_id = Crypt::decrypt(Input::get('pk'));
        $storage = SwiftStorage::find($storage_id);
        if(count($storage))
        {
            if($storage->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Storage entry not found',400);
        }        
    }    
    
    /*
     * Purchase Order: REST
     */
    public function putPurchaseorder($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $order = \SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if($order)
        {

            switch(\Input::get('name'))
            {
                case "type":
                    if(!array_key_exists(Input::get('value'),\SwiftPurchaseOrder::$types))
                    {
                        return \Response::make("Please enter a valid type",500);
                    }
                    break;
                case "validated":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make("You don't have permission for this action",500);
                    }
                    break;
                case "reference":
                    if(\Input::get('value') !== "" && !is_numeric(Input::get('value')))
                    {
                        return \Response::make("Please enter a numeric value",500);
                    }
                    break;
            }

            /*
             * New Purchase Order
             */
            if(is_numeric(\Input::get('pk')))
            {
                //All Validation Passed, let's save
                $po = new \SwiftPurchaseOrder();
                $po->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                if($order->purchaseOrder()->save($po))
                {
                    $order->touch();
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($po->id),'id'=>$po->id]));
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $po = \SwiftPurchaseOrder::find(Crypt::decrypt(Input::get('pk')));
                if($po)
                {
                    $po->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                    if($po->save())
                    {
                        $order->touch();
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                        return \Response::make('Success');
                    }
                    else
                    {
                        return \Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return \Response::make('Error saving purchase order: Invalid PK',400);
                }
            }            
        }
        else
        {
            return \Response::make('Order process form not found',404);
        }        
    }
    
    public function deletePurchaseorder()
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $po_id = Crypt::decrypt(Input::get('pk'));
        $po = SwiftPurchaseOrder::find($po_id);
        if(count($po))
        {
            $order_id = $po->order_id;
            if($po->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Purchase order entry not found',404);
        }        
    }
    
    /*
     * Reception: REST
     */
    
    public function putReception($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if(count($order))
        {
            /*
             * New Reception
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $reception = new SwiftReception();
                $reception->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($order->reception()->save($reception))
                {
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($reception->id),'id'=>$reception->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $reception = SwiftReception::find(Crypt::decrypt(Input::get('pk')));
                if($reception)
                {
                    $reception->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($reception->save())
                    {
                        Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($order),'id'=>$order->id,'user_id'=>$this->currentUser->id));
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving purchase order: Invalid PK',400);
                }
            }            
        }        
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deleteReception()
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $reception_id = Crypt::decrypt(Input::get('pk'));
        $reception = SwiftReception::find($reception_id);
        if(count($reception))
        {
            if($reception->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Purchase order entry not found',404);
        }        
    }
    
    /*
     * Mark Items
     */
    public function putMark($type)
    {
        return Flag::set($type,'\SwiftOrder',$this->adminPermission);
    }
    
    /*
     * Upload Document
     */
    
    public function postUpload($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if(count($order))
        {
            if(Input::file('file'))
            {
                $doc = new SwiftDocument();
                $doc->document = Input::file('file');
                if($order->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>Crypt::encrypt($doc->id),
                                    'id_normal'=>$doc->id,
                                    'updated_on'=>$doc->getAttachedFiles()['document']->updatedAt(), 
                                    'updated_by'=>Helper::getUserName($doc->user_id,$this->currentUser)]);
                }
                else
                {
                    return Response::make('Upload failed.',400);
                }
            }
            else
            {
                return Response::make('File not found.',400);
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        } 
    }
    
    /*
     * Delete upload
     */
    
    public function deleteUpload($doc_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $doc = SwiftDocument::find(Crypt::decrypt($doc_id));
        /*
         * Manual Validation
         */
        if(count($doc))
        {
            if($doc->delete())
            {
                echo json_encode(['success'=>1,'url'=>$doc->getAttachedFiles()['document']->url(),'id'=>Crypt::encrypt($doc->id)]);
            }
            else
            {
                return Response::make('Delete failed.',400);
            }
        }
        else
        {
            return Response::make('Document not found',404);
        } 
    }
    
    /*
     * Tags: REST
     */
    
    public function putTag()
    {
        /*
        * Check Permissions
        */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }
        
        if(Input::get('pk') && !is_numeric(Input::get('pk')))
        {
            $doc = SwiftDocument::with('tag')->find(Crypt::decrypt(Input::get('pk')));
            if($doc)
            {
                //Lets check those tags
                if(count($doc->tag))
                {
                    if(Input::get('value'))
                    {
                        //It already has some tags
                        //Save those not in table
                        foreach(Input::get('value') as $val)
                        {
                            $found = false;
                            foreach($doc->tag as $t)
                            {
                                if($t->type == $val)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Save
                            if(!$found)
                            {
                                /*
                                 * Validate dat tag
                                 */
                                if(key_exists($val,SwiftTag::$orderTrackingTags))
                                {
                                    $tag = new SwiftTag(array('type'=>$val));
                                    if(!$doc->tag()->save($tag))
                                    {
                                        return Response::make('Error: Unable to save tags',400);
                                    }
                                }
                            }
                        }

                        //Delete values from table, not in value array

                        foreach($doc->tag as $t)
                        {
                            $found = false;
                            foreach(Input::get('value') as $val)
                            {
                                if($val == $t->type)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Delete
                            if(!$found)
                            {
                                if(!$t->delete())
                                {
                                    return Response::make('Error: Cannot delete tag',400);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Delete all existing tags
                        if(!$doc->tag()->delete())
                        {
                            return Response::make('Error: Cannot delete tag',400);
                        }
                    }
                }
                else
                {
                    //Alright, just save then
                    foreach(Input::get('value') as $val)
                    {
                        /*
                         * Validate dat tag
                         */
                        if(key_exists($val,SwiftTag::$orderTrackingTags))
                        {
                            $tag = new SwiftTag(array('type'=>$val));
                            if(!$doc->tag()->save($tag))
                            {
                                return Response::make('Error: Unable to save tags',400);
                            }
                        }
                        else
                        {
                            return Response::make('Error: Invalid tags',400);
                        }
                    }
                }
                Queue::push('WorkflowActivity@updateTask',array('class'=>$doc->document_type,'id'=>$doc->document_id,'user_id'=>$this->currentUser->id));
                return Response::make('Success');
            }
            else
            {
                return Response::make('Error: Document not found',400);
            }
        }
        else
        {
            return Response::make('Error: Document number invalid',400);
        }
    }
    
    /*
     * Cancel Workflow
     */
    
    public function postCancel($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->createPermission]))
        {
            return parent::forbidden();
        }
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        
        if(count($order))
        {
            //Not Admin and not creator
            if(!$this->currentUser->hasAccess($this->adminPermission) && $this->currentUser->id != $order->revisionHistory()->orderBy('created_at','ASC')->first()->user_id)
            {
                return Response::make('Operation not allowed',400);
            }
            
            if(WorkflowActivity::cancel($order))
            {
                return Response::make('Workflow has been cancelled',200);
            }
            else
            {
                return Response::make('Unable to cancel workflow: Save failed',400);
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    /*
     * Transit Calendar data by AJAX
     */
    
    public function postTransitcalendarforeign($business_unit=false)
    {
        $startdate = gmdate("Y-m-d",Input::get('start'));
        $enddate = gmdate("Y-m-d",Input::get('end'));
        
        $freight = SwiftFreight::query();
        
        $freight->where('freight_eta','>=',$startdate,'and')
                    ->where('freight_eta','<=',$enddate,'and')
                    ->whereIn('freight_type',[\SwiftFreight::TYPE_SEA,SwiftFreight::TYPE_AIR])
                    ->with(['order','order.workflow'])
                    ->whereHas('order',function($q) use ($business_unit){
                        $q->whereHas('workflow',function($q2){
                            return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                        });
                        if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                        {
                            $q->where('business_unit','=',$business_unit);
                        }    
                        return $q;
                    });
        
        $freightResult = $freight->get()->all();
        
        if(count($freightResult))
        {
            $freightresponse = array();
            foreach($freightResult as $f)
            {
                switch($f->order->business_unit)
                {
                    case SwiftOrder::SCOTT_CONSUMER:
                        $className = "bg-color-orange";
                        break;
                    case SwiftOrder::SCOTT_HEALTH;
                        $className = "bg-color-green";
                        break;
                    case SwiftOrder::SEBNA:
                        $className = "bg-color-blue";
                        break;
                }
                
                switch($f->freight_type)
                {
                    case SwiftFreight::TYPE_AIR:
                        $vesselIcon = '<i class="fa fa-lg fa-plane" title="air"></i>';
                        break;
                    case SwiftFreight::TYPE_LAND:
                        $vesselIcon = '<i class="fa fa-lg fa-truck" title="land"></i>';
                        break;
                    case SwiftFreight::TYPE_SEA:
                        $vesselIcon = '<i class="fa fa-lg fa-anchor" title="sea"></i>';
                        break;
                    default:
                        $vesselIcon = '<i class="fa fa-lg fa-question" title="unknown"></i>';  
                        break;
                }
                
                $freightresponse[] = array(
                                        'title'=>$f->order->name." (ID: ".$f->order->id.")",
                                        'allDay'=>true,
                                        'start'=>strtotime($f->freight_eta),
                                        'url'=>'/order-tracking/view/'.Crypt::encrypt($f->order->id),
                                        'className'=> $className." pjax",
                                        'vesselVoyage' => ($f->vessel_voyage!="" ? $f->vessel_voyage : '<i class="fa fa-question"></i>'),
                                        'vesselName' => ($f->vessel_name!="" ? $f->vessel_name : '<i class="fa fa-question"></i>'),
                                        'vesselIcon' => $vesselIcon,
                                    );
            }
            
            return Response::json($freightresponse);
        }
        else
        {
            return Response::make("");
        }
    }

    public function postTransitcalendarlocal($business_unit=false)
    {
        $startdate = gmdate("Y-m-d",Input::get('start'));
        $enddate = gmdate("Y-m-d",Input::get('end'));

        $freight = SwiftFreight::query();

        $freight->where('freight_eta','>=',$startdate,'and')
                    ->where('freight_eta','<=',$enddate,'and')
                    ->where('freight_type','=',\SwiftFreight::TYPE_LAND)
                    ->with(['order','order.workflow','company'])
                    ->whereHas('order',function($q) use ($business_unit){
                        $q->whereHas('workflow',function($q2){
                            return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                        });
                        if($business_unit !== false && array_key_exists($business_unit,\SwiftOrder::$business_unit))
                        {
                            $q->where('business_unit','=',$business_unit);
                        }
                        return $q;
                    });

        $freightResult = $freight->get()->all();

        if(count($freightResult))
        {
            $freightresponse = array();
            foreach($freightResult as $f)
            {
                switch($f->order->business_unit)
                {
                    case SwiftOrder::SCOTT_CONSUMER:
                        $className = "bg-color-orange";
                        break;
                    case SwiftOrder::SCOTT_HEALTH;
                        $className = "bg-color-green";
                        break;
                    case SwiftOrder::SEBNA:
                        $className = "bg-color-blue";
                        break;
                }

                switch($f->freight_type)
                {
                    case SwiftFreight::TYPE_AIR:
                        $vesselIcon = '<i class="fa fa-lg fa-plane" title="air"></i>';
                        break;
                    case SwiftFreight::TYPE_LAND:
                        $vesselIcon = '<i class="fa fa-lg fa-truck" title="land"></i>';
                        break;
                    case SwiftFreight::TYPE_SEA:
                        $vesselIcon = '<i class="fa fa-lg fa-anchor" title="sea"></i>';
                        break;
                    default:
                        $vesselIcon = '<i class="fa fa-lg fa-question" title="unknown"></i>';
                        break;
                }

                $freightresponse[] = array(
                                        'title'=>$f->order->name." (ID: ".$f->order->id.")",
                                        'allDay'=>true,
                                        'start'=>strtotime($f->freight_eta),
                                        'url'=>'/order-tracking/view/'.Crypt::encrypt($f->order->id),
                                        'className'=> $className." pjax",
                                        'freightCompany' => $f->freight_company,
                                        'vesselIcon' => $vesselIcon,
                                    );
            }

            return Response::json($freightresponse);
        }
        else
        {
            return Response::make("");
        }
    }

    public function postStorageDemurrage($business_unit=false)
    {
        $startdate = gmdate("Y-m-d",Input::get('start'));
        $enddate = gmdate("Y-m-d",Input::get('end'));

        $storage = SwiftStorage::query();
        $demurrage = SwiftStorage::query();

        $storage->where('storage_start','>=',$startdate,'AND')
                ->where('storage_start','<=',$enddate,'AND')
                ->with(['order','order.workflow','order.shipment'=>function($q){
                    return $q->whereIn('type',array_keys(\SwiftShipment::$type));
                }])
                ->whereHas('order',function($q) use ($business_unit){
                    $q->whereHas('workflow',function($q2){
                        return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                    });

                    if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                    {
                        $q->where('business_unit','=',$business_unit);
                    }
                    return $q;
                });

        $storageResult = $storage->get()->all();

        $demurrage->where('demurrage_start','>=',$startdate,'AND')
                ->where('demurrage_start','<=',$enddate,'AND')
                ->with(['order','order.workflow','order.shipment'=>function($q){
                    return $q->whereIn('type',array_keys(\SwiftShipment::$type));
                }])
                ->whereHas('order',function($q) use ($business_unit){
                    $q->whereHas('workflow',function($q2){
                        return $q2->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                    });

                    if(array_key_exists($business_unit,\SwiftOrder::$business_unit))
                    {
                        $q->where('business_unit','=',$business_unit);
                    }
                    return $q;
                });

        $demurrageResult = $demurrage->get()->all();

        if(count($storageResult) || count($demurrageResult))
        {
            $response = array();
            foreach(['storage'=>$storageResult,'demurrage'=>$demurrageResult] as $context=>$v)
            {
                foreach($v as $row)
                {

                    switch($context)
                    {
                        case 'storage':
                            $bgColor = "bg-color-blue";
                            break;
                        case 'demurrage':
                            $bgColor = "bg-color-red";
                            break;
                    }

                    $shipment = "";
                    $shipArray = [];
                    foreach($row->order->shipment as $ship)
                    {
                        if(!isset($shipArray[$ship->getTypeRevisionAttribute($ship->type)]))
                        {
                            $shipArray[$ship->getTypeRevisionAttribute($ship->type)] = 1;
                        }
                        else
                        {
                            $shipArray[$ship->getTypeRevisionAttribute($ship->type)] += 1;
                        }
                    }

                    arsort($shipArray);

                    foreach($shipArray as $k => $v)
                    {
                        if($shipment === "")
                        {
                            $shipment = "{$v} x $k";
                        }
                        else
                        {
                            $shipment .= ", {$v} x $k";
                        }
                    }

                    $response[] = [
                        'title' => $row->order->name." (ID: ".$row->order->id.")".($shipment !== "" ? "\n $shipment" : ""),
                        'allDay' => true,
                        'start' => strtotime($context === "storage" ? $row->storage_start : $row->demurrage_start),
                        'url' => Helper::generateURL($row->order),
                        'className' => "$bgColor pjax",
                        'progress' => \WorkflowActivity::progress($row->order,$this->context)['label']
                    ];
                }
            }

            return Response::json($response);
        }
        else
        {
            return Response::make("");
        }
    }
    
    /*
     * Help : AJAX
     */
    
    public function getHelp($id)
    {
        /*
        * Check Permissions
        */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return "You don't have access to this resource.";
        }
        
        $needPermission = true;
        
        if($this->currentUser->hasAccess($this->adminPermission))
        {
            $needPermission = false;
        }
        
        $form = SwiftOrder::find(Crypt::decrypt($id));
        if(count($form))
        {
            return WorkflowActivity::progressHelp($form,$needPermission);
        }
        else
        {
            return "We can't find the resource that you were looking for.";
        }
    }
    
    public function getLateNodes()
    {
        $this->data['late_node_forms'] = WorkflowActivity::lateNodeByForm('order-tracking');
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes('order-tracking');
        
        echo View::make('workflow/overview_latenodes',$this->data)->render();
    }
    
    public function getPendingNodes()
    {
        $this->data['pending_node_activity'] = WorkflowActivity::statusByType('order-tracking');
        
        echo View::make('workflow/overview_pendingnodes',$this->data)->render();
    }
    
    public function getStories($business_unit=0)
    {
        $this->data['stories'] = Story::fetch(Config::get('context')[$this->context],10,0,$business_unit > 0 ? array(array('business_unit','=',(int)$business_unit)) : array());
        $this->data['dynamicStory'] = OrderTrackingHelper::dynamicStory($business_unit > 0 ? array(array('business_unit','=',$business_unit)) : array());
        
        echo View::make('story/chapter',$this->data)->render();
    }
}