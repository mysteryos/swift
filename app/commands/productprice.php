<?php
/*
 * Name: Product Price Updater
 * Description: Scourges the SCT JDE database for prices
 */

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class productprice extends ScheduledCommand {
    
        /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'productprice:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Searches SCT JDE sales for product prices and updates the swift DB';

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
                    $user = Sentry::findUserById(0);
                    // Login system user
                    Sentry::login($user, false);
                }
	}
        
        public function getName()
        {
            return $this->$name;
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
            /*
             * Check AP Table for products without prices and update accordingly
             */
            
        }        
        
/*
         * Add Schedule
         */
        public function schedule(Schedulable $scheduler)
        {
            //Every Day at 4a.m
            return $scheduler->daily()->hours(4)->minutes(30);
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

