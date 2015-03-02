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
        $this->isAdmin = $this->currentUser->hasAccess($this->adminPermission);
        $this->isAccountingDept = $this->currentUser->hasAnyAccess([$this->accountingPaymentVoucherPermission,
                                                                    $this->accountingPaymentIssuePermission]);
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
            $acp = new SwiftAccountsPayable();
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