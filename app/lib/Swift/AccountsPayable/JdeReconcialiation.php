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

    /*
     * Reconciliate Payment Voucher Task for Laravel Queue
     *
     * @param mixed $job
     * @param array $data
     *
     */
    public static function reconcialiatePaymentVoucherTask($job,$data)
    {
        if(isset($data['user_id']))
        {
            $user = \Sentry::findUserById($data['user_id']);

            // Log the user in
            \Sentry::login($user, false);
        }

        if(isset($data['class']) && isset($data['id']))
        {
            $eloqentClass = new $data['class'];
            $eloquentObject = $eloqentClass::find($data['id']);
            //Get payment voucher records
            $pvs = $eloquentObject->paymentVoucher->where('validated','!=',\SwiftACPPaymentVoucher::VALIDATION_COMPLETE)->get();
            foreach($pvs as $pv)
            {
                if(self::reconcialiatePaymentVoucher($pv))
                {
                    self::autofillPaymentVoucher($pv);
                }
            }
        }
        else
        {
            throw new \RuntimeException('Eloquent class or object ID missing');
        }
        $job->delete();
    }

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
        $jdePV = \JdePaymentVoucher::where('DOC','=',$pv->number)
                ->where('kco','=',sprintf('%05d', $pv->acp->billable_company_code),'AND')
                ->first();
        if(!$jdePV)
        {
            $pv->validated_msg = "Payment Voucher Number not found in JDE Database";
            $pv->validated = \SwiftACPPaymentVoucher::VALIDATION_ERROR;
            $pv->save();
            return false;
        }

        //Supplier Code
        if((int)$jdePV->an8 !== $pv->acp->supplier_code)
        {
            $pv->load('acp');
            $comment = new \SwiftComment([
                'comment' => "Supplier code mismatch: {$jdePV->AN8} for PV no: {$pv->number}",
                'user_id' => \Sentry::getUser()->id
            ]);

            $pv->acp->comments()->save($comment);
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
     * Reconciliate Payment Task for Laravel Queue
     *
     * @param mixed $job
     * @param array $data
     *
     */
    public static function reconcialiatePaymentTask($job,$data)
    {
        if(isset($data['user_id']))
        {
            $user = \Sentry::findUserById($data['user_id']);

            // Log the user in
            \Sentry::login($user, false);
        }

        if(isset($data['class']) && isset($data['id']))
        {
            $eloqentClass = new $data['class'];
            $eloquentObject = $eloqentClass::find($data['id']);
            //Get Payment Records
            $pays = $eloquentObject->payment()->where('validated','!=',\SwiftACPPayment::VALIDATION_COMPLETE)->get();
            foreach($pays as $pay)
            {
                if(self::reconcialiatePayment($pay))
                {
                    self::autofillPayment($pay);
                }
            }
        }
        else
        {
            throw new \RuntimeException('Eloquent class or object ID missing');
        }
        
        $job->delete();
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

        $pay->validated = \SwiftACPPAyment::VALIDATION_COMPLETE;
        $pay->validated_msg = null;
        $pay->save();
        return true;
    }

    /*
     * Autofill Invoice details based on Payment Voucher Info
     *
     * @param \SwiftACPPaymentVoucher $pv
     *
     * @return boolean
     */
    public static function autofillPaymentVoucher(\SwiftACPPaymentVoucher &$pv)
    {
        $jdePV = \JdePaymentVoucher::where('DOC','=',$pv->number)
                 ->where('kco','=',sprintf('%05d', $pv->acp->billable_company_code),'AND')->first();
        if($jdePV && $pv->validated === \SwiftACPPaymentVoucher::VALIDATION_COMPLETE)
        {
            $pv->load('invoice');
            $mapping = [
                'number' => 'vinv',
                'date'  => 'divj',
                'currency_code' => 'crcd',
                'gl_code' => 'glc',
                'due_date' => 'ddj'
            ];

            foreach($mapping as $col => $jdeCol)
            {
                if($jdeCol === 'ag')
                {
                    $pv->invoice->$col = abs($jdePV->$jdeCol);
                }
                else
                {
                    $pv->invoice->$col = $jdePV->$jdeCol;
                }
            }

            //Total Amounts

            $dueAmountTotal = \JdePaymentVoucher::where('DOC','=',$pv->number)
                                ->where('kco','=',sprintf('%05d', $pv->acp->billable_company_code),'AND')
                                ->groupBy('DOC')
                                ->sum('ag');
            $pv->invoice->due_amount = $dueAmountTotal;

            $openAmountTotal = \JdePaymentVoucher::where('DOC','=',$pv->number)
                                ->where('kco','=',sprintf('%05d', $pv->acp->billable_company_code),'AND')
                                ->groupBy('DOC')
                                ->sum('aap');
            $pv->invoice->open_amount = $dueAmountTotal;

            return $pv->invoice->save();
        }

        return false;
    }

    /*
     * Auto Fill Payment based on payment Number
     *
     * @param \SwiftACPPayment $pay
     *
     * @return boolean
     */
    public static function autofillPayment(\SwiftACPPayment &$pay)
    {
        $jdePay = \JdePaymentHeader::where('docm','=',$pay->payment_number)
                  ->first();

        if($jdePay && $pay->validated === \SwiftACPPAyment::VALIDATION_COMPLETE)
        {
            $mapping = [
                'batch_number' => 'icu',
                'currency_code' => 'crcd',
                'date' => 'dmtj',
                'amount' => 'paap',
            ];

            foreach($mapping as $col => $jdeCol)
            {
                if($jdeCol === 'paap')
                {
                    $pay->$col = abs($jdePay->$jdeCol);
                }
                else
                {
                    $pay->$col = $jdePay->$jdeCol;
                }
            }

            return $pay->save();
        }

        return false;
    }
}

