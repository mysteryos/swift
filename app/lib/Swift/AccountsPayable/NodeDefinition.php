<?php
/*
 * Name: A&P Request Node Definition
 * Description: Provides functions to handle Node Definition
 */

NameSpace Swift\AccountsPayable;

Class NodeDefinition {
    public function acpStart($nodeActivity)
    {
        return true;
    }

    public function acpPreparation($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

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

                if($invoice->date === "")
                {
                    $returnReasonList['date'] = "Enter invoice date issued";
                }

                if($invoice->due_date === "")
                {
                    $returnReasonList['invoice_due_date'] = "Enter invoice due date";
                }

                if($invoice->due_amount <= 0)
                {
                    $returnReasonList['invoice_due_amount'] = "Enter invoice due amount";
                }

                if($invoice->due_amount <= 0)
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
            $approvals = $acp->approval()->get();
            if(count($approvals) === 0)
            {
                $returnReasonList['hodapproval_absent'] = "Publish form and send to HOD for approval";
            }
            
            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }

        }

        return $returnReason ? $returnReasonList : false;
    }

    public function acpHodApproval($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $approvals = $acp->approval()->get();
            if(count($approvals))
            {
                foreach($approvals as $a)
                {
                    if(in_array($a->type,[\SwiftApproval::APPROVED,\SwiftApproval::REJECTED]))
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

    public function acpCreditnote($nodeActivity,$returnReason=false)
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

    public function acpPaymentvoucher($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

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

    public function acpPaymentissue($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
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

    public function acpChequeSign($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $chequeNotSignedCount = $acp
                                    ->payment()
                                    ->where('status','<',\SwiftACPRequest::STATUS_SIGNED)
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

    public function acpChequeReady($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($acp))
        {
            $chequeNotReadyCount = $acp
                                    ->payment()
                                    ->where('status','<',\SwiftACPRequest::STATUS_DISPATCHED)
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

    public function acpCheckpayment($nodeActivity,$returnReason=false)
    {
        return true;
    }

    public function acpBanktransfer($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

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

    public function acpEnd($nodeActivity)
    {
        return true;
    }
}