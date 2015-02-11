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
        $this->pageTitle = "Administration - All";
        
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
            $this->data['form'] = $salesman;
            $this->data['rootURL'] = $this->rootURL;
            return $this->makeView('salesman/edit');
        }
        else
        {
            return parent::notfound();
        }        
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