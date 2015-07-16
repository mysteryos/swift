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
}