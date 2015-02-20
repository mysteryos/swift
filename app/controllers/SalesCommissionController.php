<?php

class SalesCommissionController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Sales Comission";
        $this->rootURL = $this->data['rootURL'] = $this->context = "sales-commission";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
        $this->keyaccountmanagerPermission = \Config::get("permission.{$this->context}.key-account-manager");
    }
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->context.'/overview');
    }
    
    public function getOverview()
    {
        //Stats
        
    }
    
    /*
     * Commission Info: Start
     */
    
    public function getCommissionCalc()
    {
        SalesCommission::calculatePerSalesman(1,(new Carbon('first day of last month'))->subMonth(), (new Carbon('last day of last month'))->subMonth(),true);
    }
    
    public function getCommissionOverview($selectedDepartment=false)
    {
        //SalesCommission::calculatePerSalesman(1,(new Carbon('first day of last month')), (new Carbon('last day of last month')),true);
        $this->pageTitle = "Commision - Overview";
        
        //Get list of authorized departments to view
        if($this->currentUser->isSuperUser() || $this->currentUser->hasAccess($this->adminPermission))
        {
            $superUser = true;
            $departmentList = SwiftSalesmanDepartment::all();
        }
        else
        {
            $superUser = false;
            $departmentList = SwiftSalesmanDepartment::whereHas('permission',function($q){
                                return $q->whereIn('permission',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                              })->get();
                              
            if(!count($departmentList))
            {
                return parent::forbidden();
            }
        }
        
        $commissionQuery = \SwiftSalesCommissionCalc::query()->with('salesman','salesman.user');
        
        //Filter by Department
        if($selectedDepartment !== false)
        {
            if(!$superUser)
            {
                //Check access again
                $departmentCheck = SwiftSalesmanDepartment::whereHas('permission',function($q){
                                    return $q->whereIn('permission',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                                  })->where('id','=',(int)$selectedDepartment)->count();
                                  
                if($departmentCheck === 0)
                {
                    return parent::forbidden();
                }
            }
            
            //Department Filter
            $commissionQuery->whereHas('salesman',function($q) use ($selectedDepartment){
                return $q->whereHas('department',function($q) use ($selectedDepartment){
                    return $q->where('id','=',(int)$selectedDepartment);
                });
            });
        }
        
        /*
         * Last three months worth of commission
         */
        $commissionQuery->groupBy('salesman_id',DB::raw("MONTH(date_start)"),DB::raw("YEAR(date_start)"))
                        ->select(DB::raw('*, SUM(total) as total,MONTH(date_start) as date_month'))
                        ->where('date_start','>=',Carbon::now()->subMonths(3)->day(1))
                        ->orderBy('date_start','DESC');
        
        $lastThreeMonthsCommission = $commissionQuery->get();
        
        $this->data['lastThreeMonthsCommission'] = $lastThreeMonthsCommission;
        $this->data['selectedDepartment'] = $selectedDepartment;
        $this->data['departmentList'] = $departmentList;
        
        return $this->makeView('sales-commission/commission-overview');
    }
    
    public function getCommissionStories($selectedDepartment=false)
    {
        $this->data['stories'] = new \Illuminate\Support\Collection;
        $this->data['dynamicStory'] = false;
        
        echo View::make('story/chapter',$this->data)->render();        
    }
    
    public function getCommissionView($salesman_id,$date_start)
    {
        //check Permission
        
        if($this->currentUser->isSuperUser() || $this->currentUser->hasAccess($this->adminPermission))
        {
            $superUser = true;
        }
        else
        {
            $superUser = false;
            $isAllowed = SwiftSalesman::where('salesman_id','=',$salesman_id)
                            ->whereHas('department',function($q){
                                    return $q->whereIn('permission',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                            })->count();
            
            if($isAllowed === 0)
            {
                return parent::forbidden();
            }
        }
        
        $commissions = SwiftSalesCommissionCalc::where('salesman_id','=',$salesman_id)
                       ->where('date_start','=',Carbon::createFromFormat('Y-m-d',$date_start)->format('Y-m-d'))
                       ->with('scheme','budget','rate','salesman','salesman.department')
                       ->orderBy('created_at','ASC')
                       ->get();
        
        $salesman = SwiftSalesman::withTrashed()->find($salesman_id);
        
        if(count($salesman))
        {
            $this->data['message'][] = ['type'=>'info',
                                        'msg'=>'This represents a snapshot of all information used to calculate the commission of the salesman as at '.$commissions->first()->created_at->toDateTimeString()];
            $this->data['commissions'] = $commissions;
            $this->data['salesman'] = $salesman;
            $this->data['date_start'] = Carbon::createFromFormat('Y-m-d',$date_start)->format('Y-m-d');
            $this->data['salesman_id'] = $salesman->id;
            return $this->makeView('sales-commission/commission-view');
        }
        else
        {
            return parent::notfound();
        }
    }
    
    public function getCommissionDetailCalcView($salesman_id,$date_start)
    {
        //check Permission
        
        if($this->currentUser->isSuperUser() || $this->currentUser->hasAccess($this->adminPermission))
        {
            $superUser = true;
        }
        else
        {
            $superUser = false;
            $isAllowed = SwiftSalesman::where('salesman_id','=',$salesman_id)
                            ->whereHas('department',function($q){
                                    return $q->whereIn('permission',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                            })->count();
            
            if($isAllowed === 0)
            {
                return parent::forbidden();
            }
        }
        
        $commissions = SwiftSalesCommissionCalc::where('salesman_id','=',$salesman_id)
                       ->where('date_start','=',Carbon::createFromFormat('Y-m-d',$date_start)->format('Y-m-d'))
                       ->with(['scheme','budget','rate','salesman.department','product'=>function($q){
                           return $q->orderBy('jde_doc','ASC');
                       },'product.jdeproduct'])
                       ->orderBy('created_at','ASC')
                       ->get();
        
        if(count($commissions))
        {
            $this->data['commissions'] = $commissions;
            echo View::make('sales-commission/commission-view_commission_detailed',$this->data)->render();
        }
        else
        {
            return parent::notfound();
        }        
    }
            
            
    /*
     * Commission Info: End
     */            
    
    /*
     * Scheme: Start
     */
    
    public function getScheme($type='all',$page=1)
    {
        $limitPerPage = 15;
        $this->pageTitle = "Scheme - ".$type;
        
        $query = SwiftSalesCommissionScheme::query();
        
        /*
         * Filters
         */
        switch($type)
        {
            case "deleted":
                $query->onlyTrashed();
                break;
            case "active":
                break;
            case "all":
            default:
                $query->withTrashed();
                break;
        }
        
        $count = $query->count();
        
        /*
         * Pagination
         */
        
        $query->take($limitPerPage);
        if($page > 1)
        {
            $query->offset(($page-1)*$limitPerPage);
        }
        
        $list = $query->get();
        
        foreach($list as $r)
        {
            $r->revision_latest = Helper::getMergedRevision(['rate'],$r);
        }
        
        /*
         * Data
         */
        
        $this->data['canCreate'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->data['edit_access'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->data['count'] = isset($filter) ? $count : SwiftSalesCommissionScheme::count();
        $this->data['type'] = $type;
        $this->data['list'] = $list;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['rootURL'] = $this->rootURL;
        $this->data['filter'] = "";
        
        return $this->makeView('sales-commission/schemelists');
        
    }
    
    public function getCreateScheme()
    {
        //Check Permission
        if($this->currentUser->hasAnyAccess(array($this->adminPermission)))
        {
            $this->pageTitle = 'Create Scheme';
            $this->data['typeList'] = SwiftSalesCommissionScheme::$type;
            return $this->makeView('sales-commission/scheme-create');
        }
        else
        {
            return parent::forbidden();
        }        
    }
    
    public function postCreateScheme()
    {
        if(!$this->currentUser->hasAnyAccess(array($this->adminPermission)))
        {
            return parent::forbidden();
        }
        else
        {
            $validator = Validator::make(Input::all(),
                            array('name'=>'required',
                                  'type'=>array('required','in:'.implode(',',array_keys(SwiftSalesCommissionScheme::$type)))
                            )
                        );
            
            if($validator->fails())
            {
                return json_encode(['success'=>0,'errors'=>$validator->errors()]);
            }
            
            //Check if unique
            $nameCount = SwiftSalesCommissionScheme::where('name','=',Input::get('name'))->count();
            if($nameCount)
            {
                return Response::make("'".Input::get('name')."' already exists. Please enter a unique name.",500);
            }
            
            
            $cat = new SwiftSalesCommissionScheme;
            
            $cat->name = Input::get('name');
            $cat->type = Input::get('type');
            
            if($cat->save())
            {
                    //Story Relate
//                    Queue::push('Story@relateTask',array('obj_class'=>get_class($order),
//                                                         'obj_id'=>$order->id,
//                                                         'action'=>SwiftStory::ACTION_CREATE,
//                                                         'user_id'=>$this->currentUser->id,
//                                                         'context'=>get_class($order)));
                    $cat_id = Crypt::encrypt($cat->id);
                    //Success
                    echo json_encode(['success'=>1,'url'=>"/$this->rootURL/edit-scheme/$cat_id"]);
            }
            else
            {
                return Response::make("Failed to save scheme",500);
            }            
        }
    }
    
    public function getViewScheme($id)
    {
        if($this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return Redirect::action('SalesmanCommisionController@getEditScheme',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess($this->viewPermission))
        {
            return $this->formScheme($id,false);
        }
        else
        {
            return parent::forbidden();
        }
    }

    public function getEditScheme($id)
    {
        if($this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return $this->formScheme($id,true);
        }
        elseif($this->currentUser->hasAnyAccess([$this->viewPermission]))
        {
            return Redirect::action('SalesmanCommisionController@getViewScheme',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    private function formScheme($id,$edit=false)
    {
        $scheme_id = Crypt::decrypt($id);
        
        $scheme = SwiftSalesCommissionScheme::getById($scheme_id,true);
        
        if(count($scheme))
        {
            /*
            * Enable Commenting
            */
           $this->enableComment($scheme);

           if($scheme->deleted_at !== null)
           {
               $this->data['message'][] = ['type'=>'warning','msg'=>'Scheme has been deleted on '.$scheme->deleted_at];
               $this->edit= $this->currentUser->isSuperUser();
           }
           
           $this->data['hasActiveRate'] = false;
           
           //Any rates?
           if(count($scheme->rate))
           {
                foreach($scheme->rate as $r)
                {
                    if($r->isActive)
                    {
                        $this->data['hasActiveRate'] = true;
                        break;
                    }
                }
           }
           
           if($this->data['hasActiveRate'] === false)
           {
               $this->data['message'][] = ['type'=>'warning','msg'=>"Scheme doesn't have any active rate for current period"];
           }
           
           
           
           $this->data['status_list'] = json_encode(Helper::jsonobject_encode(SwiftSalesCommissionSchemeRate::$status));
           $this->data['type_list'] = json_encode(Helper::jsonobject_encode(SwiftSalesCommissionScheme::$type));
           $this->pageTitle = $scheme->getReadableName();
           $this->data['edit'] = $edit;
           $this->data['isAdmin'] = $this->currentUser->hasAccess(array($this->adminPermission));
           $this->data['activity'] = Helper::getMergedRevision(['product'],$scheme);
           $this->data['form'] = $scheme;
           $this->data['rootURL'] = $this->rootURL;
           return $this->makeView('sales-commission/edit-scheme');
        }
        else
        {
            return parent::notfound();
        }
    }
    
    public function putSchemeGeneralinfo()
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $form = SwiftSalesCommissionScheme::find(Crypt::decrypt(Input::get('pk')));
        
        /*
         * Manual Validation
         */
        if(count($form))
        {
            switch(Input::get('name'))
            {
                case 'name':
                    break;
                case 'notes':
                    break;
                case 'type':
                    if(!array_key_exists(Input::get('value'),SwiftSalesCommissionScheme::$type))
                    {
                        return Response::make('Please select a valid type');
                    }
                    break;
                default:
                    return Response::make('Cannot save - Unknown field',400);
                    break;
            }
            
            $form->{Input::get('name')} = Input::get('value');
            
            if($form->save())
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
            return Response::make('Error - Product scheme not found',400);
        }
    }
    
    public function putSchemeProduct($id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $form = SwiftSalesCommissionScheme::find(Crypt::decrypt($id));
        
        /*
         * Manual Validation
         */
        if(count($form))
        {
            switch(Input::get('name'))
            {
                case 'jde_itm':
                    if((int)Input::get('value') <= 0)
                    {
                        return Response::make('Invalid product JDE ID',400);
                    }
                    else
                    {
                        $count = SwiftSalesCommissionSchemeProduct::where('scheme_id','=',$form->id)
                                ->where('jde_itm','=',Input::get('value'),'AND')
                                ->count();
                        if($count > 0)
                        {
                            return Response::make('Product already exists for this scheme',400);
                        }
                    }
                    break;
                default:
                    return Response::make('Cannot save - Unknown field',400);
                    break;
            }
            
            /*
             * New AP Product
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $prod = new SwiftSalesCommissionSchemeProduct();
                $prod->{Input::get('name')} = Input::get('value');
                if($form->product()->save($prod))
                {
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($prod->id),'id'=>$prod->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $prod = SwiftSalesCommissionSchemeProduct::find(Crypt::decrypt(Input::get('pk')));
                if(count($prod))
                {
                    
                    $prod->{Input::get('name')} = Input::get('value');
                    if($prod->save())
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
                    return Response::make('Error saving product: Invalid PK',400);
                }                
            }
        }
        else
        {
            return Response::make('Product Scheme not found',400);
        }
    }
    
    public function deleteSchemeProduct()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $prod = SwiftSalesCommissionSchemeProduct::find(Crypt::decrypt(Input::get('pk')));
        
        if(count($prod))
        {
            if($prod->delete())
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
            return Response::make('JDE order not found',404);
        }        
    }
    
    public function putSchemeRate($id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $form = SwiftSalesCommissionScheme::find(Crypt::decrypt($id));
        
        if(count($form))
        {
            switch(Input::get('name'))
            {
                case 'effective_date_start':
                case 'effective_date_end':
                    if(date_parse_from_format('Y-m-d',Input::get('value'))['error_count'] > 0)
                    {
                        return Response::make('Please input a valid date',400);
                    }
                    break;
                case 'rate':
                    if(!is_numeric(Input::get('value')) || Input::get('value') < 0)
                    {
                        return Response::make('Please input a valid rate',400);
                    }
                    break;
                case 'status':
                    if(!array_key_exists(Input::get('value'),\SwiftSalesCommissionSchemeRate::$status))
                    {
                        return Response::make('Please enter a valid status',400);
                    }
                    if(is_numeric(Input::get('pk')))
                    {
                        return Response::make('Please enter all the other information before activating the rate',400);
                    }
                    break;
                default:
                    return Response::make('Cannot save - Unknown field',400);
                    break;
            }
            
            /*
             * New Rate
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $rate = new SwiftSalesCommissionSchemeRate();
                $rate->{Input::get('name')} = Input::get('value');
                if($form->rate()->save($rate))
                {
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($rate->id),'id'=>$rate->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
            }
            else
            {
                $rate = SwiftSalesCommissionSchemeRate::find(Crypt::decrypt(Input::get('pk')));
                if($rate)
                {
                    //Validate Rate if Set to active
                    if(Input::get('name',"")==='status')
                    {
                        if((int)Input::get('value',0) === SwiftSalesCommissionSchemeRate::ACTIVE)
                        {
                            //Is all Info set?
                            if($rate->effective_date_start === null || $rate->effective_date_end === null || $rate->rate <0)
                            {
                                return Response::make('Please set all required fields before activating rate',400);
                            }

                            //Valid info?

                            if($rate->effective_date_end->diffIndays($rate->effective_date_start,false) > 0)
                            {
                                return Response::make('Effective date start must be less than effective date end',400);
                            }

                            //Interlapping periods
                            $overlappingRatePeriods = SwiftSalesCommissionSchemeRate::where('scheme_id','=',$form->id)
                                                    ->where('effective_date_end','>',$rate->effective_date_start,'AND')
                                                    ->where('effective_date_start','<',$rate->effective_date_end,'AND')
                                                    ->active()
                                                    ->get();
                            if(count($overlappingRatePeriods))
                            {
                                $overlappingRateIds = implode(", ",array_map(function($v){
                                                            return $v['id'];
                                                        },$overlappingRatePeriods->toArray()));
                                return Response::make('Rate active period is overlapping with the following rate IDs: '.$overlappingRateIds,400);
                            }
                        }
                    }
                    else
                    {
                        //Setting other fields when Rate is active
                        if($rate->status === SwiftSalesCommissionSchemeRate::ACTIVE)
                        {
                            return Response::make('Please set status to inactive before changing values',400);
                        }
                    }
                    
                    $rate->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($rate->save())
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
                    return Response::make('Error saving rate: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Scheme not found',400);
        }        
        
    }
    
    public function deleteSchemeRate()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $rate = SwiftSalesCommissionSchemeRate::find(Crypt::decrypt(Input::get('pk')));
        
        if(count($rate))
        {
            if($rate->delete())
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
            return Response::make('Rate not found',404);
        }
    }
    
    public function putSchemeSalesman($id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $form = SwiftSalesCommissionScheme::find(Crypt::decrypt($id));
        
        if(count($form))
        {
            switch(Input::get('name'))
            {
                case 'salesman_id':
                    if(!User::find(Input::get('value')))
                    {
                        return Response::make('Please select a valid user',400);
                    }
                    break;
                default:
                    return Response::make('Unknown Field',400);
                    break;
            }
            
            /*
             * New Rate
             */
            $form->salesman()->attach(Input::get('value'));
            return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt(Input::get('value')),'id'=>Input::get('value')]));
        }
        else 
        {
            return Response::make('Scheme not found',400);
        }
    }
    
    public function deleteSchemeSalesman($scheme_id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $form = SwiftSalesCommissionScheme::find(Crypt::decrypt($scheme_id));
        
        if(count($form))
        {
            if($form->salesman()->detach(Crypt::decrypt(Input::get('pk'))))
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
            return Response::make('Scheme not found',400);
        }
        
    }
    
    public function postDeleteScheme($scheme_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $id = Crypt::decrypt($scheme_id);
        $row = SwiftSalesCommissionScheme::find($id);
        if(count($row))
        {
            if($row->delete())
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
            return Response::make('Scheme record not found',400);
        }        
    }
    
    public function postRestoreScheme($scheme_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $id = Crypt::decrypt($scheme_id);
        $row = SwiftSalesCommissionScheme::onlyTrashed()->find($id);
        if(count($row))
        {
            if($row->restore())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to restore',400);
            }
        }
        else
        {
            return Response::make('Scheme record not found',400);
        }        
    }    
    
    //Checks if period of rate is active after recent activation
    public function getPeriodIsActive($rate_id)
    {
        $rate = SwiftSalesCommissionSchemeRate::find(Crypt::decrypt($rate_id));
        
        if(count($rate))
        {
            if($rate->isActive)
            {
                return Response::make('1');
            }
        }
        
        return Response::make('Not Active');
    }
    
    
    /*
     * Scheme: End
     */    
    
    
}