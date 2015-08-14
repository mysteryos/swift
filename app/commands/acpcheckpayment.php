<?php
/*
 * Name: Accounts Payable - Check Payment
 * Description: Retries to update the workflow
 */

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class acpcheckpayment extends ScheduledCommand {

    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'acp:checkpay';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Updates ACP workflows when they are at check payment stage';

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
        /*
         * Workflow with check payment
         */

        $forms = \SwiftACPRequest::whereHas('workflow',function($q){
                    return $q->inprogress()->whereHas('pendingNodes',function($q){
                        return $q->whereHas('definition',function($q){
                            return $q->where('name','=','acp_checkpayment');
                        });
                    });
                })
                ->with(['invoice','paymentVoucher'])
                ->get();

        foreach($forms as $f)
        {
            /*
             * Fetch Latest Open amount
             */
            if(\Swift\AccountsPayable\JdeReconcialiation::updatePvOpenAmount($f))
            {
                \WorkflowActivity::update($f,'acpayable');
            }
        }

    }

    /*
    * Add Schedule
    */
   public function schedule(Schedulable $scheduler)
   {
       //Every Day at 4a.m
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

