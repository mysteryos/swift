<?php
/*
 * Name: A&P Request Node Definition
 * Description: Provides functions to handle Node Definition
 */

NameSpace Swift\AccountsPayable;

Class NodeDefinition {
    public static function acpStart($nodeActivity)
    {
        return true;
    }

    public static function acpPreparation($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            /*
             * invoice check
             */
            $invoiceArray = $acp->invoice()->get();
            if(count($invoiceArray))
            {
                $invoice = $invoiceArray->first();
                //Verify if invoice has all necessary information

                if($invoice->date === null)
                {
                    $returnReasonList['date'] = "Enter invoice date issued";
                }

                if($invoice->due_date === null)
                {
                    $returnReasonList['invoice_due_date'] = "Enter invoice due date";
                }

                if($invoice->due_amount <= 0)
                {
                    $returnReasonList['invoice_due_amount'] = "Enter invoice due amount";
                }

                if($invoice->payment_term <= 0)
                {
                    $returnReasonList['payment_term'] = "Enter invoice payment term";
                }
            }
            else
            {
                 $returnReasonList['invoice_absent'] = "Enter invoice details";
            }

            /*
             * Approvals
             */
            $approvalHodCount = $acp->approval()->where('type','=',\SwiftApproval::APC_HOD)->count();
            if($approvalHodCount === 0)
            {
                $returnReasonList['hodapproval_absent'] = "Enter HOD's details for approval";
            }

            //Requester didn't publish
            $approvalRequesterCount = $acp->approval()->where('type','=',\SwiftApproval::APC_REQUESTER)->count();
            if($approvalRequesterCount === 0)
            {
                $returnReasonList['requester_absent'] = "Publish form";
            }
            
            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }

        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpHodApproval($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $approvals = $acp->approvalHod()->get();
            if(count($approvals))
            {
                foreach($approvals as $a)
                {
                    if(in_array($a->approved,[\SwiftApproval::APPROVED,\SwiftApproval::REJECTED]))
                    {
                        return true;
                    }
                }
            }
            $returnReasonList['approval_absent'] = "Awaiting approval of HOD";

            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpCreditnote($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $creditNoteCount = $acp->creditNote()->count();
            if($creditNoteCount === 0)
            {
                $returnReasonList['creditnote_absent'] = "Input credit note for cancelled invoice";
            }

            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpPaymentvoucher($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $paymentVoucherCount = $acp->paymentVoucher()->count();
            if($paymentVoucherCount === 0)
            {
                $returnReasonList['paymentvoucher_absent'] = "Input payment voucher details for invoice";
            }
            
            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpPaymentissue($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $acp->load('payment');
            if(count($acp->payment) === 0)
            {
                $returnReasonList['payment_absent'] = "Input payment details";
            }
            else
            {
                //all payments should have an amount
                foreach($acp->payment as $p)
                {
                    if((float)$p->amount === 0 || $p->amount === null)
                    {
                        $returnReasonList['payment_amount_absent'] = "Input amount for payment ID: ".$p->id;
                        break;
                    }
                }
            }

            //Payment Issue - Approval check
            $paymentPendingApprovalCount = $acp->approval()
                                    ->where('type','=',\SwiftApproval::APC_PAYMENT)
                                    ->where('approved','=',\SwiftApproval::PENDING)->count();
            
            if($paymentPendingApprovalCount > 0)
            {
                $returnReasonList['approval_absent'] = "Please publish the form";
            }

            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
            
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpChequeSign($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $chequeNotSignedCount = $acp
                                    ->payment()
                                    ->where('status','<',\SwiftACPPayment::STATUS_SIGNED)
                                    ->where('type','=',\SwiftACPPayment::TYPE_CHEQUE)
                                    ->count();
            
            if($chequeNotSignedCount > 0)
            {
                $returnReasonList['chequenot_signed'] = "Please set cheque status to signed where necessary";
            }
            
            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpChequeReady($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $chequeNotReadyCount = $acp
                                    ->payment()
                                    ->where('status','<',\SwiftACPPayment::STATUS_DISPATCHED)
                                    ->where('type','=',\SwiftACPPayment::TYPE_CHEQUE)
                                    ->count();
            if($chequeNotReadyCount > 0)
            {
                $returnReasonList['chequenot_ready'] = "Please set cheque status to dispatched where necessary";
            }

            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
            
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpCheckpayment($nodeActivity,$returnReason=false)
    {
        return true;
    }

    public static function acpBanktransfer($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $bankNoJournalEntryCount = $acp
                                        ->payment()
                                        ->where('type','=',\SwiftACPPayment::TYPE_BANKTRANSFER)
                                        ->where('journal_entry_number','=',0)
                                        ->count();

            if($bankNoJournalEntryCount > 0)
            {
                $returnReasonList['banknojournalentry'] = "Please set a journal entry for bank trasnfers";
            }

            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpEnd($nodeActivity)
    {
        return true;
    }
}