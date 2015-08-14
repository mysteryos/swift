<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class jdeporeconcialiation extends ScheduledCommand {
    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jdeporeconcialiation:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'JDE Purchase Order Reconciliation - Adds Order Id to Swift_Purchase_order Table';

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
            try {
                //Pending purchase Orders First
                \Helper::validatePendingPurchaseOrder();

                //Not Found Purchase Orders
                \Helper::validateNotFoundPurchaseOrder();
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