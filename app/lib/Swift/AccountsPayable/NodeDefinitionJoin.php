<?php
/*
 * Name: A&P Request Node Definition Join
 * Description: Provides functions to handle Node Definition Joins
 */

NameSpace Swift\AccountsPayable;

Class NodeDefinitionJoin {
    public static function startToPrep($nodeActivity)
    {
        return true;
    }

    public static function prepToApproval($nodeActivity)
    {
        return true;
    }

    public static function approvalToCreditnote($nodeActivity)
    {
        //Only if approval of HOD is rejected
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $approvalCount = $acp->approvalHod()
                            ->where('approved','!=',\SwiftApproval::PENDING)
                            ->count();

            $approvalRejectedCount = $acp->approvalHod()
                                ->where('approved','=',\SwiftApproval::REJECTED)
                                ->count();
            
            if($approvalCount === $approvalRejectedCount)
            {
                //Everybody rejected the invoice
                return true;
            }
        }
        return false;
    }

    public static function approvalToPaymentvoucher($nodeActivity)
    {
        //Only if approval of HOD is approved
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $approvalApproved = $acp->approvalHod()
                        ->orderBy('created_at','DESC')
                        ->where('approved','=',\SwiftApproval::APPROVED)
                        ->count();
            
            if($approvalApproved >= 1)
            {
                return true;
            }
        }
        return false;
    }

    public static function paymentvoucherToPaymentissue($nodeActivity)
    {
        //Create Approval for manual publishing of payment issue
        //Needed to block loop
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            //Check if approval entry already present

            $pendingApproval = $acp->approval()
                                ->where('type','=',\SwiftApproval::APC_PAYMENT)
                                ->where('approved','=',\SwiftApproval::PENDING)
                                ->count();
            if(!$pendingApproval)
            {
                $approval = new \SwiftApproval([
                    'type' => \SwiftApproval::APC_PAYMENT
                ]);
                $acp->approval()->save($approval);
            }
        }
        
        return true;
    }

    public static function paymentissueToChequesign($nodeActivity)
    {
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            //Has Cheques
            $chequePayment = $acp->payment()
                        ->cheque()
                        ->count();

            if($chequePayment > 0)
            {
                return true;
            }
        }
        return false;
    }

    public static function paymentissueToBanktransfer($nodeActivity)
    {
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            //Has bank Payment
            $bankPayment = $acp->payment()
                        ->bankTransfer()
                        ->count();

            if($bankPayment > 0)
            {
                return true;
            }
        }
        return false;
    }

    public static function chequesignToChequeready($nodeActivity)
    {
        return true;
    }

    public static function chequereadyToCheckpayment($nodeActivity)
    {
        return true;
    }

    public static function banktransferToCheckpayment($nodeActivity)
    {
        return true;
    }

    public static function checkpaymentToPaymentissue($nodeActivity)
    {
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $amountDue = $acp->invoice->due_amount;
            $amountPaid = \SwiftACPPayment::sumTotalAmountPaid($acp->id);

            /*
             * Amount is still due
             */
            if(round($amountPaid,0) < round($amountDue,0))
            {
                //Create new pending approval for payment
                $approval = new \SwiftApproval([
                    'type' => \SwiftApproval::APC_PAYMENT
                ]);
                $acp->approval()->save($approval);
                return true;
            }
        }
        return false;
    }

    public static function checkpaymentToEnd($nodeActivity)
    {
        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $amountDue = $acp->invoice->due_amount;
            $amountPaid = \SwiftACPPayment::sumTotalAmountPaid($acp->id);

            /*
             * No amount due
             */
            if(round($amountPaid,0) >= round($amountDue,0))
            {
                return true;
            }
        }
        return false;
    }
}