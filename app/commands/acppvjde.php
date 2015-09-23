<?php
/*
 * Name: Accounts Payable - Jde Reconciliation - Payment Vouchers
 * Description: Scourges the SCT JDE database for payment vouchers
 */

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class acppvjde extends ScheduledCommand {

    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'acp:pv';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Searches SCT JDE for Payment Voucher';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

    public function getName()
    {
        return $this->name;
    }

    public function isEnabled()
    {
        return true;
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try
        {
            /*
             * Get all payment voucher that aren't validated
             */

            $paymentVoucherList = \SwiftACPPaymentVoucher::with('acp','acp.invoice')
                                    ->where('validated','!=',\SwiftACPPaymentVoucher::VALIDATION_COMPLETE)
                                    ->get();

            foreach($paymentVoucherList as $pv)
            {
                if(\Swift\AccountsPayable\JdeReconcialiation::reconcialiatePaymentVoucher($pv))
                {
                    \Swift\AccountsPayable\JdeReconcialiation::autofillPaymentVoucher($pv);
                }
            }

            /*
             * Get all payment voucher that has been recently updated on JDE
             */

            $paymentVoucherList = \JdePaymentVoucher::groupBy('doc')
                                    ->select(\DB::raw('MAX(updated_at) as max_updated_at, doc, kco'))
                                    ->having(\DB::raw('max_updated_at'),'>=',\Carbon::now()->subDays(3))
                                    ->get();

            foreach($paymentVoucherList as $pv)
            {
                $localPv = \SwiftACPPaymentVoucher::where('number','=',$pv->doc)
                            ->where('type','=',$pv->dct,'AND')
                            ->whereHas('acp',function($q) use ($pv){
                                return $q->where('billable_company_code','=',$pv->kco);
                            })
                            ->where('validated','=',\SwiftACPPaymentVoucher::VALIDATION_COMPLETE)
                            ->first();
                if($localPv)
                {
                    \Swift\AccountsPayable\JdeReconcialiation::autofillPaymentVoucher($localPv);
                }
            }
        } 
        catch (Exception $ex)
        {
            $this->error($ex->getMessage());
            Log::error($ex);
        }

        
    }

    /*
    * Add Schedule
    */
   public function schedule(Schedulable $scheduler)
   {
       //Every Day at 6a.m
       return $scheduler->daily()->hours(6)->minutes(0);
   }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}
}