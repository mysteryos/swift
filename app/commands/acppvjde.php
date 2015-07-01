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
	protected $name = 'acppvjde:start';

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
        if (!\Sentry::check())
        {
            \Helper::loginSysUser();
        }
	}

    public function getName()
    {
        return $this->$name;
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
        
    }

    /*
    * Add Schedule
    */
   public function schedule(Schedulable $scheduler)
   {
       //Every Day at 4a.m
       return $scheduler->daily()->hours(4)->minutes(0);
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