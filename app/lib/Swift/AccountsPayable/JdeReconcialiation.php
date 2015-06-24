<?php
/*
 * Name: JDE Reconcialiation
 * Description: As title says
 */

namespace Swift\AccountsPayable;

class JdeReconcialiation {

    /*
     * Compares swift data with JDE data for Payment Vouchers
     *
     * @param \SwiftACPPaymentVoucher $pv
     * @return boolean
     */
    public static function reconcialiatePaymentVoucher(\SwiftACPPaymentVoucher &$pv)
    {
        if($pv->validated === \SwiftACPPaymentVoucher::VALIDATION_COMPLETE)
        {
            return true;
        }

        //Payment voucher number missing
        if(trim($pv->number) === "")
        {
            $pv->validated_msg = "Please input a payment voucher number";
            $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_ERROR;
            $pv->save();
            return false;
        }

        //Payment voucher number not found in database
        $jdePV = \JdePaymentVoucher::where('DOC','=',$pv->number)->first();
        if(!$jdePV)
        {
            $pv->validated_msg = "Payment Voucher Number not found in JDE Database";
            $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_ERROR;
            $pv->save();
            return false;
        }

        //Billable Company MisMatch

        if($jdePV->kco !== $pv->acp->billable_company_code)
        {
            $pv->validated_msg = "Billable company code mismatch: {$jdePV->kco}";
            $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_ERROR;
            $pv->save();
            return false;
        }

        //Supplier Code
        if($jdePV->an8 !== $pv->acp->supplier_code)
        {
            $pv->validated_msg = "Supplier code mismatch: {$jdePV->AN8}";
            $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_ERROR;
            $pv->save();
            return false;
        }

        //Invoice Number Mismatch
        if($pv->acp->invoice)
        {
            if($pv->acp->invoice->number !== null && trim($pv->acp->invoice->number) !== trim($jdePV->vinv))
            {
                $pv->validated_msg = "Supplier code mismatch: {$jdePV->AN8}";
                $error = true;
                return false;
            }
        }
        else
        {
            //No Invoices Present
            return false;
        }

        /*
         * All checks passed
         */

        $pv->validated_msg = null;
        $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_COMPLETE;
        $pv->save();
        
        return true;
    }

    /*
     * Compares swift data with JDE data for Payment
     *
     * @param \SwiftACPPayment $pay
     * @return boolean
     */
    public static function reconcialiatePayment(\SwiftACPPayment $pay)
    {
        if($pay->validated === \SwiftACPPayment::VALIDATION_COMPLETE)
        {
            return true;
        }

        if(trim($pay->payment_number) === "")
        {
            $pay->validated = \SwiftACPPayment::VALIDATION_ERROR;
            $pay->validated_msg = "Please input a payment number";
            $pay->save();
            return false;
        }

        $jdePay = \JdePaymentHeader::where('docm','=',$pay->payment_number)
                  ->first();

        if(!$jdePay)
        {
            $pay->validated = \SwiftACPPayment::VALIDATION_ERROR;
            $pay->validated_msg = "Payment number not found in JDE database";
            $pay->save();
            return false;
        }

        if($pay->amount !== "0.00" && $pay->amount !== abs($jdePay->paap))
        {
            $pay->validated = \SwiftACPPayment::VALIDATION_ERROR;
            $pay->validated_msg = "Payment amount(".abs($jdePay->paap).") doesn't match";
            $pay->save();
            return false;
        }

        if(($pay->batch_number !== null && $pay->batch_number !== "") && $pay->batch_number !== $jdePay->icu)
        {
            $pay->validated = \SwiftACPPayment::VALIDATION_ERROR;
            $pay->validated_msg = "Batch number(".$jdePay->icu.") doesn't match";
            $pay->save();
            return false;
        }

        if($pay->currency !== null && $pay->currency->code !== $jdePay->crrd)
        {
            $pay->validated = \SwiftACPPayment::VALIDATION_ERROR;
            $pay->validated_msg = "Currency(".$jdePay->crrd.") doesn't match";
            $pay->save();
            return false;
        }

        $pay->validated = \SwiftACPPAyment::VALIDATION_COMPLETE;
        $pay->validated_msg = null;
        $pay->save();
        return true;
    }

    public static function autofillPaymentVoucher(\SwiftACPPaymentVoucher &$pv)
    {
        
    }

    public static function autofillPayment(\SwiftACPPayment & $pay)
    {
        
    }
}

