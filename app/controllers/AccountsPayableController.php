<?php
class AccountsPayableController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Accounts Payable";
        $this->context = "acpayable";
        $this->rootURL = $this->data['rootURL'] = "accounts-payable";
        $this->adminPermission = "acp-admin";
        $this->viewPermission = "acp-view";
        $this->editPermission = "acp-edit";
        $this->createPermission = "acp-create";
        $this->hodPermission = "acp-hod";
        $this->accountingPaymentVoucherPermission = "acp-paymentvoucher";
        $this->accountingPaymentIssuePermission = "acp-paymentissue";
        $this->isAdmin = $this->data['isAdmin'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->isAccountingDept = $this->data['isAccountingDept'] = $this->currentUser->hasAnyAccess([$this->accountingPaymentVoucherPermission,
                                                                    $this->accountingPaymentIssuePermission]);
        $this->isHOD = $this->data['isHOD'] = $this->currentUser->hasAccess($this->hodPermission);
    }
    
    /*
     * Overview
     */
    
    public function getOverview()
    {
        $this->pageTitle = 'Overview';
        $this->data['isAdmin'] = $this->isAdmin;
        $this->data['inprogress_limit'] = 15;
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes($this->context);
        $this->data['pending_node_count'] = SwiftNodeActivity::countPendingNodesWithEta($this->context);
        
        $inprogress = $inprogress_important = $inprogress_responsible = $inprogress_important_responsible = array();

        /*
         * Admin can see all
         */
        if($this->data['isAdmin'])
        {
            $inprogress = SwiftACPRequest::getInProgress($this->data['inprogress_limit']);
            $inprogress_count = SwiftACPRequest::getInProgressCount();
            $inprogress_important = SwiftACPRequest::getInProgress(0,true);
        }
        
        /*
         * Admin can see all
         */
        if($this->data['isAdmin'])
        {
            $inprogress = $inprogress->diff($inprogress_responsible);
            $inprogress_important = $inprogress_important->diff($inprogress_important_responsible);
        }

        $inprogress_responsible = SwiftACPRequest::getInProgressResponsible();
        $inprogress_important_responsible = SwiftACPRequest::getInProgressResponsible(0,true);

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
                $acp->current_activity = WorkflowActivity::progress($acp);
                $acp->activity = Helper::getMergedRevision($acp->revisionRelations,$acp);
            }
        }

        /*
         * Data
         */
        $this->data['canCreate'] = $this->currentUser->hasAccess($this->createPermission);
        $this->data['inprogress'] = $inprogress;
        $this->data['inprogress_responsible'] = $inprogress_responsible;
        $this->data['inprogress_important'] = $inprogress_important;
        $this->data['inprogress_important_responsible'] = $inprogress_important_responsible;

        return $this->makeView('acpayable/overview');
    }

    public function getCreate()
    {
        $this->pageTitle = 'Create';
        
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

        $validator = Validator::make(Input::all(),
                    array('billable_company_code'=>['required','numeric'],
                          'supplier_code'=>['required','numeric'],
                        )
                );

        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $acp = new SwiftACPRequest();
            $acp->fill(Input::all());
            if($acp->save())
            {
                //Start the Workflow
                if(\WorkflowActivity::update($acp,$this->context))
                {
                    //Story Relate
                    Queue::push('Story@relateTask',array('obj_class'=>get_class($acp),
                                                         'obj_id'=>$acp->id,
                                                         'action'=>SwiftStory::ACTION_CREATE,
                                                         'user_id'=>$this->currentUser->id,
                                                         'context'=>get_class($acp)));
                    $id = Crypt::encrypt($acp->id);
                    //Success
                    echo json_encode(['success'=>1,'url'=>"/{$this->rootURL}/edit/$id"]);
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
            return Redirect::action('AccountsPayableController@getView',array('id'=>$id));
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
        $acp_id = Crypt::decrypt($id);
        $acp = SwiftACPRequest::getById($acp_id);

        if($acp)
        {
            /*
             * Set Read
             */

            if(!Flag::isRead($acp))
            {
                Flag::toggleRead($acp);
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

            $this->data['current_activity'] = \WorkflowActivity::progress($acp,$this->context);
            $this->data['activity'] = \Helper::getMergedRevision($acp->revisionRelations,$acp);
            $this->pageTitle = $acp->getReadableName();
            $this->data['form'] = $acp;
            $this->data['cheque_status'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$status));
            $this->data['payment_type'] = json_encode(\Helper::jsonobject_encode(\SwiftACPPayment::$type));
            $this->data['payment_term'] = json_encode(\Helper::jsonobject_encode(\SwiftACPInvoice::$paymentTerm));
            $this->data['currency'] = json_encode(\Helper::jsonobject_encode(\Currency::getAll()));
            $this->data['flag_important'] = \Flag::isImportant($acp);
            $this->data['flag_starred'] = \Flag::isStarred($acp);
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$acpayableTags));
            $this->data['owner'] = Helper::getUserName($acp->owner_user_id,$this->currentUser);
            $this->data['edit'] = $edit;
            $this->data['publishOwner'] = $this->data['publishAccounting'] = false;
            
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
                            }
                        }
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
        $acp = SwiftAPRequest::find($acp_id);

        if(count($acp))
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
                case "date":
                case "due_date":
                case "gl_code":
                    break;
                case "due_amount":
                    if(Input::get('value') !== "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter a numeric value.',400);
                    }
                    if(is_numeric(Input::get('value')) && Input::get('value') <= 0)
                    {
                        return Response::make('Please enter a valid amount',400);
                    }
                    break;
                case "payment_term":
                    if(!array_key_exists(Input::get('value'),\SwiftACPInvoice::$paymentTerm))
                    {
                        return Response::make('Please enter a valid payment Term');
                    }
                    break;
                case "currency":
                    if(Input::get('value') !== "" && !is_numeric(Input::get('value')))
                    {
                        return Response::make('Please select a valid currency.',400);
                    }
                    break;
                default:
                    return Response::make('Unknown Field',400);
                    break;
            }

            /*
             * New Invoice
             */
            return Helper::saveChildModel($acp,"\SwiftACPInvoice","invoice",$this->currentUser,false);
        }
        else
        {
            return Response::make('Accounts Payable process form not found',404);
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
                case "journal_entry_number":
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
            return Helper::saveChildModel($acp,"\SwiftACPPayment","payment",$this->currentUser);
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
                default:
                    return Response::make('Unknown Field',400);
                    break;
            }

            return Helper::saveChildModel($acp,"\SwiftACPPaymentVoucher","paymentVoucher",$this->currentUser);

        }
        else
        {
            return parent::notfound();
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

        $acp = SwiftACPRequest::find(Crypt::decrypt($id));

        if(count($acp))
        {

            if(WorkflowActivity::cancel($acp))
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
            return Response::make('Accounts payable form not found',404);
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
}