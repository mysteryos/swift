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
        if($acp)
        {
            /*
             * Approvals
             */
            $approvalHodCount = $acp->approval()->where('type','=',\SwiftApproval::APC_HOD)->count();
            if($approvalHodCount === 0)
            {
                $returnReasonList['hodapproval_absent'] = "Enter HOD's details for approval";
            }

            /*
             * Document
             */
            if($acp->document()->count() === 0)
            {
                $returnReasonList['document_absent'] = "Upload the invoice scanned document";
            }

            //Requester didn't publish
            $approvalRequesterCount = $acp->approval()->where('type','=',\SwiftApproval::APC_REQUESTER)->count();
            if($approvalRequesterCount === 0)
            {
                $returnReasonList['requester_absent'] = "Please publish the form";
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
            $pendingApprovals = $acp->approvalHod()->where('approved','=',\SwiftApproval::PENDING)->get();
            if(count($pendingApprovals) > 0)
            {
                //Still have pending approvals
                $returnReasonList['approval_pending'] = "Awaiting approval from ".implode(", ",array_map(function($v){
                                                            return $v['approval_user_name'];
                                                        },$pendingApprovals->toArray()));
            }
            else
            {
                //No Pending
                return true;
            }
            
            if(count($returnReasonList) === 0 && !$returnReason)
            {
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function acpCreditnote($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
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
             * Credit Note Check
             */
            $creditNoteCount = $acp->creditnote()->count();
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
        if($acp)
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
        if($acp)
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
                    if($p->amount === "0.00" || $p->amount === null)
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
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();

        if($acp)
        {
            //Verify Pending Payment Vouchers
            $paymentVoucherPending = $acp->paymentVoucher()->where('validated','=',\SwiftACPPaymentVoucher::VALIDATION_PENDING)->count();
            if($paymentVoucherPending > 0)
            {
                $returnReasonList['pv_pending'] = "Payment voucher validation by system is pending";
            }

            //Verify Payment Voucher Errors
            $paymentVoucherError = $acp->paymentVoucher()->where('validated','=',\SwiftACPPaymentVoucher::VALIDATION_ERROR)->count();
            if($paymentVoucherError)
            {
                $returnReasonList['pv_error'] = "Payment voucher errors have been found. See Payment voucher section";
            }

            //Verify Pending Payment Numbers
            $paymentPending = $acp->payment()->where('validated','=',\SwiftACPPayment::VALIDATION_PENDING)->count();
            if($paymentPending > 0)
            {
                $returnReasonList['payment_pending'] = "Payment validation by system is pending";
            }

            $paymentError = $acp->payment()->where('validated','=',\SwiftACPPayment::VALIDATION_ERROR)->count();
            if($paymentError > 0)
            {
                $returnReasonList['payment_error'] = "Payment errors have been found. See payment section";
            }
            
        }
        return $returnReason ? $returnReasonList : false;
    }

    public static function acpBanktransfer($nodeActivity,$returnReason=false)
    {
        $returnReasonList = array();

        $acp = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($acp)
        {
            $bankNoJournalEntryCount = $acp
                                        ->payment()
                                        ->where('type','=',\SwiftACPPayment::TYPE_BANKTRANSFER)
                                        ->where('payment_number','=',0)
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