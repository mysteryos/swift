<?php

use Illuminate\Console\Command;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class commission extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'commission:calculate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Calculate Sales Commission for active salesman';

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
                $dateArray = [];
                if($this->option('month'))
                {
                    if(strpos($this->option('month'),",") !== false)
                    {
                        $monthArray = explode(",",$this->option('month'));
                    }
                    else
                    {
                        $monthArray[] = $this->option('month');
                    }
                    
                    foreach($monthArray as $m)
                    {
                        $dateArray[] = ['date_start'=>Carbon::createFromFormat('m-Y',$m)->day(1),
                                        'date_end'=>Carbon::createFromFormat('m-Y',$m)->addMonth()->day(0)
                                        ];
                    }
                }
                else
                {
                    $dateArray[] = ['date_start'=>(new Carbon ('first day of last month')),
                                    'date_end'=>(new Carbon ('last day of last month'))];
                }
                
                foreach($dateArray as $d)
                {
                    \SalesCommission::calculateAll($d['date_start'],$d['date_end']);
                }
            }
            Catch (\Exception $e)
            {
                Log::error('Sales commission - Error: '.$e->getMessage());
                $this->error($e->getMessage());
            }
            
	}
        
        /*
         * Add Schedule
         */
        public function schedule(Schedulable $scheduler)
        {
            //Every 1st of the Month at 2a.m,
            return $scheduler->monthly()->hours(2)->days(1);
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
		return [array('month', 'month', InputOption::VALUE_OPTIONAL, 'Target month for sales commission (m-Y)', new \Carbon('first day of last month'))];
	}
}
