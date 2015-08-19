<?php

class AccountsPayableController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Accounts Payable";
        $this->context = $this->data['context'] = "acpayable";
        $this->rootURL = $this->data['rootURL'] = "accounts-payable";
        $this->permission = $this->data['permission'] = new \Permission\SwiftACPRequest();
    }

    public function getIndex()
    {
        return \Redirect::to('/'.$this->context.'/overview');
    }

    /*
     * Overview
     */
    
    public function getOverview()
    {
        $this->pageTitle = 'Overview';
        $this->data['inprogress_limit'] = 15;
        $this->data['late_node_forms_count'] = \SwiftNodeActivity::countLateNodes($this->context);
        $this->data['pending_node_count'] = \SwiftNodeActivity::countPendingNodesWithEta($this->context);
        
        $inprogress = $inprogress_important = $inprogress_responsible = $inprogress_important_responsible = array();

        /*
         * Admin can see all
         */
        if($this->permission->isAdmin())
        {
            $inprogress = \SwiftACPRequest::getInProgress($this->data['inprogress_limit']);
            $inprogress_count = \SwiftACPRequest::getInProgressCount();
            $inprogress_important = \SwiftACPRequest::getInProgress(0,true);
            $inprogress = $inprogress->diff($inprogress_responsible);
            $inprogress_important = $inprogress_important->diff($inprogress_important_responsible);
        }

        $inprogress_responsible = \SwiftACPRequest::getInProgressResponsible();
        $inprogress_important_responsible = \SwiftACPRequest::getInProgressResponsible(0,true);

        if(count($inprogress) == 0 || count($inprogress_important) == 0 || count($inprogress_responsible) == 0 || count($inprogress_important_responsible) == 0)
        {
            $this->data['in_progress_present'] = true;
        }
        else
        {
            $this->data['in_progress_present'] = false;
        }

        foreach(array($inprogress,$inprogress_responsible,$inprogress_important,$inprogress_important_responsible) as $typearray)
        {
            foreach($typearray as &$acp)
            {
                $acp->current_activity = \WorkflowActivity::progress($acp);
                $acp->activity = \Helper::getMergedRevision($acp->revisionRelations,$acp);
            }
        }

        /*
         * Data
         */
        $this->data['inprogress'] = $inprogress;
        $this->data['inprogress_responsible'] = $inprogress_responsible;
        $this->data['inprogress_important'] = $inprogress_important;
        $this->data['inprogress_important_responsible'] = $inprogress_important_responsible;

        return $this->makeView('acpayable/overview');
    }

    /*
     * Forms
     *
     * @param boolean|string $type
     * @integer $page
     */
    public function getForms($type=false,$movementOffset=0,$prevOffset=0,$nextOffset=0)
    {
        /*
         * Register Filters
         */
        $this->task()->registerFormFilters();

        $limitPerPage = 3;
        /*
         * Calculate previous offset
         */
        if($nextOffset < $limitPerPage)
        {
            $prevOffset = 0;
        }
        else
        {
            $prevOffset = $nextOffset- $movementOffset;
        }

        if((int)$prevOffset === 0 && (int)$nextOffset === 0)
        {
            $this->data['record_offset_start'] = 1;
        }
        
        $movementOffset=0;

        $this->pageTitle = 'Forms';

        //Check user group
        if($type===false)
        {
            if(!$this->permission->isAdmin())
            {
                //Set defaults
                if($this->permission->isAccountingDept())
                {
                    $type='inprogress';
                }
                else if($this->permission->canCreate())
                {
                    $type='mine';
                }
            }
            else
            {
                $type='all';
            }
        }
        else
        {
            //Creators can have access to their own only.
            if(!$this->permission->isAdmin() && $this->permission->canCreate() && !in_array($type,['mine','starred']))
            {
                $type='mine';
            }
        }

        $acprequestquery = \SwiftACPRequest::query();

        if($type != 'inprogress')
        {
            //Get node definition list
            $node_definition_result = \SwiftNodeDefinition::getByWorkflowTypeName($this->context)->all();
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
                $acprequestquery->whereHas('workflow',function($q){
                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                });
                break;
            case 'rejected':
                $acprequestquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::REJECTED);
                });
                break;
            case 'completed':
                $acprequestquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::COMPLETE);
                });
                break;
            case 'starred':
                $acprequestquery->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::STARRED,'AND')->where('user_id','=',$this->currentUser->id,'AND')->where('active','=',SwiftFlag::ACTIVE);
                });
                break;
            case 'important':
                $acprequestquery->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::IMPORTANT,'AND');
                });
                break;
            case 'mine':
                $acprequestquery->where('owner_user_id','=',$this->currentUser->id);
                break;
            case 'recent':
                $acprequestquery->join('swift_recent',function($join){
                    $join->on('swift_recent.recentable_type','=',DB::raw('"SwiftACPRequest"'));
                    $join->on('swift_recent.recentable_id','=','swift_acp_request.id');
                })->orderBy('swift_recent.updated_at','DESC')->select('swift_acp_request.*');
                break;
            case 'all':
            default:
                $acprequestquery->orderBy('updated_at','desc');
                break;
        }

        //The Filters
        $this->task()->applyFilters($acprequestquery);

        $formsCount = $acprequestquery->count();
        
        $forms = array();

        /*
         * Fetch latest history;
         */
        do
        {
            $resultquery = clone $acprequestquery;
            $resultquery->take($limitPerPage);
            $resultquery->offset($nextOffset);
            
            $result = $resultquery->get();

            //Nothing more in results
            if(count($result) === 0)
            {
                break;
            }

            foreach($result as $k => &$f)
            {
                $nextOffset++;
                if(count($forms) === $limitPerPage)
                {
                    break;
                }
                else
                {
                    $movementOffset++;
                }
                
                //Set Current Workflow Activity
                $f->current_activity = \WorkflowActivity::progress($f);

                //If in progress, we filter
                if($type === 'inprogress')
                {
                    $hasAccess = false;
                    /*
                     * Loop through node definition and check access
                     */
                    if(isset($f->current_activity['definition']))
                    {
                        foreach($f->current_activity['definition'] as $d)
                        {
                            if(\NodeActivity::hasAccess($d,\SwiftNodePermission::RESPONSIBLE))
                            {
                                $hasAccess = true;
                                break;
                            }
                        }
                    }

                    /*
                     * No Access : We Remove order from list
                     */
                    if(!$hasAccess)
                    {
                        $formsCount--;
                        continue;
                    }
                    else
                    {
                        $forms[] = $f;
                    }
                }
                else
                {
                    //check Access
                    if(!$f->permission()->checkAccess())
                    {
                        $formsCount--;
                        continue;
                    }
                    else
                    {
                        $forms[] = $f;
                    }
                }

                //Set Revision
                $f->revision_latest = \Helper::getMergedRevision($f->revisionRelations,$f);

                //Set Starred/important
                $f->flag_starred = \Flag::isStarred($f);
                $f->flag_important = \Flag::isImportant($f);
                $f->flag_read = \Flag::isRead($f);
            }
            
            if(count($forms) >= $limitPerPage)
            {
                break;
            }
            
        } while(count($forms) < $limitPerPage);

        if(!isset($this->data['record_offset_start']))
        {
            $this->data['record_offset_start'] = $nextOffset-$movementOffset;
        }


        //The Data
        $this->data['type'] = $type;
        $this->data['edit_access'] = $this->permission->canEdit() || $this->permission->isAdmin();
        $this->data['activeSuppliers'] = $this->task()->getFormActiveSuppliers(\Input::get('filter_step',false));
        $this->data['activeBillableCompanies'] = $this->task()->getFormBillableCompanyCodes(\Input::get('filter_step',false));
        $this->data['activeSteps'] = \SwiftNodeDefinition::getByWorkflowType(\SwiftWorkflowType::where('name','=',$this->context)->first()->id)->all();
        $this->data['forms'] = $forms;
        $this->data['formCount'] = $formsCount;
        $this->data['prev_offset'] = $prevOffset < 0 ? 0 : $prevOffset;
        $this->data['next_offset'] = $nextOffset;
        $this->data['movement_offset'] = $movementOffset;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['rootURL'] = $this->rootURL;

        return $this->makeView("acpayable/forms");
    }

    /*
     * Payment Voucher Process - Utility Page
     *
     */
    public function getPaymentVoucherProcess()
    {
        if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','document','purchaseOrder','paymentVoucher','invoice','approvalHod','comments'=>function($q){
                        return $q->orderBy('created_at','DESC');
                    }]);

        //Filter by workflow, at payment voucher

        $query->whereHas('workflow',function($q){
            return $q->inprogress()->whereHas('pendingNodes',function($q){
                return $q->whereHas('definition',function($q){
                   return $q->where('name','=','acp_paymentvoucher');
                });
            });
        });

        //order By
        $query->orderBy('created_at','ASC');

        //Filters
        
        if(\Input::has('billable_company'))
        {
            $query->where('billable_company','=',\Input::get('billable_company'));
        }

        if(\Input::has('supplier_code'))
        {
            $query->where('supplier_code','=',\Input::get('supplier_code'));
        }

        if(\Input::has('date'))
        {
            $query->where('created_at','>=',\Carbon::createFromFormat('Y-m-d',\Input::get('date')));
        }
        
        $forms = $query->get();
        $form_count = count($forms);

        if($form_count > 0)
        {
            foreach($forms as &$f)
            {
                if(count($f->paymentVoucher))
                {
                    $f->pv_numbers = $f->paymentVoucher->toJson();
                }
            }
        }

        $this->data['forms'] = $forms;
        $this->data['form_count'] = $form_count;
        $this->data['pageTitle'] = "Payment Voucher Process";
        
        return $this->makeView('acpayable/payment-voucher-process');
    }

    /*
     * Payment Issue Utility Page
     *
     * @param string $type
     * @param integer $page
     */
    public function getPaymentIssue($type='all',$page=1)
    {
        
        if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Register Filters
         */

        $this->task()->registerFilters();

        //Vars
        $limitPerPage = 30;

        $nodeDefinitionName = 'acp_paymentissue';

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','payment','paymentVoucher','invoice'])
                ->whereHas('workflow',function($q) use($nodeDefinitionName){
                    return $q->inprogress()->whereHas('pendingNodes',function($q) use($nodeDefinitionName){
                        return $q->whereHas('definition',function($q) use($nodeDefinitionName){
                           return $q->where('name','=',$nodeDefinitionName);
                        });
                    });
                })
                ->join('swift_acp_invoice','swift_acp_request.id','=','swift_acp_invoice.acp_id')
                ->orderBy('swift_acp_invoice.due_date','ASC');

        switch($type)
        {
            case 'all':
                if(!$this->filter['filter_start_date']['enabled'] && !$this->filter['filter_end_date']['enabled'])
                {
                    $query->has('invoice');
                }
                
                break;
            case 'overdue':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','<',\Helper::previousBusinessDay(Carbon::now())->format('Y-m-d'),'AND');
                });
                break;
            case 'today':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'tomorrow':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'future':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                });
                break;
            case 'nodate':
                $query->whereHas('invoice',function($q){
                    return $q->whereNull('due_date');
                });
                break;
        }

        /*
         * Counts
         */

        $this->task()->getCounts($nodeDefinitionName);

        //The Filters
        $this->task()->applyFilters($query);

        //Form for Display and Count

        $form_count = $query->count();

        //If no Filter - We limit the results
        if(!$this->data['filterActive'])
        {
            $query->take($limitPerPage);
            if($page > 1)
            {
                $query->offset(($page-1)*$limitPerPage);
            }
        }
        
        $forms = $query->get();

        /*
         * Add Payment Type
         */
        foreach($forms as &$f)
        {
            $f->payment_type = 0;
            
            if($f->invoice && $f->invoice->currency_code !== "")
            {
                if($f->invoice->currency_code === "MUR")
                {
                    $f->payment_type = \SwiftACPPayment::TYPE_CHEQUE;
                    continue;
                }
                else
                {
                    $f->payment_type = \SwiftACPPayment::TYPE_BANKTRANSFER;
                    continue;
                }
            }
        }

        $this->data['payment_type'] = \SwiftACPPayment::$type;
        $this->data['chequesign_users'] = \Swift\AccountsPayable\Helper::getChequeSignUserList([$this->permission->accountingChequeSignPermission]);
        $this->data['activeSuppliers'] = $this->task()->getActiveSuppliers($type,$nodeDefinitionName);
        $this->data['activeBillableCompanies'] = $this->task()->getBillableCompanyCodes($type,$nodeDefinitionName);
        $this->data['forms'] = $forms;
        $this->data['count'] = $form_count;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Payment Issue - ".ucfirst($type);
        
        return $this->makeView('acpayable/payment-issue');
    }

    /*
     * Assign Executive for Cheque Sign Utility Page
     *
     * @param string $type
     * @param integer $page
     */
    public function getChequeAssignExec($type='all',$page=1)
    {
        if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Register Filters
         */

        $this->task()->registerFilters();

        //Vars
        $limitPerPage = 30;

        $nodeDefinitionName = 'acp_cheque_assign_exec';

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','payment'=>function($q){
                        return $q->where('status','=',\SwiftACPPayment::STATUS_SIGNED);
                    },'paymentVoucher','invoice'])
                ->whereHas('workflow',function($q) use ($nodeDefinitionName){
                    return $q->inprogress()->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                        return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                           return $q->where('name','=',$nodeDefinitionName);
                        });
                    });
                })
                ->join('swift_acp_invoice','swift_acp_request.id','=','swift_acp_invoice.acp_id')
                ->orderBy('swift_acp_invoice.due_date','ASC');

        switch($type)
        {
            case 'all':
                if(!$this->filter['filter_start_date']['enabled'] && !$this->filter['filter_end_date']['enabled'])
                {
                    $query->has('invoice');
                }
                break;
            case 'overdue':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','<',\Helper::previousBusinessDay(Carbon::now())->format('Y-m-d'),'AND');
                });
                break;
            case 'today':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'tomorrow':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'future':
                //Filter by date end
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                });
                break;
            case 'nodate':
                $query->whereHas('invoice',function($q){
                    return $q->whereNull('due_date');
                });
                break;
        }

        /*
         * Counts
         */

        $this->task()->getCounts($nodeDefinitionName);


        //The Filters

        $this->task()->applyFilters($query);

        //Form for Display and Count

        $form_count = $query->count();

        //If no Filter - We limit the results
        if(!$this->data['filterActive'])
        {
            $query->take($limitPerPage);
            if($page > 1)
            {
                $query->offset(($page-1)*$limitPerPage);
            }
        }
        $forms = $query->get();

        $this->data['exec_users'] = \Swift\AccountsPayable\Helper::getChequeSignUserList([$this->permission->accountingChequeSignExecPermission]);
        $this->data['activeSuppliers'] = $this->task()->getActiveSuppliers($type,$nodeDefinitionName);
        $this->data['activeBillableCompanies'] = $this->task()->getBillableCompanyCodes($type,$nodeDefinitionName);
        $this->data['forms'] = $forms;
        $this->data['count'] = $form_count;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Assign Cheque To Executive - ".ucfirst($type);

        return $this->makeView('acpayable/cheque-assign-exec');
    }

/*
     * Assign Dispatch for Cheque Utility Page
     *
     * @param string $type
     * @param integer $page
     */
    public function getChequeDispatch($type='all',$page=1)
    {
        if(!$this->permission->canDispatchCheque() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Register Filters
         */

        $this->task()->registerFilters();

        //Variables

        $limitPerPage = 30;

        $nodeDefinitionName = 'acp_cheque_ready';

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','payment'=>function($q){
                        return $q->where('status','=',\SwiftACPPayment::STATUS_SIGNED_BY_EXEC);
                    },'paymentVoucher','invoice'])
                ->whereHas('workflow',function($q) use($nodeDefinitionName){
                    return $q->inprogress()->whereHas('pendingNodes',function($q) use($nodeDefinitionName){
                        return $q->whereHas('definition',function($q) use($nodeDefinitionName){
                           return $q->where('name','=',$nodeDefinitionName);
                        });
                    });
                })
                ->join('swift_acp_invoice','swift_acp_request.id','=','swift_acp_invoice.acp_id')
                ->orderBy('swift_acp_invoice.due_date','ASC');

        switch($type)
        {
            case 'all':
                if(!$this->filter['filter_start_date']['enabled'] && !$this->filter['filter_end_date']['enabled'])
                {
                    $query->has('invoice');
                }
                break;
            case 'overdue':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','<',\Helper::previousBusinessDay(Carbon::now())->format('Y-m-d'),'AND');
                });
                break;
            case 'today':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'tomorrow':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'future':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                });
                break;
            case 'nodate':
                $query->whereHas('invoice',function($q){
                    return $q->whereNull('due_date');
                });
                break;
        }

        /*
         * Counts
         */

        $this->task()->getCounts($nodeDefinitionName);

        //The Filters

        $this->task()->applyFilters($query);
        
        //Form for Display and Count

        $form_count = $query->count();

        //If no Filter - We limit the results
        if(!$this->data['filterActive'])
        {
            $query->take($limitPerPage);
            if($page > 1)
            {
                $query->offset(($page-1)*$limitPerPage);
            }
        }
        $forms = $query->get();

        $this->data['dispatch_method'] = \SwiftACPPayment::$dispatch;
        $this->data['activeSuppliers'] = $this->task()->getActiveSuppliers($type,$nodeDefinitionName);
        $this->data['activeBillableCompanies'] = $this->task()->getBillableCompanyCodes($type,$nodeDefinitionName);
        $this->data['forms'] = $forms;
        $this->data['count'] = $form_count;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Cheque Dispatch - ".ucfirst($type);

        return $this->makeView('acpayable/cheque-dispatch');
    }

    /*
     * HOD Approval - Utility Page
     */

    public function getHodApproval()
    {
        if(!$this->permission->isHOD())
        {
            return parent::forbidden();
        }

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['invoice','supplier','company','document','approvalHod'=>function($q){
            return $q->where('approval_user_id','=',$this->currentUser->id);
        }]);

        $query->whereHas('workflow',function($q){
            return $q->inprogress()->whereHas('pendingNodes',function($q){
                return $q->whereHas('definition',function($q){
                   return $q->where('name','=','acp_hodapproval');
                });
            });
        });

        /*
         * Filter approvals by HOD
         */
        if(!$this->currentUser->isSuperUser())
        {
            $query->whereHas('approvalHod',function($q){
                return $q->where('approved','=',\SwiftApproval::PENDING)->where('approval_user_id','=',$this->currentUser->id,'AND');
            });
        }

        //order By
        $query->orderBy('created_at','DESC');

        $forms = $query->get();
        $form_count = count($forms);

        $this->data['forms'] = $forms;
        $this->data['form_count'] = $form_count;
        $this->pageTitle = "HOD Approval";

        return $this->makeView('acpayable/hod-approval');
    }

    /*
     * Cheque Sign - Utility Page
     *
     */
    public function getChequeSign()
    {
        if(!$this->permission->canSignCheque() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Get forms
         */
        $query = \SwiftACPPayment::query();

        //With
        $query->with(['acp','invoice','acp.supplier','acp.company','acp.document','acp.approvalHod']);

        //Filter by workflow, at payment voucher

        $query->whereHas('acp',function($q){
            return $q->whereHas('workflow',function($q){
                return $q->inprogress()->whereHas('pendingNodes',function($q){
                    return $q->whereHas('definition',function($q){
                       return $q->where('name','=','acp_cheque_sign');
                    });
                });
            });
        });

        /*
         * Filter Accounting Employees
         */
        if(!$this->currentUser->isSuperUser() && $this->permission->canSignCheque())
        {
            $query->where('cheque_signator_id','=',$this->currentUser->id);
        }

        //order By
        $query->orderBy('payment_number','ASC');

        $payments = $query->get();
        $payment_count = count($payments);

        $this->data['payments'] = $payments;
        $this->data['payment_count'] = $payment_count;
        $this->data['pageTitle'] = "Cheque Sign";

        return $this->makeView('acpayable/cheque-sign');
    }
    
    /*
     * Cheque Sign By Executive - Utility Page
     *
     */
    public function getChequeSignExec()
    {
        if(!$this->permission->canSignChequeExec() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Get forms
         */
        $query = \SwiftACPPayment::query();

        //With
        $query->with(['acp','invoice','acp.supplier','acp.company','acp.document','acp.approvalHod','acp.paymentVoucher']);

        //Filter by workflow, at payment voucher

        $query->whereHas('acp',function($q){
            return $q->whereHas('workflow',function($q){
                return $q->inprogress()->whereHas('pendingNodes',function($q){
                    return $q->whereHas('definition',function($q){
                       return $q->where('name','=','acp_cheque_sign_by_exec');
                    });
                });
            });
        });

        /*
         * Filter Executive by ID
         */
        if(!$this->currentUser->isSuperUser() && $this->permission->canSignChequeExec())
        {
            $query->where('cheque_exec_signator_id','=',$this->currentUser->id);
        }

        //order By
        $query->orderBy('payment_number','ASC');

        $payments = $query->get();
        $payment_count = count($payments);

        $this->data['payments'] = $payments;
        $this->data['payment_count'] = $payment_count;
        $this->data['pageTitle'] = "Cheque Sign By Executive";

        return $this->makeView('acpayable/cheque-sign-exec');
    }

    public function getPaymentDue($type='all',$page=1)
    {
        if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        /*
         * Register Filters
         */

        $this->task()->registerFilters();

        $limitPerPage = 30;

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','payment','invoice'])
                ->whereHas('workflow',function($q){
                    return $q->inprogress();
                })
                ->join('swift_acp_invoice','swift_acp_request.id','=','swift_acp_invoice.acp_id')
                ->orderBy('swift_acp_invoice.due_date','ASC');

        switch($type)
        {
            case 'all':
                if(!$this->filter['filter_start_date']['enabled'] && !$this->filter['filter_end_date']['enabled'])
                {
                    $query->has('invoice');
                }
                break;
            case 'overdue':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','<',\Helper::previousBusinessDay(Carbon::now())->format('Y-m-d'),'AND');
                });
                break;
            case 'today':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'tomorrow':
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','=',\Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');
                break;
            case 'future':
                //Filter by date end
                $query->whereHas('invoice',function($q){
                    return $q->whereNotNull('due_date')
                          ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                });
                break;
            case 'nodate':
                $query->whereHas('invoice',function($q){
                    return $q->whereNull('due_date');
                });
                break;
        }

        /*
         * Counts
         */

        $this->task()->getCounts();


        //The Filters

        $this->task()->applyFilters($query);

        //Form for Display and Count

        $form_count = $query->count();

        //If no Filter - We limit the results
        if(!$this->data['filterActive'])
        {
            $query->take($limitPerPage);
            if($page > 1)
            {
                $query->offset(($page-1)*$limitPerPage);
            }
        }
        
        $forms = $query->get();

        foreach($forms as &$f)
        {
            $f->current_activity = \WorkflowActivity::progress($f);
        }

        $this->data['activeSuppliers'] = $this->task()->getActiveSuppliers($type);
        $this->data['activeBillableCompanies'] = $this->task()->getBillableCompanyCodes($type);
        $this->data['forms'] = $forms;
        $this->data['count'] = $form_count;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Payment Due - ".ucfirst($type);
        return $this->makeView('acpayable/payment-due');
    }

    public function getCreate()
    {
        $this->pageTitle = 'Create';

        /*
         * Check Permission
         */
        if(!$this->permission->isAdmin() && !$this->permission->canCreate())
        {
            return parent::forbidden();
        }

        return $this->makeView('acpayable/create');
    }

    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!$this->permission->isAdmin() && !$this->permission->canCreate())
        {
            return parent::forbidden();
        }

        $validator = \Validator::make(\Input::all(),
                    [   'billable_company_code'=>['required','numeric'],
                        'supplier_code'=>['required','numeric'],
                        'po_number'=>['numeric'],
                        'po_type' => ['in:'.implode(',',\SwiftPurchaseOrder::$types)],
                    ]
                );

        if($validator->fails())
        {
            return \Response::make($validator->errors(),400);
        }
        else
        {
            $inputData = \Input::all();
            $invoiceExist = new \Illuminate\Database\Eloquent\Collection();
            if($inputData['invoice_number'] !== "")
            {
                //See if invoice number already exists
                $invoiceExist = \SwiftACPRequest::where('billable_company_code','=',\Input::get('billable_company_code'))
                                ->where('supplier_code','=',\Input::get('supplier_code'),'AND')
                                ->whereHas('invoice',function($q) use ($inputData){
                                    return $q->where('number','=',$inputData['invoice_number']);
                                })->whereHas('workflow',function($q){
                                    return $q->inprogress();
                                })->get();
            }
            
            if(count($invoiceExist))
            {
                return \Response::make("Invoice already exists for supplier: <a href='".\Helper::generateUrl($invoiceExist->first())."' class='pjax'>Click here to view form</a>",400);
            }
            else
            {
                $acp = new \SwiftACPRequest();
                $acp->fill($inputData);
                if($acp->save())
                {
                    $invoice = new \SwiftACPInvoice([
                        'date_received' => \Carbon::now()
                    ]);
                    /*
                     * Has invoice number
                     */
                    if(\Input::has('invoice_number') && $inputData['invoice_number'] !== "")
                    {
                        $invoice->number = $inputData['invoice_number'];
                    }

                    $acp->invoice()->save($invoice);

                    /*
                     * Has Purchase Order
                     */

                    if(\Input::has('po_number') && $inputData['po_number'] !== "")
                    {
                        if(\Input::has('po_type') && in_array(\Input::get('po_type'),array_keys(\SwiftPurchaseOrder::$types)))
                        {
                            $purchaseOrder = new \SwiftPurchaseOrder([
                                'reference' => $inputData['po_number'],
                                'type' => $inputData['po_type']
                            ]);
                            $acp->purchaseOrder()->save($purchaseOrder);
                        }
                    }
                    
                    //Start the Workflow
                    if(\WorkflowActivity::update($acp,$this->context))
                    {
                        //Story Relate
                        \Queue::push('Story@relateTask',array('obj_class'=>get_class($acp),
                                                             'obj_id'=>$acp->id,
                                                             'action'=>\SwiftStory::ACTION_CREATE,
                                                             'user_id'=>$this->currentUser->id,
                                                             'context'=>get_class($acp)));
                        $id = \Crypt::encrypt($acp->id);
                        //Success
                        echo json_encode(['success'=>1,'url'=>"/{$this->rootURL}/edit/$id"]);
                    }
                    else
                    {
                        return \Response::make("Failed to save workflow",400);
                    }
                }
                else
                {
                    echo "";
                    return false;
                }
            }
        }
    }

    /*
     * Create Multi Forms from single PDF
     */
    public function getCreateMulti()
    {
        if(!$this->permission->canCreate())
        {
            return parent::forbidden();
        }

        $this->pageTitle = 'Create Multi';

        return $this->makeView('acpayable/create-multi');
    }

    /*
     * 
     */

    public function postMultiPdf()
    {
        if(\Input::has('multi_pdf'))
        {
            $file = \Input::file('document');
            $file_name = $file->getClientOriginalName()."_".microtime();
            if($file->move(storage_path().'/tmp/',$file_name))
            {
                $pdf = new setasign\fpdi\FPDI();
                $pdf->setSourceFile();
                return \Response::json(['file_name'=>$file_name]);
            }
        }
        else
        {
            return \Response::make("Please upload a file.",400);
        }

        return \Response::make("Unable to process your request",500);
    }

    public function getView($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,false);
        }

        if($this->permission->canEdit() || $this->permission->isAdmin())
        {
            return Redirect::action('AccountsPayableController@getEdit',array('id'=>$id));
        }
        elseif($this->permission->canView())
        {
            return $this->form($id,false);
        }
        else
        {
            /*
             * Check Sharing Settings
             */
            
            $className = \Config::get('context.'.$this->context);
            //Check Sharing Settings
            $sharedUser = \SwiftShare::findUserByForm($className,\Crypt::decrypt($id),$this->currentUser->id);

            if($sharedUser)
            {
                //Check Permission
                if($sharedUser->permission === \SwiftShare::PERMISSION_EDIT)
                {
                    return \Redirect::action('AccountsPayableController@getEdit',array('id'=>$id,'override'=>true));
                }
                else
                {
                    return $this->form($id,false);
                }
            }
            else
            {
                return parent::forbidden();
            }
        }
    }

    public function getEdit($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,true);
        }

        if($this->permission->canEdit() || $this->permission->isAdmin())
        {
            return $this->form($id,true);
        }
        elseif($this->permission->canView())
        {
            return \Redirect::action('AccountsPayableController@getView',array('id'=>$id));
        }
        else
        {
            /*
             * Check Sharing Settings
             */
            $className = \Config::get('context.'.$this->context);
            //Check Sharing Settings
            $sharedUser = \SwiftShare::findUserByForm($className,\Crypt::decrypt($id),$this->currentUser->id);

            if($sharedUser)
            {
                //Check Permission
                if($sharedUser->permission === \SwiftShare::PERMISSION_EDIT)
                {
                    return $this->form($id,true);
                }
                else
                {
                    return \Redirect::action('AccountsPayableController@getView',array('id'=>$id,'override'=>true));
                }
            }
            else
            {
                return parent::forbidden();
            }
        }
    }

    private function form($id,$edit=false)
    {
        $acp_id = \Crypt::decrypt($id);
        $acp = \SwiftACPRequest::getById($acp_id);

        if($acp)
        {
            //Check Access
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }

            $acp->encrypted_id = \Crypt::encrypt($acp->id);

            //\Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
            /*
             * Set Read
             */

            if(!\Flag::isRead($acp))
            {
                \Flag::toggleRead($acp);
            }

            /*
             * Enable Commenting
             */
            $this->enableComment($acp);

            //Find HoDs;
            $this->data['approval_hod'] = array();
            $hods = \Sentry::findAllUsersWithAccess(array($this->permission->HODPermission));
            if(count($hods))
            {
                foreach($hods as $h)
                {
                    if($h->activated && !$h->isSuperUser())
                    {
                        $this->data['approval_hod'][$h->id] = $h->first_name." ".$h->last_name;
                    }
                }

                asort($this->data['approval_hod']);
            }

            //Is Related
            if($acp->payable)
            {
                $relatedAcps = $acp->payable->payable()->where('id','!=',$acp->id)->get();
                foreach($relatedAcps as &$r)
                {
                    \WorkflowActivity::progress($r,$this->context);
                }
                $this->data['related_forms'] = $relatedAcps;
            }

            $this->data['current_activity'] = \WorkflowActivity::progress($acp,$this->context);
            $this->data['activity'] = \Helper::getMergedRevision($acp->revisionRelations,$acp);
            $this->pageTitle = $acp->getReadableName();
            $this->data['form'] = $acp;
            $this->data['po_validation'] = json_encode(Helper::jsonobject_encode(SwiftPurchaseOrder::$validation));
            $this->data['cheque_status'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$status));
            $this->data['payment_type'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$type));
            $this->data['cheque_dispatch'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$dispatch));
            $this->data['pv_type'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPaymentVoucher::$typeArray));
            $this->data['pv_validation'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPaymentVoucher::$validationArray));
            $this->data['pay_validation'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$validationArray));
            $this->data['approval_hod'] = json_encode(\Helper::jsonobject_encode($this->data['approval_hod']));
            $this->data['chequesign_users'] = json_encode(\Helper::jsonobject_encode(
                                                \Swift\AccountsPayable\Helper::getChequeSignUserList([$this->permission->accountingChequeSignPermission])
                                            ));
            $this->data['chequesign_exec_users'] = json_encode(\Helper::jsonobject_encode(
                                                        \Swift\AccountsPayable\Helper::getChequeSignUserList([$this->permission->accountingChequeSignExecPermission])
                                                    ));
            $this->data['currency'] = json_encode(\Helper::jsonobject_encode(\Currency::getAll()));
            $this->data['flag_important'] = \Flag::isImportant($acp);
            $this->data['flag_starred'] = \Flag::isStarred($acp);
            $this->data['type_order'] = json_encode(\Helper::jsonobject_encode(\SwiftACPRequest::$order));
            $this->data['po_type'] = json_encode(\Helper::jsonobject_encode(\SwiftPurchaseOrder::$types));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$acpayableTags));
            $this->data['owner'] = \Helper::getUserName($acp->owner_user_id,$this->currentUser);
            $this->data['edit'] = $edit;
            $this->data['publishOwner'] = $this->data['publishAccounting'] = $this->data['addCreditNote'] = $this->data['savePaymentVoucher'] = $this->data['checkPayment'] = $this->data['signCheque'] = false;
            $this->data['isCreator'] = $acp->owner_user_id == $this->currentUser->id;
            
            if($edit === true)
            {
                if($this->data['current_activity']['status'] == \SwiftWorkflowActivity::INPROGRESS)
                {
                    if(!array_key_exists('definition_obj',$this->data['current_activity']))
                    {
                        /*
                         * Detect buggy workflows
                         * Update on the spot
                         */
                        \WorkflowActivity::update($acp);
                    }
                    else
                    {
                        foreach($this->data['current_activity']['definition_obj'] as $d)
                        {
                            if($d->data != "")
                            {
                                if(isset($d->data->publishOwner) && 
                                    ($this->permission->isAdmin() || 
                                     $this->permission->isAccountingDept() ||
                                     $acp->isOwner() ||
                                     $acp->isSharedWith($this->currentUser->id,\SwiftShare::PERMISSION_EDIT_PUBLISH))
                                    )
                                {
                                    $this->data['publishOwner'] = true;
                                    break;
                                }

                                if(isset($d->data->publishAccounting) && ($this->permission->isAdmin() || $this->permission->isAccountingDept()))
                                {
                                    $this->data['publishAccounting'] = true;
                                    break;
                                }

                                if(isset($d->data->addCreditNote))
                                {
                                    $this->data['addCreditNote'] = true;
                                    break;
                                }

                                if(isset($d->data->signCheque))
                                {
                                    $this->data['signCheque'] = true;
                                    break;
                                }

                                if(isset($d->data->savePaymentVoucher) && ($this->permission->isAdmin() || $this->permission->isAccountingDept()))
                                {
                                    $this->data['savePaymentVoucher'] = true;
                                    break;
                                }

                                if(isset($d->data->checkPayment) && ($this->permission->isAdmin() || $this->permission->isAccountingDept()))
                                {
                                    $this->data['checkPayment'] = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                //Check if form has supplier payment terms
                if($this->permission->isAccountingDept() || $this->permission->isAdmin())
                {
                    if($this->data['checkPayment'] === true)
                    {
                        $checkProgress = \WorkflowActivity::checkProgress($acp);
                        if(is_array($checkProgress))
                        {
                            $this->data['message'][] = [ 'type' => 'warning',
                                                         'msg' => 'Validation: '. implode(", ",$checkProgress)
                                                        ];
                        }
                    }
                }
            }
            
            //Save recently viewed form
            \Helper::saveRecent($acp,$this->currentUser);

            return $this->makeView("$this->context/edit");
        }
        else
        {
            return parent::notfound();
        }
    }

    public function putGeneralInfo()
    {
        $acp_id = \Crypt::decrypt(Input::get('pk'));
        $acp = \SwiftACPRequest::find($acp_id);

        if($acp)
        {

            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }

            switch(\Input::get('name'))
            {
                case "name":
                    break;
                case "description":
                    break;
                case "type":
                    if(!array_key_exists(\Input::get('value'),\SwiftACPRequest::$order))
                    {
                        return \Response::make("Please select a valid type",500);
                    }
                    break;
                case "billable_company_code":
                    if(!is_numeric(trim(\Input::get('value'))))
                    {
                        return \Response::make("Company code should be numeric.",400);
                    }
                    break;
                case "supplier_code":
                    if(!is_numeric(trim(\Input::get('value'))))
                    {
                        return \Response::make("Supplier code should be numeric.",400);
                    }
                    break;
                default:
                    return \Response::make("Unknown Field",400);
            }

            $acp->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
            if($acp->save())
            {
                return \Response::make('Success', 200);
            }
            else
            {
                return \Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return parent::notfound();
        }
    }

/*
     * Purchase Order: REST
     */
    public function putPurchaseorder($acp_id)
    {

        $acp = SwiftACPRequest::find(Crypt::decrypt($acp_id));

        /*
         * Manual Validation
         */
        if(count($acp))
        {

            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }

            return Helper::saveChildModel($acp,"\SwiftPurchaseOrder","purchaseOrder",$this->currentUser,true);
        }
        else
        {
            return Response::make('Accounts Payable process form not found',404);
        }
    }

    public function deletePurchaseorder()
    {
        $po_id = Crypt::decrypt(Input::get('pk'));
        $po = SwiftPurchaseOrder::find($po_id);
        if(count($po))
        {
            $acp = $po->purchasable;
            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }
            
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

    public function putCreditnote($acp_id)
    {
        $acp = SwiftACPRequest::find(Crypt::decrypt($acp_id));

        /*
         * Manual Validation
         */
        if(count($acp))
        {

            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }

            /*
             * New Credit Note
             */
            return Helper::saveChildModel($acp,"\SwiftACPCreditNote","creditNote",$this->currentUser,false);
        }
        else
        {
            return parent::notfound();
        }
    }

    public function deleteCreditnote()
    {
        $credit_id = \Crypt::decrypt(Input::get('pk'));
        $credit = \SwiftACPCreditNote::find($credit_id);
        if(count($credit))
        {
            $acp = $credit->acp;
            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }
            
            if($credit->delete())
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
            return Response::make('Credit note entry not found',404);
        }
    }

    public function putInvoice($acp_id)
    {
        $acp = \SwiftACPRequest::find(Crypt::decrypt($acp_id));

        /*
         * Manual Validation
         */
        if(count($acp))
        {
            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }
            
            
            //Validation
            switch(\Input::get('name'))
            {
                case "number":
                    $invoiceExist = new \Illuminate\Database\Eloquent\Collection();
                    if(\Input::get('value') !== "")
                    {
                        //See if invoice number already exists
                        $invoiceExist = \SwiftACPRequest::where('billable_company_code','=',$acp->billable_company_code)
                                        ->where('supplier_code','=',$acp->supplier_code,'AND')
                                        ->where('id','!=',$acp->id,'AND')
                                        ->whereHas('invoice',function($q){
                                            return $q->where('number','=',Input::get('value'));
                                        })->whereHas('workflow',function($q){
                                            return $q->inprogress();
                                        })->get();
                    }

                    if(count($invoiceExist))
                    {
                        return \Response::make("Invoice already exists: <a href='".Helper::generateUrl($invoiceExist->first())."' class='pjax'>Click here to view form</a>",400);
                    }
                    break;
                case "date":
                case "due_date":
                case "gl_code":
                    break;
                case "due_amount":
                case "open_amount":
                    if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
                    {
                        return \Response::make('Please enter a numeric value.',400);
                    }
                    break;
                case "currency_code":
                    if(\Input::get('value') === "")
                    {
                        return \Response::make('Please select a valid currency.',400);
                    }
                    break;
                default:
                    return \Response::make('Unknown Field',400);
                    break;
            }

            /*
             * New Invoice
             */
            return \Helper::saveChildModel($acp,"\SwiftACPInvoice","invoice",$this->currentUser,false);
        }
        else
        {
            return \Response::make('Accounts Payable process form not found',404);
        }
    }

    public function deleteInvoice()
    {
        $id = Crypt::decrypt(Input::get('pk'));
        $invoice = SwiftACPInvoice::find($id);
        if($invoice)
        {
            $acp = $invoice->acp;
            /*
             * Check Permissions
             */
            if(!$acp->permission()->checkAccess())
            {
                return parent::forbidden();
            }
            
            if($invoice->delete())
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
            return Response::make('Invoice entry not found',404);
        }
    }

    public function putPayment($acp_id)
    {
        $acp = \SwiftACPRequest::find(\Crypt::decrypt($acp_id));

        if(count($acp))
        {
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            //Validation
            switch(\Input::get('name'))
            {
                case "type":
                    if(!array_key_exists(\Input::get('value'),\SwiftACPPayment::$type))
                    {
                        return \Response::make('Please enter valid payment type',400);
                    }
                    break;
                case "date":
                case "cheque_dispatch_comment":
                    break;
                case "cheque_signator_id":
                case "cheque_exec_signator_id":
                    if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')) && (int)\Input::get('value') <= 0)
                    {
                        return \Response::make("Please select a valid user",400);
                    }
                    break;
                case "amount":
                case "payment_number":
                case "batch_number":
                    if(\Input::get('value')!== "" && !is_numeric(\Input::get('value')))
                    {
                        return \Response::make('Please enter a numeric value',400);
                    }
                    break;
                case "status":
                    if(!array_key_exists(\Input::get('value'),\SwiftACPPayment::$status))
                    {
                        return \Response::make('Please enter a valid status',400);
                    }
                    break;
                case "cheque_dispatch":
                    if(!array_key_exists(\Input::get('value'), \SwiftACPPayment::$dispatch))
                    {
                        return \Response::make('Please enter a valid dispatch method.',400);
                    }
                    break;
                case "currency_code":
                    if(\Input::get('value') === "")
                    {
                        return \Response::make('Please select a valid currency code',400);
                    }
                    break;
                case "validated":
                case "validated_msg":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make('You don\'t have permission for this action',400);
                    }
                    break;
                default:
                    return \Response::make('Unknown Field',400);
                    break;
            }

            /*
             * New Payment
             */
            if(is_numeric(\Input::get('pk')))
            {

                if(\Input::get('name') !== "type")
                {
                    return \Response::make('Please select type of payment first',400);
                }

                //All Validation Passed, let's save
                $model_obj = new \SwiftACPPayment();
                $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                if(\Input::get('value')===\SwiftACPPayment::TYPE_CHEQUE)
                {
                    $model_obj->status = \SwiftACPPayment::STATUS_ISSUED;
                }
                if($acp->payment()->save($model_obj))
                {
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                    return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($model_obj->id),'id'=>$model_obj->id]));
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }

            }
            else
            {
                $model_obj = SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));
                if($model_obj)
                {
                    $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                    if($model_obj->save())
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
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
        else
        {
            return parent::notfound();
        }
    }

    public function deletePayment()
    {
        $id = Crypt::decrypt(Input::get('pk'));
        $payment = SwiftACPPayment::find($id);
        if($payment)
        {
            $acp = $payment->acp;
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            if($payment->delete())
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
            return Response::make('Payment entry not found',404);
        }
    }

    public function putPaymentvoucher($acp_id)
    {
        $acp = \SwiftACPRequest::find(Crypt::decrypt($acp_id));

        if($acp)
        {
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            //Validation
            switch(\Input::get('name'))
            {
                case "number":
                    if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
                    {
                        return \Response::make("Please input a numeric value.",400);
                    }
                    break;
                case "type":
                    if(\Input::get('value') !== "")
                    {
                        if(!array_key_exists(\Input::get('value'),\SwiftACPPaymentVoucher::$typeArray))
                        {
                            return \Response::make("Please select a valid type.",400);
                        }
                    }
                    break;
                case "validated":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make("You don't have access to this feature.",400);
                    }
                    
                    if(\Input::get('value') !== "" && !array_key_exists((int)\Input::get('value'),\SwiftACPPaymentVoucher::$validationArray))
                    {
                        return \Response::make("Please enter a valid value.",400);
                    }
                    break;
                case "validated_msg":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make("You don't have access to this feature.",400);
                    }
                    break;
                default:
                    return \Response::make('Unknown Field',400);
                    break;
            }

            return \Helper::saveChildModel($acp,"\SwiftACPPaymentVoucher","paymentVoucher",$this->currentUser,true);

        }
        else
        {
            return parent::notfound();
        }
    }

    public function deletePaymentvoucher()
    {
        $id = \Crypt::decrypt(Input::get('pk'));
        $pv = \SwiftACPPaymentVoucher::find($id);
        if($pv)
        {
            $acp = $pv->acp;
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            if($pv->delete())
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
            return Response::make('Payment voucher entry not found',404);
        }
    }

    public function putApprovalHod($acp_id)
    {
        $acp = \SwiftACPRequest::with('approvalHod')->find(Crypt::decrypt($acp_id));

        if($acp)
        {
            if($acp->permission()->checkAccess())
            {
                switch(\Input::get('name'))
                {
                    case "approval_user_id":
                        if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
                        {
                            return \Response::make("Please select a valid user.",400);
                        }
                        foreach($acp->approvalHod as $approval)
                        {
                            if($approval->approval_user_id === \Input::get('value'))
                            {
                                return \Response::make("User already exists as approver. Select another one.",400);
                                break;
                            }
                        }
                        break;
                    case "approved":
                        if(\Input::get('value') !== "" && !array_key_exists(\Input::get('value'),\SwiftApproval::$approved))
                        {
                            return \Response::make("Please select a valid approval status.",400);
                        }
                        if(!$this->permission->isHOD())
                        {
                            return \Response::make("You don't have access to this action",400);
                        }
                        break;
                    default:
                        return \Response::make('Unknown Field',400);
                        break;
                }

                if(is_numeric(\Input::get('pk')))
                {
                    //All Validation Passed, let's save
                    $model_obj = new \SwiftApproval();
                    $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                    $model_obj->type = \SwiftApproval::APC_HOD;
                    if($acp->approval()->save($model_obj))
                    {
                        return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($model_obj->id),'id'=>$model_obj->id]));
                    }
                    else
                    {
                        return \Response::make('Failed to save. Please retry',400);
                    }

                }
                else
                {
                    $model_obj = \SwiftApproval::find(\Crypt::decrypt(\Input::get('pk')));
                    if($model_obj)
                    {
                        $model_obj->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                        if($model_obj->save())
                        {
                            if(Input::get('name')==="approved")
                            {
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
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
            else
            {
                return parent::forbidden();
            }
        }
        else
        {
            return parent::notfound();
        }
    }

    public function deleteApprovalHod()
    {
        $id = \Crypt::decrypt(Input::get('pk'));
        $approval = \SwiftApproval::find($id);
        if($approval)
        {
            $acp = $approval->approvable;
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            if($approval->delete())
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
            return \Response::make('Approval entry not found',404);
        }
    }

    public function postFormapprovalowner($acp_id)
    {
        $id = \Crypt::decrypt($acp_id);
        $acp = \SwiftACPRequest::with('approvalRequester','invoice')->find($id);
        if($acp)
        {
            //Is owner or Is admin
            if($acp->owner_user_id === $this->currentUser->id || $this->permission->isAdmin())
            {
                $workflow_progress = \WorkflowActivity::progress($acp);
                if($workflow_progress['status'] === \SwiftWorkflowActivity::INPROGRESS)
                {
                    if(empty($workflow_progress['definition_obj']))
                    {
                        \WorkflowActivity::update($acp);
                    }

                    foreach($workflow_progress['definition_obj'] as $def)
                    {
                        if(isset($def->data->publishOwner))
                        {
                            if(count($acp->approvalRequester))
                            {
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                                return \Response::make('Form already Published',400);
                            }
                            else
                            {
                                $returnReasonList = array();

                                /*
                                 * Approvals
                                 */
                                if(count($acp->approvalHod) === 0)
                                {
                                    $returnReasonList['hodapproval_absent'] = "Enter HOD's details for approval";
                                }

                                /*
                                 * Documents
                                 */

                                if(count($acp->document) === 0)
                                {
                                    $returnReasonList['document_absent'] = "Upload invoice document";
                                }

                                if(count($returnReasonList) !== 0)
                                {
                                    return Response::make(implode(", ",$returnReasonList),400);
                                }

                                /*
                                 * All great we proceed on
                                 */

                                $approval = new \SwiftApproval([
                                    'approval_user_id' => $this->currentUser->id,
                                    'approved' => \SwiftApproval::APPROVED,
                                    'type'=> \SwiftApproval::APC_REQUESTER
                                ]);

                                $acp->approval()->save($approval);
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                                return \Response::make('Success');
                            }
                            break;
                        }
                    }

                    return \Response::make("You can't publish the form at this time.");
                }
                else
                {
                    return \Response::make('Workflow is either complete or rejected.');
                }
            }
            else
            {
                return parent::forbidden();
            }
        }
        else
        {
            return parent::notfound();
        }
    }

    public function postFormapprovalaccounting($acp_id)
    {
        $id = \Crypt::decrypt($acp_id);
        $acp = \SwiftACPRequest::with('invoice')->find($id);
        if($acp)
        {
            //Is accounting or Admin
            if($this->permission->isAccountingDept() || $this->permission->isAdmin())
            {
                $workflow_progress = \WorkflowActivity::progress($acp);
                if($workflow_progress['status'] === \SwiftWorkflowActivity::INPROGRESS)
                {
                    if(empty($workflow_progress['definition_obj']))
                    {
                        \WorkflowActivity::update($acp);
                    }

                    foreach($workflow_progress['definition_obj'] as $def)
                    {
                        if(isset($def->data->publishAccounting))
                        {
                            $countApproval = $acp->approvalPayment()
                                                ->where('approved','=',\SwiftApproval::PENDING)
                                                ->get();
                            if($countApproval === 0)
                            {
                                //No pending approvals
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                                return \Response::make('Form already Published',400);
                            }
                            else
                            {
                                $progress = \WorkflowActivity::checkProgress($acp);

                                /*
                                 * Only approval remains
                                 */
                                if(is_array($progress) && count($progress) === 1 && array_key_exists("approval_absent",$progress))
                                {
                                    /*
                                     * All great we proceed on
                                     */
                                    $paymentApprovals = $acp->approvalPayment()
                                                        ->where('approved','=',\SwiftApproval::PENDING)
                                                        ->get();

                                    foreach($paymentApprovals as $a)
                                    {
                                        $a->approved = \SwiftApproval::APPROVED;
                                        $a->approval_user_id = $this->currentUser->id;
                                        $a->save();
                                    }

                                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                                    return \Response::make('Success');
                                }
                                else
                                {
                                    if($progress === true)
                                    {
                                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                                        return \Response::make('Success');
                                    }
                                    else
                                    {
                                        return \WorkflowActivity::progressHelp($acp,false);
                                    }
                                }
                            }
                            break;
                        }
                    }

                    return \Response::make("You can't publish the form at this time.",500);
                    
                }
                else
                {
                    return \Response::make('Workflow is either complete or rejected.',500);
                }
            }
            else
            {
                return parent::forbidden();
            }
        }
        else
        {
            return parent::notfound();
        }
    }

    public function putPaymentNumber($form_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        $form = \SwiftACPRequest::with('invoice')->find(\Crypt::decrypt($form_id));
        if($form)
        {
            if(!is_numeric(\Input::get('value')))
            {
                return \Response::make("Batch number should be numeric",400);
            }

            if(is_numeric(\Input::get('pk')))
            {
                $payment = new \SwiftACPPayment([
                    'payment_number' => \Input::get('value'),
                    'status' => \SwiftACPPayment::STATUS_ISSUED
                ]);

                /*
                 * Determine Payment Type
                 */
                if($form->invoice && $form->invoice->currency_code !== "")
                {
                    if($form->invoice->currency_code === "MUR")
                    {
                        $payment->type = \SwiftACPPayment::TYPE_CHEQUE;
                    }
                    else
                    {
                        $payment->type = \SwiftACPPayment::TYPE_BANKTRANSFER;
                    }
                }

                if($form->payment()->save($payment))
                {
                    return \Response::json(['encrypted_id'=>\Crypt::encrypt($payment->id)]);
                }
            }
            else
            {
                $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));
                if($payment)
                {
                    $payment->payment_number = \Input::get('value');
                    if($payment->save())
                    {
                        return \Response::make("Success");
                    }

                }
                else
                {
                    return \Response::make("Payment Entry Not Found",400);
                }
            }
        }
        else
        {
            return \Response::make("Account Payable Form Not Found",404);
        }

        return \Response::make("Unable to process your request",500);
    }

    public function putBatchNumber($form_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        $form = \SwiftACPRequest::with('invoice')->find(\Crypt::decrypt($form_id));
        if($form)
        {
            if(!is_numeric(\Input::get('value')))
            {
                return \Response::make("Batch number should be numeric",400);
            }

            if(is_numeric(\Input::get('pk')))
            {
                $payment = new \SwiftACPPayment([
                    'batch_number' => \Input::get('value'),
                    'status' => \SwiftACPPayment::STATUS_ISSUED
                ]);

                /*
                 * Determine Payment Type
                 */
                if($form->invoice && $form->invoice->currency_code !== "")
                {
                    if($form->invoice->currency_code === "MUR")
                    {
                        $payment->type = \SwiftACPPayment::TYPE_CHEQUE;
                    }
                    else
                    {
                        $payment->type = \SwiftACPPayment::TYPE_BANKTRANSFER;
                    }
                }

                if($form->payment()->save($payment))
                {
                    return \Response::json(['encrypted_id'=>\Crypt::encrypt($payment->id)]);
                }
            }
            else
            {
                $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));
                if($payment)
                {
                    $payment->batch_number = \Input::get('value');
                    if($payment->save())
                    {
                        return \Response::make("Success");
                    }

                }
                else
                {
                    return \Response::make("Payment Entry Not Found",400);
                }
            }
        }
        else
        {
            return \Response::make("Account Payable Form Not Found",404);
        }

        return \Response::make("Unable to process your request",500);
    }
    
    public function putChequeSignatorId($form_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        $form = \SwiftACPRequest::with('invoice')->find(\Crypt::decrypt($form_id));
        if($form)
        {
            if(!is_numeric(\Input::get('value')) || ((int) \Input::get('value') <= 0))
            {
                return \Response::make("Signator Id should be numeric",400);
            }

            if(is_numeric(\Input::get('pk')))
            {
                $payment = new \SwiftACPPayment([
                    'cheque_signator_id' => \Input::get('value'),
                    'status' => \SwiftACPPayment::STATUS_ISSUED
                ]);

                /*
                 * Determine Payment Type
                 */
                if($form->invoice && $form->invoice->currency_code !== "")
                {
                    if($form->invoice->currency_code === "MUR")
                    {
                        $payment->type = \SwiftACPPayment::TYPE_CHEQUE;
                    }
                    else
                    {
                        $payment->type = \SwiftACPPayment::TYPE_BANKTRANSFER;
                    }
                }

                if($form->payment()->save($payment))
                {
                    return \Response::json(['encrypted_id'=>\Crypt::encrypt($payment->id)]);
                }
            }
            else
            {
                $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));
                if($payment)
                {
                    $payment->cheque_signator_id = \Input::get('value');
                    if($payment->save())
                    {
                        return \Response::make("Success");
                    }

                }
                else
                {
                    return \Response::make("Payment Entry Not Found",400);
                }
            }
        }
        else
        {
            return \Response::make("Account Payable Form Not Found",404);
        }

        return \Response::make("Unable to process your request",500);
    }

    public function putPaymentType($form_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        $form = \SwiftACPRequest::with('invoice')->find(\Crypt::decrypt($form_id));
        if($form)
        {
            if(!is_numeric(\Input::get('value')) || array_key_exists((int)\Input::get('value'),\SwiftACPPayment::$type))
            {
                return \Response::make("Type of payment not recognized",400);
            }

            if(is_numeric(\Input::get('pk')))
            {
                $payment = new \SwiftACPPayment([
                    'type' => (int)\Input::get('value')
                ]);

                if($form->payment()->save($payment))
                {
                    return \Response::json(['encrypted_id'=>\Crypt::encrypt($payment->id)]);
                }
            }
            else
            {
                $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));
                if($payment)
                {
                    $payment->type = (int)\Input::get('value');
                    if($payment->save())
                    {
                        return \Response::make("Success");
                    }
                }
                else
                {
                    return \Response::make("Payment Entry Not Found",400);
                }
            }
        }
        else
        {
            return \Response::make("Account Payable Form Not Found",404);
        }

        return \Response::make("Unable to process your request",500);
    }

    public function putChequeExec()
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        if(is_numeric(\Input::get('pk')))
        {
            return \Response::make("Invalid key for payment",500);
        }

        $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));

        if($payment)
        {
            if(!is_numeric(\Input::get('value')))
            {
                return \Response::make("User not recognized",400);
            }

            $payment->cheque_exec_signator_id = (int)\Input::get('value');
            if($payment->save())
            {
                return \Response::make("Success");
            }
        }

        return \Response::make("Unable to process your request",500);
    }

    public function postPaymentExecSign($payment_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        if(is_numeric($payment_id))
        {
            return \Response::make("Invalid key for payment",500);
        }

        $payment = \SwiftACPPayment::with('acp')->find(\Crypt::decrypt($payment_id));

        if($payment)
        {
            if($payment->status === \SwiftACPPayment::STATUS_SIGNED)
            {
                if($payment->cheque_exec_signator_id === null)
                {
                    return \Response::make("Please select a user to sign the cheque.",500);
                }
                
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($payment->acp),'id'=>$payment->acp->id,'user_id'=>$this->currentUser->id));
                return \Response::make("Success");
            }
            else
            {
                return \Response::make("Please make sure the cheque has been signed by accounting dept first.",400);
            }
        }

        return \Response::make("Unable to process your request at this time",500);
    }

    public function postSaveChequeSign()
    {
        if(!$this->permission->isAdmin() && !$this->permission->canSignCheque())
        {
            return parent::forbidden();
        }

        if(\Input::has('pv_id'))
        {
            foreach(\Input::get('pv_id') as $pv_id)
            {
                $paymentId = \Crypt::decrypt($pv_id);

                $payment = \SwiftACPPayment::with('acp')
                            ->where('id','=',$paymentId)
                            ->first();
                
                if($payment && $payment->acp)
                {
                    $payment->status = \SwiftACPPayment::STATUS_SIGNED;
                    $payment->save();
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($payment->acp),'id'=>$payment->acp->id,'user_id'=>$this->currentUser->id));
                }
            }

            return \Response::make("Success");
        }
        else
        {
            return \Response::make("Unable to process your request",400);
        }
    }
    
    public function postSaveChequeSignExec()
    {
        if(!$this->permission->isAdmin() && !$this->permission->canSignChequeExec())
        {
            return parent::forbidden();
        }

        if(\Input::has('pv_id'))
        {
            foreach(\Input::get('pv_id') as $pv_id)
            {
                $paymentId = \Crypt::decrypt($pv_id);

                $payment = \SwiftACPPayment::with('acp')
                            ->where('id','=',$paymentId)
                            ->first();

                if($payment && $payment->acp)
                {
                    $payment->status = \SwiftACPPayment::STATUS_SIGNED_BY_EXEC;
                    $payment->save();
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($payment->acp),'id'=>$payment->acp->id,'user_id'=>$this->currentUser->id));
                }
            }

            return \Response::make("Success");
        }
        else
        {
            return \Response::make("Unable to process your request",400);
        }
    }

    /*
     * Cheque Dispatch Method Save
     * Route: /cheque-dispatch
     */
    public function putChequeDispatch()
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        if(is_numeric(\Input::get('pk')))
        {
            return \Response::make("Invalid key for payment",500);
        }

        $payment = \SwiftACPPayment::find(\Crypt::decrypt(\Input::get('pk')));

        if($payment)
        {
            if(!is_numeric(\Input::get('value')) || !array_key_exists(\Input::get('value'),\SwiftACPPayment::$dispatch))
            {
                return \Response::make("Dispatch method not recognized",400);
            }

            $payment->cheque_dispatch = (int)\Input::get('value');
            if($payment->save())
            {
                return \Response::make("Success");
            }
        }

        return \Response::make("Unable to process your request",500);
    }

    /*
     * Cheque Dispatch Publish Form
     * Route: /cheque-dispatch
     */
    public function postChequeDispatch($payment_id)
    {
        if(!$this->permission->isAdmin() && !$this->permission->isAccountingDept())
        {
            return parent::forbidden();
        }

        $payment = \SwiftACPPayment::with('acp')->find(\Crypt::decrypt($payment_id));

        if($payment)
        {
            if((int)$payment->cheque_dispatch <= 0)
            {
                return \Response::make("Please select a dispatch method for form ID ".$payment->acp->id,500);
            }

            $payment->status = \SwiftACPPayment::STATUS_DISPATCHED;
            if($payment->save())
            {
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($payment->acp),'id'=>$payment->acp->id,'user_id'=>$this->currentUser->id));
                return \Response::make("Success");
            }
        }

        return \Response::make("Unable to process your request",500);
    }

    public function postSaveHodApproval($form_id,$approval_type)
    {
        if(!$this->permission->isHOD())
        {
            return parent::forbidden();
        }

        $form = \SwiftACPRequest::with('approvalHod')->find(\Crypt::decrypt($form_id));

        if($form)
        {
            if(!array_key_exists($approval_type,\SwiftApproval::$approved))
            {
                return \Response::make("Please approve/reject this form",400);
            }

            if(!$this->currentUser->isSuperUser())
            {
                foreach($form->approvalHod as $app)
                {
                    if($app->approval_user_id === $this->currentUser->id)
                    {
                        $app->approved = $approval_type;
                        $app->save();
                    }
                }

                //Save comment
                if((int)$approval_type === \SwiftApproval::REJECTED)
                {
                    $comment = new \SwiftComment([
                        'comment' => 'Rejected: '.\Input::get('comment'),
                        'user_id' => $this->currentUser->id,
                    ]);

                    $form->comments()->save($comment);
                }
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                return \Response::make('Success');
            }
            else
            {
                foreach($form->approvalHod as $app)
                {
                    if($app->approved === \SwiftApproval::PENDING)
                    {
                        $app->approved = $approval_type;
                        $app ->save();
                    }
                }

                //Save comment
                if((int)$approval_type === \SwiftApproval::REJECTED)
                {
                    $comment = new \SwiftComment([
                        'comment' => 'Rejected: '.\Input::get('comment'),
                        'user_id' => $this->currentUser->id,
                    ]);

                    $form->comments()->save($comment);
                }
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                return \Response::make('Success');
            }
        }

        return \Response::make("Unable to process your request",500);
    }

    public function postUpload($id)
    {

        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin() && !$this->permission->canEdit())
        {
            return parent::forbidden();
        }

        $acp = \SwiftACPRequest::find(Crypt::decrypt($id));
        /*
         * Manual Validation
         */
        if(count($acp))
        {
            if(\Input::file('file'))
            {
                $doc = new \SwiftACPDocument();
                $doc->document = \Input::file('file');
                if($acp->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>\Crypt::encrypt($doc->id),
                                    'updated_on'=>$doc->getAttachedFiles()['document']->updatedAt(),
                                    'updated_by'=>\Helper::getUserName($doc->user_id,$this->currentUser)]);
                }
                else
                {
                    return \Response::make('Upload failed.',400);
                }
            }
            else
            {
                return \Response::make('File not found.',400);
            }
        }
        else
        {
            return \Response::make('Accounts Payable form not found',404);
        }
    }

    public function postSupplierUpload($id)
    {
        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin() && !$this->permission->canEdit())
        {
            return parent::forbidden();
        }

        $supplier = \JdeSupplierMaster::find($id);
        /*
        * Manual Validation
         */
        if($supplier)
        {
            if(\Input::file('file'))
            {
                $doc = new \SwiftSupplierDocument();
                $doc->document = \Input::file('file');
                if($supplier->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>\Crypt::encrypt($doc->id),
                                    'updated_on'=>$doc->getAttachedFiles()['document']->updatedAt(),
                                    'updated_by'=>\Helper::getUserName($doc->user_id,$this->currentUser)]);
                }
                else
                {
                    return \Response::make('Upload failed.',400);
                }
            }
            else
            {
                return \Response::make('File not found.',400);
            }
        }
        else
        {
            return \Response::make('Supplier form not found',404);
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
        if(!$this->permission->isAdmin() && !$this->permission->canEdit())
        {
            return parent::forbidden();
        }

        $doc = \SwiftACPDocument::find(\Crypt::decrypt($doc_id));
        /*
         * Manual Validation
         */
        if(count($doc))
        {
            if($doc->delete())
            {
                echo json_encode(['success'=>1,'url'=>$doc->getAttachedFiles()['document']->url(),'id'=>\Crypt::encrypt($doc->id)]);
            }
            else
            {
                return \Response::make('Delete failed.',400);
            }
        }
        else
        {
            return \Response::make('Document not found',404);
        }
    }

    public function putSupplierPaymentTerm($supplier_code)
    {
        $id = \Crypt::decrypt($supplier_code);
        $supplier = \JdeSupplierMaster::find($id);
        if($supplier)
        {
            /*
             * Check Permissions
             */
            if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
            {
                return parent::forbidden();
            }

            //Validation
            switch(\Input::get('name'))
            {
                case "type":
                    if(!array_key_exists(\Input::get('value'),\SupplierPaymentTerm::$typeList))
                    {
                        return \Response::make("Please input a valid value",400);
                    }
                    break;
                case "term_id":
                    if(!\PaymentTerm::find(\Input::get('value')))
                    {
                        return \Response::make("Please input a valid value",400);
                    }
                    break;
                default:
                    return \Response::make('Unknown Field',400);
                    break;
            }

            return \Helper::saveChildModel($supplier,"\SupplierPaymentTerm","paymentTerm",$this->currentUser,false);
        }
        else
        {
            return \Response::make('Supplier not found',500);
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
        if(!$this->permission->isAdmin() && !$this->permission->canEdit())
        {
            return parent::forbidden();
        }

        if(\Input::get('pk') && !is_numeric(\Input::get('pk')))
        {
            $doc = \SwiftACPDocument::with('tag')->find(\Crypt::decrypt(Input::get('pk')));
            if($doc)
            {
                //Lets check those tags
                if(count($doc->tag))
                {
                    if(\Input::get('value'))
                    {
                        //It already has some tags
                        //Save those not in table
                        foreach(\Input::get('value') as $val)
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
                                if(key_exists($val,\SwiftTag::$acpayableTags))
                                {
                                    $tag = new \SwiftTag(array('type'=>$val));
                                    if(!$doc->tag()->save($tag))
                                    {
                                        return \Response::make('Error: Unable to save tags',400);
                                    }
                                }
                            }
                        }

                        //Delete values from table, not in value array

                        foreach($doc->tag as $t)
                        {
                            $found = false;
                            foreach(\Input::get('value') as $val)
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
                                    return \Response::make('Error: Cannot delete tag',400);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Delete all existing tags
                        if(!$doc->tag()->delete())
                        {
                            return \Response::make('Error: Cannot delete tag',400);
                        }
                    }
                }
                else
                {
                    //Alright, just save then
                    foreach(\Input::get('value') as $val)
                    {
                        /*
                         * Validate dat tag
                         */
                        if(key_exists($val,\SwiftTag::$acpayableTags))
                        {
                            $tag = new \SwiftTag(array('type'=>$val));
                            if(!$doc->tag()->save($tag))
                            {
                                return \Response::make('Error: Unable to save tags',400);
                            }
                        }
                        else
                        {
                            return \Response::make('Error: Invalid tags',400);
                        }
                    }
                }
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Error: Document not found',400);
            }
        }
        else
        {
            return \Response::make('Error: Document number invalid',400);
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
        if(!$this->permission->isAdmin() && !$this->permission->canEdit())
        {
            return "You don't have access to this resource.";
        }

        $needPermission = true;

        if($this->permission->isAdmin())
        {
            $needPermission = false;
        }

        $form = \SwiftACPRequest::find(Crypt::decrypt($id));
        if(count($form))
        {
            return \WorkflowActivity::progressHelp($form,$needPermission);
        }
        else
        {
            return "We can't find the resource that you were looking for.";
        }
    }

    /*
     * Cancel Workflow
     */

    public function postCancel($id)
    {

        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        $acp = \SwiftACPRequest::find(Crypt::decrypt($id));

        if(count($acp))
        {
            if(\WorkflowActivity::cancel($acp))
            {
                return \Response::make('Workflow has been cancelled',200);
            }
            else
            {
                return \Response::make('Unable to cancel workflow: Save failed',400);
            }
        }
        else
        {
            return \Response::make('Accounts payable form not found',404);
        }
    }

    /*
     * Mark Items
     */
    public function putMark($type)
    {
        return \Flag::set($type,'SwiftACPRequest',$this->permission->adminPermission);
    }

    /*
     * Suppliers: Start
     */

    public function getSupplierList($filter=false,$page=1)
    {
        if(!$this->permission->canView())
        {
            return parent::forbidden();
        }

        $limitPerPage = 30;

        $this->pageTitle = 'Supplier List';

        $alphabetSoup = $this->data['alphabetSoup'] = range('A','Z');
        if($filter!==false && $filter!=="all" && $filter!=="active")
        {
            $filter = strtoupper($filter);
            if(!in_array($filter,$alphabetSoup))
            {
                return parent::notfound();
            }
        }

        if($filter === false)
        {
            $filter = "all";
        }

        $supplierQuery = \JdeSupplierMaster::query();

        //We have a filter by alphabet
        if(strlen($filter) == 1)
        {
            $supplierQuery->where('Supplier_Name','LIKE',$filter.'%');
            $formsCount = $supplierQuery->count();
        }
        else
        {
            switch($filter)
            {
                case "active":
                    $supplierQuery->has('invoice');
                    $formsCount = $supplierQuery->count();
                    break;
            }
        }

        $supplierQuery->take($limitPerPage);
        if($page > 1)
        {
            $supplierQuery->offset(($page-1)*$limitPerPage);
        }
        
        $forms = $supplierQuery
                 ->orderBy('Supplier_Name','ASC')
                 ->with('invoice')
                 ->get();

        $this->data['filter'] = $filter;
        $this->data['forms'] = $forms;
        $this->data['count'] = $filter === "all" ? JdeSupplierMaster::count() : $formsCount;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['edit_access'] = $this->permission->isHOD() || $this->permission->isAccountingDept();

        return $this->makeView("acpayable/suppliers");
    }

    public function getSupplier($mode,$supplier_code=false)
    {
        if($supplier_code === false)
        {
            return parent::notfound();
        }

        $supplier = \JdeSupplierMaster::with(['acp'=>function($q) {
                        return $q->orderBy('updated_at','DESC');
                    },'document','credit'])
                    ->find($supplier_code);

        if($supplier)
        {
            //Check access to mode
            if($mode=='edit')
            {
                if(!$this->permission->isAccountingDept() && !$this->permission->isAdmin())
                {
                    $mode='view';
                }
            }
            else
            {
                if($this->permission->isAccountingDept() || $this->permission->isAdmin())
                {
                    $mode='edit';
                }
            }

            /*
             * Scott's Total Amount Due
             */

            foreach($supplier->acp as &$acp)
            {
                $acp->current_activity = \WorkflowActivity::progress($acp,$this->context);
            }

            /*
             * Enable Commenting
             */
            $this->enableComment($supplier);
            
            $this->pageTitle = $supplier->getReadableName();
            
            $this->data['edit'] = $mode==='edit' ? true : false;
            $this->data['form'] = $supplier;
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$supplierTags));
            $this->data['payment_term_type'] = json_encode(\Helper::jsonobject_encode(\SupplierPaymentTerm::$typeList));
            $this->data['payment_term_term'] = json_encode(\Helper::jsonobject_encode(\PaymentTerm::getAll()));
            $this->data['activity'] = Helper::getMergedRevision(['credit'],$supplier,true);
            return $this->makeView('acpayable/supplier_single');
        }
        else
        {
            return parent::notfound();
        }
    }

    /*
     * Suppliers: End
     */

    /*
     * Custom Quick Saves
     */

    public function postSavePv()
    {
        if(\Input::has('id') || \Input::has('pv-id'))
        {
            if(\Input::get('pv-number',"") === "")
            {
                return \Response::make('Please input a PV number',400);
            }

            //Save PV directly
            if(\Input::has('pv-id'))
            {
                $pv = \SwiftACPPaymentVoucher::find(\Crypt::decrypt(\Input::get('pv-id')));
                if($pv)
                {
                    $pv->number = \Input::get('pv-number');
                    $pv->type = \Input::get('type');
                    $pv->save();
                    return \Response::json(['id'=>\Crypt::encrypt($pv->id)]);
                }
            }
            
            //Create new / Old one was deleted
            $acp = \SwiftACPRequest::with('paymentVoucher')->find(\Crypt::decrypt(\Input::get('id')));
            if($acp)
            {
                if($acp->paymentVoucher)
                {
                    $acp->paymentVoucher->number = \Input::get('pv-number');
                    $acp->paymentVoucher->type = \Input::get('type');
                    $acp->paymentVoucher->save();
                }
                else
                {
                    $pv = new \SwiftACPPaymentVoucher([
                        'number' => \Input::get('pv-number'),
                        'type' => \Input::get('type')
                    ]);
                    $acp->paymentVoucher()->save($pv);
                }

                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($acp),'id'=>$acp->id,'user_id'=>$this->currentUser->id));
                return \Response::json(['id'=>\Crypt::encrypt($pv->id)]);
            }
            else
            {
                return \Response::make('Accounts Payable form with ID:'.\Crypt::decrypt(\Input::get('id')).' not found',400);
            }
        }
        else
        {
            return \Response::make('Invalid request',500);
        }
    }

    /*
     * Overview : Ajax Widgets
     */
    public function getLateNodes()
    {
        $this->data['late_node_forms'] = WorkflowActivity::lateNodeByForm($this->context);
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes($this->context);

        echo View::make('workflow/overview_latenodes',$this->data)->render();
    }

    public function getPendingNodes()
    {
        $this->data['pending_node_activity'] = WorkflowActivity::statusByType($this->context);

        echo View::make('workflow/overview_pendingnodes',$this->data)->render();
    }

    public function getStories()
    {
        if($this->permission->isAdmin() || $this->permission->isAccountingDept())
        {
            $this->data['stories'] = Story::fetch(Config::get('context')[$this->context]);
        }
        else
        {
            //TBD: Stories for normal users
            $this->data['stories'] = [];
        }

        $this->data['dynamicStory'] = false;

        echo View::make('story/chapter',$this->data)->render();
    }

    /*
     * @action: POST
     * @url: save-by-form
     */

    public function postSaveByForm()
    {
        /*
         * Basic Validation
         */

        $validator = \Validator::make(
            \Input::all(),
            [
//                'pv_number' => 'required|numeric',
                'payable_type' => 'required',
                'payable_id' => 'required',
                'billable_company_code' => 'required|numeric',
                'supplier_code' => 'required|numeric',
                'due_date' => 'date_format:d/m/Y',
                'type' => 'required|in:'.implode(",",array_keys(\SwiftACPRequest::$order)),
                'invoice_number' => 'required'
            ]
        );
        if($validator->fails())
        {
            return \Response::make("The following errors were found: ".$validator->errors(),400);
        }

        /*
         * Check for duplicate PVs
         */

        /*$acpWithPVCount = \SwiftACPRequest::whereHas('paymentVoucher',function($q){
                            return $q->where('number','=',\Input::get('pv_number'));
                        })
                        ->whereHas('workflow',function($q){
                            return $q->where('status','!=',\SwiftWorkflowActivity::REJECTED);
                        })
                        ->where('billable_company_code','=',\Input::get('billable_company_code'))
                        ->get();

        if(count($acpWithPVCount))
        {
            return \Response::make("Payment voucher '".\Input::get('pv_number')."' already used. <a href='".\Helper::generateURL($acpWithPVCount->first())."' class='pjax'>Click here to view form.</a>",400);
        }*/

        /*
         * Validation Passed - We Save
         */

        $type = \Input::get('payable_type');
        $id = \Crypt::decrypt(\Input::get('payable_id'));

        //Save Accounts Payable
        $acp = new \SwiftACPRequest([
            'payable_type' => $type,
            'payable_id' => $id,
            'owner_user_id' => $this->currentUser->id,
            'billable_company_code' => \Input::get('billable_company_code'),
            'supplier_code' => \Input::get('supplier_code'),
            'type' => \Input::get('type')
        ]);

        $acp->save();

        //Save Payment Voucher
        /*$paymentVoucher = new \SwiftACPPaymentVoucher([
           'number' => \Input::get('pv_number')
        ]);
        $acp->paymentVoucher()->save($paymentVoucher);*/

        //Save Invoice
        $invoice = new \SwiftACPInvoice([
           'number' => \Input::get('invoice_number'),
           'due_date' => \Carbon::createFromFormat('d/m/Y',\Input::get('due_date'))
        ]);
        
        $acp->invoice()->save($invoice);

        //Create Workflow
        if(\WorkflowActivity::update($acp,$this->context))
        {
            //Story Relate
            \Queue::push('Story@relateTask',array('obj_class'=>get_class($acp),
                                                 'obj_id'=>$acp->id,
                                                 'action'=>\SwiftStory::ACTION_CREATE,
                                                 'user_id'=>$this->currentUser->id,
                                                 'context'=>get_class($acp)));

            return \Response::json(['msg'=>"Form saved successfully, <a class='pjax' href='".\Helper::generateUrl($acp)."'>click here to view & publish</a>"]);
        }
        else
        {
            return \Response::json(['msg'=>"A critical error occured. Contact your administrator. Id: {$acp->getKey()}"],500);
        }
        
    }

    public function getListByForm($class,$id)
    {
        if(in_array($class,\Config::get("context")))
        {
            $form = $class::with('payable')->find(\Crypt::decrypt($id));

            if($form)
            {
                foreach($form->payable as &$acp)
                {
                    $acp->current_activity = \WorkflowActivity::progress($acp,'acpayable');
                }

                return \View::make(\Helper::resolveContext($class).".edit_payable_list",['acp'=>$form->payable])->render();
            }
            else
            {
                return \Response::make("Unable to find form",500);
            }
        }
        else
        {
            return \Response::make("Cannot find form: context undefined",500);
        }
    }

    /*
     * View Payment
     * @param integer $payment_id
     */
    public function getViewPayment($payment_id)
    {
        //Check Access
        if($this->permission->canSignChequeExec() || $this->permission->isAdmin() || $this->permission->isAccountingDept())
        {
            if(is_numeric($payment_id))
            {
                $payment = \JdePaymentHeader::where('docm','=',$payment_id)
                            ->with(['supplier','detail'=>function($q){
                                return $q->orderBy('rc5','ASC');
                            }])->first();
                
                if($payment)
                {
                    echo \View::make('jdepayment.payment-single',['pay'=>$payment])->render();
                    return;
                }
                else
                {
                    return \Response::make('<div class="well"><h3>Payment record has not been found. Please check again tomorrow.</h3></div>');
                }
            }
            else
            {
                return \Response::make('<div class="well"><h3>Payment number should be numeric</h3></div>');
            }
        }
        else
        {
            return \Response::make('<div class="well"><h3>You don\'t have access to this feature.</h3></div>');
        }
    }
}