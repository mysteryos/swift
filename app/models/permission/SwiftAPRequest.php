<?php

namespace Permission;

class SwiftAPRequest extends Permission {

    public $adminPermission = 'apr-admin';
    public $viewPermission = 'apr-view';
    public $editPermission = 'apr-edit';
    public $ccarePermission = 'apr-ccare';
    public $storePermission = 'apr-store';
    public $createPermission = 'apr-create';
    public $catManPermission = 'apr-catman';
    public $execPermission = 'apr-exec';

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

    public function isAdmin()
    {
        return $this->currentUser->hasAccess($this->adminPermission);
    }

    public function isCatMan()
    {
        return $this->currentUser->hasAccess($this->catManPermission);
    }

    public function isCcare()
    {
        return $this->currentUser->hasAccess($this->ccarePermission);
    }

    public function isExec()
    {
        return $this->currentUser->hasAccess($this->execPermission);
    }

    public function isStore()
    {
        return $this->currentUser->hasAccess($this->storePermission);
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