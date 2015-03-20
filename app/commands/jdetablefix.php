<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class jdetablefix extends ScheduledCommand {
    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jdetablefix:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'JDE Table Fix - Reconciliate Tables For Eloquent Compatiblity';

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
                $orphanLines = \JdePurchaseOrderItem::whereNull('order_id')
                                ->groupBy(['Order_Type','Order_Number'])
                                ->get();
                foreach($orphanLines as $o)
                {
                    //Search for ID
                    $po = \JdePurchaseOrder::findByNumberAndType($o->Order_Number,$o->Order_Type);
                    if($po)
                    {
                        \DB::connection('sct_jde')
                        ->table('sct_jde.jdepodetail')
                        ->where('Order_Type','=',$o->Order_Type)
                        ->where('Order_Number','=',$o->Order_Number,'AND')
                        ->update(['order_id'=>$po->id]);
                        $this->info('Reconciliated Purchase Order ID: '.$po->id);
                    }
                    else
                    {
                        Log::error("JDETABLEFIX - Purchase Order was not found - Reference: ".$o->Order_Number." ".$o->Order_Type);
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
        return $scheduler->daily()->hours(4);
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