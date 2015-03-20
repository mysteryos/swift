<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class elasticsearchdaily extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'elasticsearchdaily:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Elastic search index daily';

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
            try {
                $supplierAll = \JdeSupplierMaster::all();
                foreach($supplierAll as $s)
                {
                    $params = array();
                    $params['index'] = \App::environment();
                    $params['type'] = 'supplier';
                    $params['id'] = $s->Supplier_Code;
                    $params['timestamp'] = Carbon::now()->toIso8601String();
                    $params['body']['supplier']['id'] = $s->Supplier_Code;
                    $params['body']['supplier']['name'] = $s->Supplier_Name;
                    $params['body']['supplier']['city'] = $s->Supplier_City;
                    $params['body']['supplier']['vat'] = $s->Supplier_LongAddNo;
                    \Es::index($params);
                    $this->info('Supplier Indexed ID:'.$s->Supplier_Code);
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
        return $scheduler->daily()->hours(4)->minutes(10);
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
