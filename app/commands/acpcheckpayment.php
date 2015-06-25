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
	protected $name = 'acpcheckpayment:start';

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
        return false;
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

        $forms = \SwiftAPRequest::whereHas('workflow',function($q){
                    return $q->inprogress()->whereHas('pendingNodes',function($q){
                        return $q->whereHas('definition',function($q){
                            return $q->where('name','=','acp_checkpayment');
                        });
                    });
                })
                ->get();

        foreach($forms as $f)
        {
            \Workflow::update($f,'acpayable');
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

