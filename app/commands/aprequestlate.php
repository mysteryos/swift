<?php
/*
 * Name: AP Request Tasks
 * Description: Sends mail to requester if A&P Processing is late
 */

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class apRequestLate extends ScheduledCommand {
        /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'aprequest:late';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends Mail to A&P requesters if their A&P is late';

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

    public function isEnabled() {
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
               $lateApRequest = SwiftAPRequest::whereHas('SwiftWorkflowActivity',function($q){
                                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')->where('updated_at','<',new Datetime('today -7 days'));
                                })->whereHas('SwiftReminder',function($q){
                                    return $q->where('updated_at','<',new Datetime('today -7 days'));
                                },0);
                
            }
            catch(Exception $e)
            {
                $this->error($e->getMessage());
                Log::error($e);
            }
	}
        
        /*
         * Add Schedule
         */
        public function schedule(Schedulable $scheduler)
        {
            //Every Day at 4a.m
            return $scheduler->daily()->hours(8);
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