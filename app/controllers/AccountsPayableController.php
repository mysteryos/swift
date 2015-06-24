<?php

class AccountsPayableController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Accounts Payable";
        $this->context = $this->data['context'] = "acpayable";
        $this->rootURL = $this->data['rootURL'] = "accounts-payable";
        $this->adminPermission = "acp-admin";
        $this->viewPermission = "acp-view";
        $this->editPermission = "acp-edit";
        $this->createPermission = "acp-create";
        $this->HODPermission = "acp-hod";
        $this->accountingPaymentVoucherPermission = "acp-paymentvoucher";
        $this->accountingPaymentIssuePermission = "acp-paymentissue";

        $this->canWhat();
        $this->isWho();

    }

    private function canWhat()
    {
        $this->canCreate = $this->data['canCreate'] = $this->currentUser->hasAccess($this->createPermission);
        $this->canEdit = $this->data['canEdit'] = $this->currentUser->hasAccess($this->editPermission);
        $this->canView = $this->data['canView'] = $this->currentUser->hasAccess($this->viewPermission);
    }

    private function isWho()
    {
        $this->isAdmin = $this->data['isAdmin'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->isAccountingDept = $this->data['isAccountingDept'] = $this->currentUser->hasAnyAccess([$this->accountingPaymentVoucherPermission,
                                                                    $this->accountingPaymentIssuePermission]);
        $this->isHOD = $this->data['isHOD'] = $this->currentUser->hasAccess($this->HODPermission);
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
        if($this->data['isAdmin'])
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
    public function getForms($type=false,$page=1)
    {
        $limitPerPage = 15;

        $this->pageTitle = 'Forms';

        //Check user group
        if($type===false)
        {
            if(!$this->isAdmin)
            {
                //Set defaults
                if($this->isAccountingDepartment)
                {
                    $type='inprogress';
                }
                else if($this->canCreate)
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
            if(!$this->isAdmin && $this->canCreate && !in_array($type,['mine','starred']))
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
            case 'all':
                $acprequestquery->orderBy('updated_at','desc');
                break;
        }

        //Filters
        if(Input::has('filter'))
        {

            if(Session::has('acp_form_filter'))
            {
                $filter = Session::get('acp_form_filter');
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
                        $acprequestquery->where('business_unit','=',$f_val);
                        break;
                    case 'node_definition_id':
                        $acprequestquery->whereHas('workflow',function($q) use($f_val){
                           return $q->whereHas('nodes',function($q) use($f_val){
                               return $q->where('node_definition_id','=',$f_val);
                           });
                        });
                        break;
                }
            }

            Session::flash('acp_form_filter',$filter);

        }
        else
        {
            Session::forget('acp_form_filter');
        }

        $formsCount = $acprequestquery->count();
        if($type != 'inprogress')
        {
            /*
             * If not in progress, we limit rows
             */
            $acprequestquery->take($limitPerPage);
            if($page > 1)
            {
                $acprequestquery->offset(($page-1)*$limitPerPage);
            }
        }
        $forms = $acprequestquery->get();

        /*
         * Fetch latest history;
         */
        foreach($forms as $k => &$f)
        {

            //Set Current Workflow Activity
            $f->current_activity = WorkflowActivity::progress($f);

            //If in progress, we filter
            if($type == 'inprogress')
            {
                $hasAccess = false;
                /*
                 * Loop through node definition and check access
                 */
                if(isset($f->current_activity['definition']))
                {
                    foreach($f->current_activity['definition'] as $d)
                    {
                        if(NodeActivity::hasAccess($d,SwiftNodePermission::RESPONSIBLE))
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
                    unset($forms[$k]);
                    $formsCount--;
                    continue;
                }
            }
            else
            {
                if(isset($filter) && isset($filter['node_definition_id']))
                {
                    if(!isset($f->current_activity['definition']) || !in_array((int)$filter['node_definition_id'],$f->current_activity['definition']))
                    {
                        unset($forms[$k]);
                        $formsCount--;
                        break;
                    }
                }
            }

            //Set Revision
            $f->revision_latest = Helper::getMergedRevision($f->revisionRelations,$f);

            //Set Starred/important
            $f->flag_starred = Flag::isStarred($f);
            $f->flag_important = Flag::isImportant($f);
            $f->flag_read = Flag::isRead($f);
        }

        //The Data
        $this->data['type'] = $type;
        $this->data['canCreate'] = $this->currentUser->hasAnyAccess([$this->createPermission,$this->adminPermission]);
        $this->data['edit_access'] = $this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]);
        $this->data['forms'] = $forms;
        $this->data['count'] = isset($filter) ? $formsCount : SwiftACPRequest::count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['filter'] = Input::has('filter') ? "?filter=1" : "";
        $this->data['rootURL'] = $this->rootURL;

        return $this->makeView("acpayable/forms");
    }

    /*
     * Payment Voucher Process - Utility Page
     *
     */
    public function getPaymentVoucherProcess()
    {
        if(!$this->isAccountingDept && !$this->isAdmin)
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
            return $q->inprogress()->whereHas('nodes',function($q){
                return $q->whereHas('definition',function($q){
                   return $q->where('name','=','acp_paymentvoucher');
                })
                ->where('user_id','=',0);
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
     * Cheque Issue Utility Page
     *
     * @param string $type
     * @param integer $page
     */
    public function getChequeIssue($type='all',$page=1)
    {
        if(!$this->isAccountingDept && !$this->isAdmin)
        {
            return parent::forbidden();
        }

        /*
         * Register Filters
         */

        $this->filter['filter_start_date'] = ['name'=>'Start Date',
                                                'value' => Input::get('filter_start_date'),
                                                'enabled' => Input::has('filter_start_date')
                                            ];

        $this->filter['filter_end_date'] = ['name'=>'End Date',
                                                'value' => Input::get('filter_end_date'),
                                                'enabled' => Input::has('filter_end_date')
                                            ];

        $this->filter['filter_supplier'] = ['name'=>'Supplier',
                                            'value' => Input::has('filter_supplier') ? JdeSupplierMaster::find(Input::get('filter_supplier'))->getReadableName() : false,
                                            'enabled' => Input::has('filter_supplier')
                                            ];

        $limitPerPage = 30;

        /*
         * Get forms
         */
        $query = \SwiftACPRequest::query();
        $activeSuppliers = \SwiftACPRequest::query();

        //With
        $query->with(['supplier','company','paymentVoucher','paymentVoucher.invoice'])
                ->whereHas('workflow',function($q){
                    return $q->inprogress()->whereHas('pendingNodes',function($q){
                        return $q->whereHas('definition',function($q){
                           return $q->where('name','=','acp_paymentissue');
                        });
                    });
                })
                ->join('swift_acp_invoice','swift_acp_request.id','=','swift_acp_invoice.acp_id')
                ->orderBy('swift_acp_invoice.due_date','ASC');

        $activeSuppliers->distinct('supplier_code')
                        ->orderBy('supplier_code','ASC')
                        ->with(['supplier'])
                        ->whereHas('workflow',function($q){
                            return $q->inprogress()->whereHas('nodes',function($q){
                                return $q->whereHas('definition',function($q){
                                   return $q->where('name','=','acp_paymentissue');
                                });
                            });
                        });

        switch($type)
        {
            case 'all':
                if(\Input::has('filter_end_date') || \Input::has('filter_end_date'))
                {
                    $filter_end_date = \Carbon::createFromFormat('d/m/Y',Input::get('filter_end_date'));
                    $filter_start_date = \Carbon::createFromFormat('d/m/Y',Input::get('filter_start_date'));

                    $query->whereHas('paymentVoucher',function($q) use ($filter_end_date,$filter_start_date){
                        return $q->whereHas('invoice',function($q) use ($filter_end_date,$filter_start_date){
                                      if($filter_start_date !== false)
                                      {
                                          $q->where('due_date','>=',$filter_start_date->format('Y-m-d'),'AND');
                                      }
                                      if($filter_end_date !== false)
                                      {
                                          $q->where('due_date','<=',$filter_end_date->format('Y-m-d'),'AND');
                                      }

                                      $q->whereNotNull('due_date');

                                      return $q;
                                });
                    });

                }
                else
                {
                    $query->whereHas('paymentVoucher',function($q){
                        return $q->whereHas('invoice',function($q){
                                      return $q->whereNotNull('due_date');
                                });
                    });
                }
                break;
            case 'overdue':
                $query->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','<',Helper::previousBusinessDay(Carbon::now()->subDay())->format('Y-m-d'),'AND');
                                });
                });
                
                $activeSuppliers->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','<',Helper::previousBusinessDay(Carbon::now()->subDay())->format('Y-m-d'),'AND');
                                });
                });
                
                break;
            case 'today':
                $query->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','=',Carbon::now()->format('Y-m-d'),'AND');
                                });
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');

                $activeSuppliers->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','=',Carbon::now()->format('Y-m-d'),'AND');
                                });
                });
                
                break;
            case 'tomorrow':
                $query->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','=',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                                });
                })
                ->orderBy('swift_acp_request.supplier_code','ASC');

                $activeSuppliers->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','=',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                                });
                });
                
                break;
            case 'future':
                //Filter by date end
                if(Input::has('filter_end_date') && Carbon::createFromFormat('d/m/Y',Input::get('filter_end_date')))
                {
                    $query->whereHas('paymentVoucher',function($q){
                        return $q->whereHas('invoice',function($q){
                                      return $q->whereNotNull('due_date')
                                            ->where('due_date','>',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND')
                                            ->where('due_date','>',Carbon::createFromFormat('d/m/Y',Input::get('filter_end_date'))->format('Y-m-d'));
                                    });
                    });
                }
                else
                {
                    $query->whereHas('paymentVoucher',function($q){
                        return $q->whereHas('invoice',function($q){
                                      return $q->whereNotNull('due_date')
                                            ->where('due_date','>',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'));
                                    });
                    });
                }

                $activeSuppliers->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNotNull('due_date')
                                        ->where('due_date','>',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'));
                                });
                });
            
                break;
            case 'nodate':
                $query->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                  return $q->whereNull('due_date');
                                });
                });

                $activeSuppliers->whereHas('paymentVoucher',function($q){
                    return $q->whereHas('invoice',function($q){
                                    return $q->whereNull('due_date');
                                });
                });
                break;
        }

        /*
         * Counts
         */

        //Overdue

        $this->data['overdue_count'] = \SwiftACPRequest::whereHas('workflow',function($q){
                                            return $q->inprogress()->whereHas('nodes',function($q){
                                                return $q->whereHas('definition',function($q){
                                                   return $q->where('name','=','acp_paymentissue');
                                                });
                                            });
                                        })
                                        ->whereHas('paymentVoucher',function($q){
                                            return $q->whereHas('invoice',function($q){
                                                          return $q->whereNotNull('due_date')
                                                                ->where('due_date','<',Helper::previousBusinessDay(Carbon::now())->format('Y-m-d'),'AND');
                                                        });
                                        })->count();

        //Today

        $this->data['today_count'] = \SwiftACPRequest::whereHas('workflow',function($q){
                                            return $q->inprogress()->whereHas('nodes',function($q){
                                                return $q->whereHas('definition',function($q){
                                                   return $q->where('name','=','acp_paymentissue');
                                                });
                                            });
                                        })
                                        ->whereHas('paymentVoucher',function($q){
                                            return $q->whereHas('invoice',function($q){
                                                          return $q->whereNotNull('due_date')
                                                                ->where('due_date','=',Carbon::now()->format('Y-m-d'),'AND');
                                                        });
                                        })->count();

        //Tomorrow

        $this->data['tomorrow_count'] = \SwiftACPRequest::whereHas('workflow',function($q){
                                            return $q->inprogress()->whereHas('nodes',function($q){
                                                return $q->whereHas('definition',function($q){
                                                   return $q->where('name','=','acp_paymentissue');
                                                });
                                            });
                                        })->whereHas('paymentVoucher',function($q){
                                            return $q->whereHas('invoice',function($q){
                                                          return $q->whereNotNull('due_date')
                                                                ->where('due_date','=',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'),'AND');
                                                        });
                                        })->count();

        //Future

        $this->data['future_count'] = \SwiftACPRequest::whereHas('workflow',function($q){
                                            return $q->inprogress()->whereHas('nodes',function($q){
                                                return $q->whereHas('definition',function($q){
                                                   return $q->where('name','=','acp_paymentissue');
                                                });
                                            });
                                        })
                                        ->whereHas('paymentVoucher',function($q){
                                            return $q->whereHas('invoice',function($q){
                                                          return $q->whereNotNull('due_date')
                                                                ->where('due_date','>',Helper::nextBusinessDay(Carbon::now()->addDay())->format('Y-m-d'));
                                                        });
                                        })->count();

        $this->data['nodate_count'] = \SwiftACPRequest::whereHas('workflow',function($q){
                                            return $q->inprogress()->whereHas('nodes',function($q){
                                                return $q->whereHas('definition',function($q){
                                                   return $q->where('name','=','acp_paymentissue');
                                                });
                                            });
                                        })
                                        ->whereHas('paymentVoucher',function($q){
                                            return $q->whereHas('invoice',function($q){
                                                          return $q->whereNull('due_date');
                                                        });
                                        })
                                        ->count();


        //The Filters

        if(\Input::has('filter_supplier') && is_numeric(\Input::get('filter_supplier')))
        {
            $query->where('supplier_code','=',\Input::get('filter_supplier'));
        }

        //Form for Display and Count

        $form_count = $query->count();

        $query->take($limitPerPage);
        if($page > 1)
        {
            $query->offset(($page-1)*$limitPerPage);
        }
        $forms = $query->get();

        $activeSupplierResult = $activeSuppliers->get();

//        $this->data['overdue'] = $overdue;
//        $this->data['dueToday'] = $dueToday;
//        $this->data['dueTomorrow'] = $dueTomorrow;
//        $this->data['dueFuture'] = $dueFuture;
//        $this->data['tomorrowDate'] = $this->data['futureStartDate'] = $tomorrowDate;
//        $this->data['futureEndDate'] = $futureEndDate;

        $this->data['activeSuppliers'] = $activeSupplierResult;
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
        $this->data['pageTitle'] = "Cheque Issue - ".ucfirst($type);
        return $this->makeView('acpayable/payment-cheque-issue');
        
    }

    public function getCreate()
    {
        $this->pageTitle = 'Create';

        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->createPermission]))
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->createPermission]))
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
                    /*
                     * Has invoice number
                     */
                    if(\Input::has('invoice_number') && $inputData['invoice_number'] !== "")
                    {
                        $invoice = new \SwiftACPInvoice([
                            'number' => $inputData['invoice_number']
                        ]);
                        $acp->invoice()->save($invoice);
                    }

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

    public function getView($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,false);
        }

        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return Redirect::action('AccountsPayableController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess([$this->viewPermission]))
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
        elseif($this->currentUser->hasAnyAccess([$this->viewPermission]))
        {
            return \Redirect::action('AccountsPayableController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }

    private function checkAccess($acp)
    {
        $hasAccess = false;
        //Owner has access
        if($acp->isOwner())
        {
            $hasAccess = true;
        }

        //Accounting or Admin has access
        if($this->isAccountingDept || $this->isAdmin)
        {
            $hasAccess = true;
        }

        $approvalUserIds = array();
        $approvalUserIds = array_map(function($val){
                                if($val['type'] === \SwiftApproval::APC_HOD)
                                {
                                    return $val['approval_user_id'];
                                }
                           },$acp->approval->toArray());

        //HoDs have access
        if(in_array($this->currentUser->id,$approvalUserIds))
        {
            $hasAccess = true;
        }

        //Permission Check - End
        return $hasAccess;
    }

    private function form($id,$edit=false)
    {
        $acp_id = \Crypt::decrypt($id);
        $acp = \SwiftACPRequest::getById($acp_id);

        if($acp)
        {

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

            //Permission Check
            if(!$this->checkAccess($acp))
            {
                return parent::forbidden();
            }

            //Find HoDs;
            $this->data['approval_hod'] = array();
            $hods = \Sentry::findAllUsersWithAccess(array($this->HODPermission));
            if(count($hods))
            {
                foreach($hods as $h)
                {
                    $this->data['approval_hod'][$h->id] = $h->first_name." ".$h->last_name;
                }
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
            $this->data['cheque_status'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$status));
            $this->data['payment_type'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$type));
            $this->data['cheque_dispatch'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$dispatch));
            $this->data['payment_term'] = json_encode(\Helper::jsonobject_encode(\SwiftACPInvoice::$paymentTerm));
            $this->data['pv_validation'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPaymentVoucher::$validationArray));
            $this->data['approval_hod'] = json_encode(\Helper::jsonobject_encode($this->data['approval_hod']));
            $this->data['currency'] = json_encode(\Helper::jsonobject_encode(\Currency::getAll()));
            $this->data['flag_important'] = \Flag::isImportant($acp);
            $this->data['flag_starred'] = \Flag::isStarred($acp);
            $this->data['type_order'] = json_encode(\Helper::jsonobject_encode(\SwiftACPRequest::$order));
            $this->data['po_type'] = json_encode(\Helper::jsonobject_encode(\SwiftPurchaseOrder::$types));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$acpayableTags));
            $this->data['owner'] = \Helper::getUserName($acp->owner_user_id,$this->currentUser);
            $this->data['edit'] = $edit;
            $this->data['publishOwner'] = $this->data['publishAccounting'] = $this->data['addCreditNote'] = $this->data['savePaymentVoucher'] = false;
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
                                if(isset($d->data->publishOwner) && ($this->data['isAdmin'] || $this->isAccountingDepartment || $acp->isOwner()))
                                {
                                    $this->data['publishOwner'] = true;
                                    break;
                                }

                                if(isset($d->data->publishAccounting) && ($this->data['isAdmin'] || $this->isAccountingDepartment))
                                {
                                    $this->data['publishAccounting'] = true;
                                    break;
                                }

                                if(isset($d->data->addCreditNote))
                                {
                                    $this->data['addCreditNote'] = true;
                                    break;
                                }

                                if(isset($d->data->savePaymentVoucher) && ($this->data['isAdmin'] || $this->isAccountingDepartment))
                                {
                                    $this->data['savePaymentVoucher'] = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                //Check if form has supplier payment terms
                if($this->isAccountingDept || $this->isAdmin)
                {
                    if(!$acp->supplier->paymentTerm)
                    {
                        $this->data['message'][] = [ 'type' => 'warning',
                                                     'msg' => "This supplier doesn't have any payment terms. <a href=\"".Helper::generateURL($acp->supplier)."\" class=\"pjax\">Please provide one.</a>"
                                                    ];
                    }
                }
            }
            
            //Save recently viewed form
            Helper::saveRecent($acp,$this->currentUser);

            return $this->makeView("$this->context/edit");
        }
        else
        {
            return parent::notfound();
        }
    }

    public function putGeneralInfo()
    {
        $acp_id = Crypt::decrypt(Input::get('pk'));
        $acp = SwiftACPRequest::find($acp_id);

        if($acp)
        {

            if(!$this->checkAccess($acp))
            {
                return parent::forbidden();
            }

            switch(Input::get('name'))
            {
                case "name":
                    break;
                case "description":
                    break;
                case "type":
                    if(!array_key_exists(Input::get('value'),\SwiftACPRequest::$order))
                    {
                        return Response::make("Please select a valid type",500);
                    }
                    break;
                case "billable_customer_code":
                    if(!is_numeric(trim(Input::get('value'))))
                    {
                        return Response::make("Company code should be numeric.",400);
                    }
                    break;
                case "supplier_code":
                    if(!is_numeric(trim(Input::get('value'))))
                    {
                        return Response::make("Supplier code should be numeric.",400);
                    }
                    break;
                default:
                    return Response::make("Unknown Field",400);
            }

            $acp->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
            if($acp->save())
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
            if(!$this->checkAccess($acp))
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
            if(!$this->checkAccess($acp))
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
            if(!$this->checkAccess($acp))
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
            if(!$this->checkAccess($acp))
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
        $acp = SwiftACPRequest::find(Crypt::decrypt($acp_id));

        /*
         * Manual Validation
         */
        if(count($acp))
        {
            /*
             * Check Permissions
             */
            if(!$this->checkAccess($acp))
            {
                return parent::forbidden();
            }
            
            
            //Validation
            switch(Input::get('name'))
            {
                case "number":
                    $invoiceExist = new \Illuminate\Database\Eloquent\Collection();
                    if(Input::get('value') !== "")
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
                    if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
                    {
                        return \Response::make('Please enter a numeric value.',400);
                    }
                    if((is_numeric(\Input::get('value')) && (int)\Input::get('value') <= 0) || Input::get('value')==="")
                    {
                        return \Response::make('Please enter a valid amount',400);
                    }
                    break;
                case "payment_term":
                    if(!array_key_exists(\Input::get('value'),\SwiftACPInvoice::$paymentTerm))
                    {
                        return \Response::make('Please enter a valid payment Term');
                    }
                    break;
                case "currency":
                    if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
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
            if(!$this->checkAccess($acp))
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
        $acp = SwiftACPRequest::find(Crypt::decrypt($acp_id));

        if(count($acp))
        {
            /*
             * Check Permissions
             */
            if(!$this->isAccountingDept && !$this->isAdmin)
            {
                return parent::forbidden();
            }

            //Validation
            switch(Input::get('name'))
            {
                case "type":
                    if(!array_key_exists(Input::get('value'),\SwiftACPPayment::$type))
                    {
                        return Response::make('Please enter valid payment type',400);
                    }
                    break;
                case "date":
                case "cheque_dispatch_comment":
                case "currency":
                    break;
                case "amount":
                case "payment_number":
                case "batch_number":
                    if(Input::get('value')!== "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter a numeric value',400);
                    }
                    break;
                case "status":
                    if(!array_key_exists(Input::get('value'),\SwiftACPPayment::$status))
                    {
                        return Response::make('Please enter a valid status',400);
                    }
                    break;
                case "cheque_dispatch":
                    if(!array_key_exists(Input::get('value'), \SwiftACPPayment::$dispatch))
                    {
                        return Response::make('Please enter a valid dispatch method.',400);
                    }
                    break;
                case "currency":
                    if(!is_numeric(Input::get('value')) || Input::get('value') === "")
                    {
                        return Response::make('Please select a valid currency code',400);
                    }
                    else
                    {
                        if(Input::get('value') <= 0)
                        {
                            return Response::make('Please select a valid currency code',400);
                        }
                    }
                default:
                    return Response::make('Unknown Field',400);
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
            if(!$this->isAccountingDept && !$this->isAdmin)
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
        $acp = SwiftACPRequest::find(Crypt::decrypt($acp_id));

        if($acp)
        {
            /*
             * Check Permissions
             */
            if(!$this->isAccountingDept && !$this->isAdmin)
            {
                return parent::forbidden();
            }

            //Validation
            switch(Input::get('name'))
            {
                case "number":
                    if(Input::get('value') !== "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make("Please input a numeric value.");
                    }
                    break;
                case "validated":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make("You don't have access to this feature.");
                    }
                    
                    if(Input::get('value') !== "" && !in_array((int)Input::get('value'),\SwiftACPPaymentVoucher::$validationArray))
                    {
                        return Response::make("Please enter a valid value.");
                    }
                    break;
                case "validated_msg":
                    if(!$this->currentUser->isSuperUser())
                    {
                        return \Response::make("You don't have access to this feature.");
                    }
                    break;
                default:
                    return Response::make('Unknown Field',400);
                    break;
            }

            return Helper::saveChildModel($acp,"\SwiftACPPaymentVoucher","paymentVoucher",$this->currentUser,true);

        }
        else
        {
            return parent::notfound();
        }
    }

    public function deletePaymentvoucher()
    {
        $id = Crypt::decrypt(Input::get('pk'));
        $pv = SwiftACPPaymentVoucher::find($id);
        if($pv)
        {
            $acp = $pv->acp;
            /*
             * Check Permissions
             */
            if(!$this->isAccountingDept && !$this->isAdmin)
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
        $acp = \SwiftACPRequest::find(Crypt::decrypt($acp_id));

        if($acp)
        {
            if($this->checkAccess($acp))
            {
                switch(\Input::get('name'))
                {
                    case "approval_user_id":
                        if(\Input::get('value') !== "" && !is_numeric(\Input::get('value')))
                        {
                            return \Response::make("Please select a valid user.",400);
                        }
                        break;
                    case "approved":
                        if(\Input::get('value') !== "" && !array_key_exists(\Input::get('value'),\SwiftApproval::$approved))
                        {
                            return \Response::make("Please select a valid approval status.",400);
                        }
                        if(!$this->isHOD)
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
            if(!$this->isAccountingDept && !$this->isAdmin)
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
            if($acp->owner_user_id === $this->currentUser->id || $this->isAdmin)
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
            if($this->isAccountingDept || $this->isAdmin)
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
                                $returnReasonList = array();

                                /*
                                 * Manual check for all data
                                 */
                                $paymentCount = $acp->payment()->count();
                                if($paymentCount === 0)
                                {
                                    $returnReasonList['payment_absent'] = "Input payment details";
                                }
                                else
                                {
                                    $payment = $acp->payment()->get();
                                    //all payments should have an amount
                                    foreach($payment as $p)
                                    {
                                        if($p->amount === 0)
                                        {
                                            $returnReasonList['payment_amount_absent'] = "Input amount for payment ID: ".$p->id;
                                        }
                                    }
                                }

                                if(count($returnReasonList) !== 0)
                                {
                                    return \Response::make(implode(", ",$returnReasonList),400);
                                }

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

    public function putPaymentNumber($form_id)
    {
        $form = \SwiftACPRequest::find(\Crypt::decrypt($form_id));
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
                    'type' => \SwiftACPPayment::TYPE_CHEQUE
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
        $form = \SwiftACPRequest::find(\Crypt::decrypt($form_id));
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
                    'type' => \SwiftACPPayment::TYPE_CHEQUE
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

    public function postUpload($id)
    {

        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
                $doc->document = Input::file('file');
                if($acp->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>Crypt::encrypt($doc->id),
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
            return Response::make('Accounts Payable form not found',404);
        }
    }

    public function postSupplierUpload($id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        $supplier = \JdeSupplierMaster::find($id);
        /*
        * Manual Validation
         */
        if($supplier)
        {
            if(Input::file('file'))
            {
                $doc = new SwiftSupplierDocument();
                $doc->document = Input::file('file');
                if($supplier->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>Crypt::encrypt($doc->id),
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
            return Response::make('Supplier form not found',404);
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

        $doc = SwiftAPDocument::find(Crypt::decrypt($doc_id));
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
            if(!$this->isAccountingDept && !$this->isAdmin)
            {
                return parent::forbidden();
            }

            //Validation
            switch(Input::get('name'))
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
                    return Response::make('Unknown Field',400);
                    break;
            }

            return Helper::saveChildModel($supplier,"\SupplierPaymentTerm","paymentTerm",$this->currentUser,false);
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        if(Input::get('pk') && !is_numeric(Input::get('pk')))
        {
            $doc = SwiftACPDocument::with('tag')->find(Crypt::decrypt(Input::get('pk')));
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
                                if(key_exists($val,SwiftTag::$acpayableTags))
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
                        if(key_exists($val,SwiftTag::$acpayableTags))
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

        $form = SwiftACPRequest::find(Crypt::decrypt($id));
        if(count($form))
        {
            return WorkflowActivity::progressHelp($form,$needPermission);
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
        if(!$this->isAdmin)
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
        return \Flag::set($type,'SwiftACPRequest',$this->adminPermission);
    }

    /*
     * Suppliers: Start
     */

    public function getSupplierList($filter=false,$page=1)
    {
        if(!$this->currentUser->hasAccess($this->viewPermission))
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
        $this->data['edit_access'] = $this->isHOD || $this->isAccountingDept;

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
                if(!$this->isAccountingDept && !$this->isAdmin)
                {
                    $mode='view';
                }
            }
            else
            {
                if($this->isAccountingDept || $this->isAdmin)
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
                    $pv->save();
                    return \Response::json(['id'=>\Crypt::encrypt($pv->id)]);
                }
            }
            
            //Create new / Old one was deleted
            $acp = \SwiftACPRequest::find(\Crypt::decrypt(\Input::get('id')));
            if($acp)
            {
                $pv = new \SwiftACPPaymentVoucher([
                   'number' => \Input::get('pv-number')
                ]);
                $acp->paymentVoucher()->save($pv);

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
        if($this->isAdmin || $this->isAccountingDept)
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
}