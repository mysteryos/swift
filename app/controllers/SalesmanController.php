<?php

class SalesmanController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Salesman";
        $this->rootURL = $this->context = $this->data['context'] = "salesman";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
    }
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->context.'/overview');
    }    
    
    public function getOverview()
    {
        
    }
    
    public function getAdministration($department='all',$page=1)
    {
        $limitPerPage = 15;
        $this->pageTitle = "Administration - ".$department;
        
        $salesmanQuery = SwiftSalesman::query();
        
        /*
         * Filters
         */
        switch($department)
        {
            case "deleted":
                $salesmanQuery->onlyTrashed();
                break;
            case "active":
                break;
            case "all":
            default:
                $salesmanQuery->withTrashed();
                break;
        }
        
        $salesmanCount = $salesmanQuery->count();
        
        /*
         * Pagination
         */
        
        $salesmanQuery->take($limitPerPage);
        if($page > 1)
        {
            $salesmanQuery->offset(($page-1)*$limitPerPage);
        }
        
        /*
         * Relations
         */
        
        $salesmanQuery->with(['department']);
        
        $salesmanList = $salesmanQuery->get();
        
        foreach($salesmanList as $s)
        {
            $s->revision_latest = Helper::getMergedRevision(['client','salesbudget'],$s); 
        }
        
        /*
         * Data
         */
        
        $this->data['canCreate'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->data['edit_access'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->data['count'] = isset($filter) ? $salesmanCount : SwiftSalesman::count();
        $this->data['department'] = $department;
        $this->data['salesmanList'] = $salesmanList;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['rootURL'] = $this->rootURL;
        $this->data['filter'] = "";
        
        return $this->makeView('salesman/lists');
        
    }
    
    public function getView($id)
    {
        if($this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return Redirect::action('SalesmanController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess($this->viewPermission))
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
        if($this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif($this->currentUser->hasAnyAccess($this->viewPermission))
        {
            return Redirect::action('SalesmanController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    private function form($id,$edit=false)
    {
        $salesman_id = Crypt::decrypt($id);
        
        //Returns trashed salesman if superuser.
        $salesman = SwiftSalesman::getById($salesman_id,true);
        
        if(count($salesman))
        {
            /*
             * Enable Commenting
             */
            $this->enableComment($salesman);
            
            If($salesman->deleted_at !== null)
            {
                $this->data['message'] = [['type'=>'warning','msg'=>'Salesman has been deleted on '.$salesman->deleted_at]];
                $this->edit= $this->currentUser->isSuperUser();
            }
            
            $this->pageTitle = $salesman->getReadableName();
            $this->data['edit'] = $edit;
            $this->data['isAdmin'] = $this->currentUser->hasAccess(array($this->adminPermission));
            $this->data['activity'] = Helper::getMergedRevision(['client','salesbudget'],$salesman);
            $this->data['departmentList'] = json_encode(\SwiftSalesmanDepartment::getList($this->currentUser->isSuperUser()));
            $this->data['schemeList']= json_encode(Helper::jsonobject_encode(SwiftSalesCommissionScheme::getAll()));
            $this->data['form'] = $salesman;
            $this->data['rootURL'] = $this->rootURL;
            return $this->makeView('salesman/edit');
        }
        else
        {
            return parent::notfound();
        }        
    }
    
    public function getBudget($selectedDepartment=false)
    {
        $this->pageTitle = "Salesman - Budget";
        
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
        
        $commissionQuery = \SwiftSalesCommissionCalc::query();
        $commissionQuery->with('salesman','salesman.user','budget','scheme');
        
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
        
        //Must have budget
        $commissionQuery->has('budget');
        
        //Date Filter
        
        $dateStart = Input::get('date_start',(new Carbon('first day of last month'))->format('m-Y'));
        $dateEnd = Input::get('date_start',(new Carbon('first day of last month'))->format('m-Y'));
        $dateStart = Carbon::createFromFormat('m-Y',$dateStart)->day(1)->format('Y-m-d');
        $dateEnd = Carbon::createFromFormat('m-Y',$dateEnd)->addMonth()->day(0)->format('Y-m-d');
        
        $commissionQuery->where('date_start','>=',$dateStart)
                        ->where('date_end','<=',$dateEnd,'AND')
                        ->orderBy('salesman_id','ASC');
                
        //Fetch results
        
        $commissionResult = $commissionQuery->get();
        
        $salesmanCommission = array();
        $currentSalesman = 0;
        foreach($commissionResult as $c)
        {
            if($currentSalesman !== $c->salesman_id)
            {
                $currentSalesman = $c->salesman_id;
                $salesmanCommission[$c->salesman_id]['salesman'] = $c->salesman;
            }
            
            $salesmanCommission[$c->salesman_id]['chart'][] = ['scheme_name'=>$c->scheme->name,
                                                        'budget'=>$c->budget->value,
                                                        'actual'=>$c->total];
        }
        
        $this->data['salesmanCommission'] = $salesmanCommission;
        $this->data['datePeriodStart'] = $dateStart;
        $this->data['datePeriodEnd'] = $dateEnd;

        return $this->makeView('salesman/budget');
    }
    
    public function getCreate()
    {
        //Check Permission
        if($this->currentUser->hasAnyAccess(array($this->adminPermission)))
        {
            $this->pageTitle = 'Create';
            $currentSalesmanIds = SwiftSalesman::select('user_id')
                                        ->distinct()->get()
                                        ->toArray();
            
            $userQuery = User::query()->whereActivated(1)->where('id','!=',$this->currentUser->id,'AND');
            
            if(count($currentSalesmanIds))
            {
                $userQuery->whereNotIn('id',array_map(function($v){
                    return $v['user_id'];
                },$currentSalesmanIds));
            }
            
            $userList = $userQuery->get();
            
            $this->data['userList'] = $userList;
            return $this->makeView('salesman/create');
        }
        else
        {
            return parent::forbidden();
        }        
    }
    
    public function postCreate()
    {
        if(!$this->currentUser->hasAnyAccess(array($this->adminPermission)))
        {
            return parent::forbidden();
        }
        else
        {
            $validator = Validator::make(Input::all(),
                            array('salesman'=>'required')
                        );
            
            if($validator->fails())
            {
                return json_encode(['success'=>0,'errors'=>$validator->errors()]);
            }
            
            $salesman = new SwiftSalesman;
            
            $salesman->user_id = Crypt::decrypt(Input::get('salesman'));
            $salesman->notes = Input::get('notes',"");
            
            if($salesman->save())
            {
                    //Story Relate
//                    Queue::push('Story@relateTask',array('obj_class'=>get_class($order),
//                                                         'obj_id'=>$order->id,
//                                                         'action'=>SwiftStory::ACTION_CREATE,
//                                                         'user_id'=>$this->currentUser->id,
//                                                         'context'=>get_class($order)));
                    $salesman_id = Crypt::encrypt($salesman->id);
                    //Success
                    echo json_encode(['success'=>1,'url'=>"/$this->rootURL/edit/$salesman_id"]);
            }
            else
            {
                return Response::make("Failed to save salesman",500);
            }            
        }
    }
    
    public function putGeneralinfo()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $salesman = \SwiftSalesman::find(Crypt::decrypt(Input::get('pk')));
        
        if(count($salesman))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'notes':
                    break;
                case 'department_id':
                    if(!array_key_exists(Input::get('value'),\SwiftSalesmanDepartment::getList($this->currentUser->isSuperUser())))
                    {
                        return Response::make('Department doesn\'t exist',400);
                    }
                    break;
                default:
                    return Response::make('Unknown field',400);
                    break;
            }
            
            //All Validation Passed, let's save
            $salesman->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
            if($salesman->save())
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
            return Response::make('Salesman record not found',404);
        }        
    }
    
    public function putClient($salesman_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $salesman = \SwiftSalesman::find(Crypt::decrypt($salesman_id));
        
        if(count($salesman))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'customer_code':
                    if(Input::get('value') != "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter only numbers.',400);
                    }
                    
                    if(is_numeric(Input::get('value')) && (int)Input::get('value') < 0)
                    {
                        return Response::make('Please enter a positive value',400);
                    }
                    break;
                default:
                    return Response::make('Unknown field',400);
                    break;
            }

            /*
             * New Client
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $salesmanClient = new SwiftSalesmanClient();
                $salesmanClient->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($salesman->client()->save($salesmanClient))
                {
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($salesmanClient->id),'id'=>$salesmanClient->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
            }
            else
            {
                $salesmanClient = SwiftSalesmanClient::find(Crypt::decrypt(Input::get('pk')));
                if($salesmanClient)
                {
                    $salesmanClient->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($salesmanClient->save())
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
                    return Response::make('Error saving client: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Salesman record not found',404);
        }        
    }
    
    public function deleteClient()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }        
        
        $id = Crypt::decrypt(Input::get('pk'));
        $row = SwiftSalesmanClient::find($id);
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
            return Response::make('Client entry not found',400);
        }        
    }
    
    public function putBudget($salesman_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $salesman = \SwiftSalesman::find(Crypt::decrypt($salesman_id));
        
        if(count($salesman))
        {
            //Validation
            switch(Input::get('name'))
            {
                case 'date':
                    if(!date_parse_from_format('m-Y', Input::get('value')))
                    {
                        return Response::make('Please input a valid date (m-Y)',400);
                    }
                    break;
                case 'value':
                    $num = (new \NumberFormatter('en_US',NumberFormatter::DECIMAL))->parse(trim(Input::get('value')));
                    if($num === false)
                    {
                        return Response::make('Please input a valid value for budget',400);
                    }
                    break;
                case 'scheme_id':
                    if(!\SwiftSalesCommissionScheme::find(Input::get('value')))
                    {
                        return Response::make('Please select a valid scheme',400);
                    }
                    break;
                default:
                    return Response::make('Unknown Field',400);
                    break;
            }
            
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $budget = new SwiftSalesCommissionBudget();
                switch(Input::get('name'))
                {
                    case 'date':
                        $budget->date_start = Carbon::createFromFormat('m-Y',Input::get('value'))->day(1);
                        $budget->date_end = Carbon::createFromFormat('m-Y',Input::get('value'))->addMonth()->day(0);
                        break;
                    case 'value':
                        $budget->{Input::get('name')} = Input::get('value') == "" ? null : (new \NumberFormatter('en_US',NumberFormatter::DECIMAL))->parse(trim(Input::get('value')));
                        break;
                    default:
                        $budget->{Input::get('name')} = Input::get('value');
                        break;
                }
                
                if($salesman->salesbudget()->save($budget))
                {
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($budget->id),'id'=>$budget->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }                
            }
            else
            {
                $budget = SwiftSalesCommissionBudget::find(Crypt::decrypt(Input::get('pk')));
                if($budget)
                {
                    /*
                     * Validation
                     */
                    
                    //Duplicate months
//                    $budgetDuplicates = SwiftSalesCommissionBudget::whereSalesmanId($salesman->id)
//                                        ->where('date_end','>',Carbon::createFromFormat('m-Y',Input::get('value'))->day(1),'AND')
//                                        ->where('date_start','<',Carbon::createFromFormat('m-Y',Input::get('value'))->addMonth()->day(0),'AND')
//                                        ->where('scheme_id','=',(int)$budget->scheme_id)
//                                        ->where('id','!=',$budget->id)
//                                        ->get();
                    
//                    if(count($budgetDuplicates))
//                    {
//                        $budgetIds = implode(", ",array_map(function($v){
//                            return $v['id'];
//                        },$budgetDuplicates->toArray()));
//
//                        return Response::make('Budget period overlaps with IDs: '.$budgetIds,400);
//                    }                    
                    
                    switch(Input::get('name'))
                    {
                        case 'date':
                            $budget->date_start = Carbon::createFromFormat('m-Y',Input::get('value'))->day(1);
                            $budget->date_end = Carbon::createFromFormat('m-Y',Input::get('value'))->addMonth()->day(0);
                            break;
                        case 'value':
                            $budget->{Input::get('name')} = Input::get('value') == "" ? null : (new \NumberFormatter('en_US',NumberFormatter::DECIMAL))->parse(trim(Input::get('value')));
                            break;
                        default:
                            $budget->{Input::get('name')} = Input::get('value');
                            break;
                    }
                    
                    if($budget->save())
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
                    return Response::make('Error saving budget: Invalid PK',400);
                }                
            }
        }
        else
        {
            return Response::make('Salesman record not found',400);
        }
    }
    
    public function deleteBudget()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }        
        
        $id = Crypt::decrypt(Input::get('pk'));
        $row = SwiftSalesCommissionBudget::find($id);
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
            return Response::make('Budget entry not found',400);
        }         
    }
    
    public function postDelete($salesman_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $id = Crypt::decrypt($salesman_id);
        $row = SwiftSalesman::find($id);
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
            return Response::make('Salesman record not found',400);
        }        
    }
    
    public function postRestore($salesman_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission]))
        {
            return parent::forbidden();
        }
        
        $id = Crypt::decrypt($salesman_id);
        $row = SwiftSalesman::onlyTrashed()->find($id);
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
            return Response::make('Salesman record not found',400);
        }        
    }

}