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

class aprequestCompleter extends ScheduledCommand {
        /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'aprequest:completer';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Completes A&P Requests older than 1 month';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
        if ( ! Sentry::check())
        {
            Helper::loginSysUser();
        }
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
                $lateApRequest = SwiftAPRequest::whereHas('workflow',function($q){
                                    $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')->where('updated_at','<',new Datetime('today -1 month'))
                                    ->whereHas('nodes',function($q){
                                        return $q->where('user_id','=',0)->whereHas('definition',function($q){
                                            return $q->where('name','=','apr_delivery');
                                        });
                                    });
                                })
                                ->whereHas('order',function($q){
                                    return $q->whereNotNull('ref')->whereNotNull('type')->where('status','=',\SwiftErpOrder::FILLED,'AND');
                                })
                                ->with('order')
                                ->get();

                foreach($lateApRequest as $apr)
                {
                    foreach($apr->order as $order)
                    {
                        //Check if order has JDE sales row
                        //Therefore Invoice has already been published for that order, we mark it as complete.
                        
                    }
                }
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
            return $scheduler->daily()->hours(5);
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