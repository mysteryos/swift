<?php

namespace Permission;

class SwiftPR extends Permission {
    public $adminPermission     = 'pr-admin';
    public $viewPermission      = 'pr-view';
    public $editPermission      = 'pr-edit';
    public $createPermission    = 'pr-create';
    public $createSalesmanPermission = 'pr-create-salesman';
    public $createOndeliveryPermission = 'pr-create-ondelivery';
    public $createInvoiceCancelledPermission = 'pr-create-invoice-cancelled';
    public $ccarePermission     = 'pr-ccare';
    public $pickupPermission    = 'pr-storepickup';
    public $receptionPermission = 'pr-storereception';
    public $validationPermission = 'pr-storevalidation';
    public $retailmanPermission = 'pr-retailman';
    public $salesmanPermission  = 'pr-salesman';
    public $creditorPermission  = 'pr-creditor';

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

    public function canCreateSalesman()
    {
        return $this->currentUser->hasAccess($this->createSalesmanPermission);
    }

    public function canCreateOnDelivery()
    {
        return $this->currentUser->hasAccess($this->createOndeliveryPermission);
    }

    public function canCreateInvoiceCancelled()
    {
        return $this->currentUser->hasAccess($this->createInvoiceCancelledPermission);
    }

    public function isAdmin()
    {
        return $this->currentUser->hasAccess($this->adminPermission);
    }

    public function isSalesman()
    {
        return $this->currentUser->hasAccess($this->salesmanPermission);
    }

    public function isRetailMan()
    {
        return $this->currentUser->hasAccess($this->retailmanPermission);
    }

    public function isCcare()
    {
        return $this->currentUser->hasAccess($this->ccarePermission);
    }

    public function isStorePickup()
    {
        return $this->currentUser->hasAccess($this->pickupPermission);
    }

    public function isStoreReception()
    {
        return $this->currentUser->hasAccess($this->receptionPermission);
    }

    public function isStoreValidation()
    {
        return $this->currentUser->hasAccess($this->validationPermission);
    }

    public function isCreditor()
    {
        return $this->currentUser->hasAccess($this->creditorPermission);
    }

    public function checkAccess()
    {
        $hasAccess = false;
        //Owner has access
        if($this->form->isOwner($this->currentUser->id))
        {
            $hasAccess = true;
        }

        if($this->isAdmin() || $this->isStoreReception() || $this->isStoreValidation() || $this->isCreditor() || $this->isCcare() || $this->isRetailMan())
        {
            $hasAccess = true;
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