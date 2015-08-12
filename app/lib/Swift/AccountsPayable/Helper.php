<?php
/*
 * Name:
 * Description:
 */

namespace Swift\AccountsPayable;

class Helper
{
    /*
     * Calculates the Due Date
     *
     * @param integer $paymentTerm
     * @param \Carbon\Carbon $invoice_date
     *
     * @return \Carbon\Carbonn
     */
    
    public static function calculateDueDate($paymentTerm,\Carbon\Carbon $invoice_date)
    {
        
    }

    public static function getChequeSignUserList(array $permissions)
    {
        $chequesign_users = array();
        $signChequeUsers = \Sentry::findAllUsersWithAccess($permissions);
        if(count($signChequeUsers))
        {
            foreach($signChequeUsers as $cu)
            {
                if(!$cu->isSuperUser() && $cu->activated)
                {
                    $chequesign_users[$cu->id] = $cu->first_name." ".$cu->last_name;
                }
            }
        }
        asort($chequesign_users);
        
        return $chequesign_users;
    }

    public static function checkAccess($acp)
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

        //HoDs have access
        $approvalUserIds = array();
        $approvalUserIds = array_map(function($val){
                                if($val['type'] === \SwiftApproval::APC_HOD)
                                {
                                    return $val['approval_user_id'];
                                }
                           },$acp->approval->toArray());


        if(in_array($this->currentUser->id,$approvalUserIds))
        {
            if($this->isHOD)
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
                            },$acp->payment->toArray());

        if(in_array($this->currentUser->id,$executiveUserIds))
        {
            if($this->canSignChequeExec)
            {
                $hasAccess = true;
            }
        }

        /*
         * Sharing Access
         */
        if(!$hasAccess)
        {
             $sharedUserCount = $acp->share()->where('to_user_id','=',$this->currentUser->id)->count();
             if($sharedUserCount > 0)
             {
                 $hasAccess = true;
             }
        }

        //Permission Check - End
        return $hasAccess;
    }
}