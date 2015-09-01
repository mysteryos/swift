<?php

/*
 * Name: SwiftACPRequest
 * Description: Regulates access to accounts payable
 */

namespace Permission;

class SwiftACPRequest extends Permission {

    public $adminPermission = "acp-admin";
    public $viewPermission = "acp-view";
    public $editPermission = "acp-edit";
    public $createPermission = "acp-create";
    public $HODPermission = "acp-hod";
    public $accountingPaymentVoucherPermission = "acp-paymentvoucher";
    public $accountingPaymentIssuePermission = "acp-paymentissue";
    public $accountingChequeDispatch = "acp-chequedispatch";
    public $accountingChequeSignPermission = "acp-chequesign";
    public $accountingChequeSignExecPermission = "acp-exec";

    public function __construct($form=false,$user_id=false)
    {
        parent::__construct($form,$user_id);
    }

    public function canCreate()
    {
        return $this->currentUser->hasAccess($this->createPermission);
    }

    public function canEdit()
    {
        return $this->currentUser->hasAccess($this->editPermission);
    }

    public function canView()
    {
        return $this->currentUser->hasAccess($this->viewPermission);
    }

    public function canSignCheque()
    {
        return $this->currentUser->hasAccess($this->accountingChequeSignPermission);
    }

    public function canSignChequeExec()
    {
        return $this->currentUser->hasAccess($this->accountingChequeSignExecPermission);
    }

    public function canDispatchCheque()
    {
        return $this->currentUser->hasAccess($this->accountingChequeDispatch);
    }

    public function isAdmin()
    {
        return $this->currentUser->hasAccess($this->adminPermission);
    }

    public function isAccountingDept()
    {
        return $this->currentUser->hasAnyAccess([$this->accountingPaymentVoucherPermission,
                                                                        $this->accountingPaymentIssuePermission,
                                                                        $this->accountingChequeSignPermission,
                                                                        $this->accountingChequeDispatch
                                                                    ]);
    }

    public function isHOD()
    {
        return $this->currentUser->hasAccess($this->HODPermission);
    }

    public function checkAccess()
    {
        $hasAccess = false;
        //Owner has access
        if($this->form->isOwner($this->currentUser->id))
        {
            $hasAccess = true;
        }

        //Accounting or Admin has access
        if($this->isAccountingDept() || $this->isAdmin())
        {
            $hasAccess = true;
        }

        //HoDs have access
        $approvalUserIds = array();
        $approvalUserIds = array_map(function($val){
                                if($val['type'] === \SwiftApproval::APC_HOD)
                                {
                                    return $val['approval_user_id'];
                                }
                           },$this->form->approval->toArray());


        if(in_array($this->currentUser->id,$approvalUserIds))
        {
            if($this->isHOD())
            {
                $hasAccess = true;
            }
        }

        //Executive Access
        $executiveUserIds = [];
        $executiveUserIds = array_map(function($val){
                                if($val['cheque_exec_signator_id'] !== null && (int)$val['cheque_exec_signator_id'] > 0)
                                {
                                    return $val['cheque_exec_signator_id'];
                                }
                            },$this->form->payment->toArray());

        if(in_array($this->currentUser->id,$executiveUserIds))
        {
            if($this->canSignChequeExec())
            {
                $hasAccess = true;
            }
        }

        /*
         * Sharing Access
         */
        if(!$hasAccess && $this->form->isSharedWith($this->currentUser->id))
        {
            $hasAccess = true;
        }

        //Permission Check - End
        return $hasAccess;
    }

}