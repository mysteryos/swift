<?php

use Illuminate\Console\Command;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class jdetablefix extends Command {
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
	}

    public function getName()
    {
        return $this->name;
    }

    public function isEnabled() {
        return false;
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
                            ->groupBy(['order_type','order_number','order_company'])
                            ->get();
            foreach($orphanLines as $o)
            {
                //Search for ID
                $po = \JdePurchaseOrder::findByNumberTypeCompany($o->order_number,$o->order_type,$o->order_company);
                if($po)
                {
                    \DB::connection('sct_jde')
                    ->table('sct_jde.jdepodetail')
                    ->where('order_type','=',$o->order_type)
                    ->where('order_number','=',$o->order_number,'AND')
                    ->where('order_company','=',$o->order_company,'AND')
                    ->update(['order_id'=>$po->id]);
                    $this->info('Reconciliated Purchase Order ID: '.$po->id);
                }
                else
                {
                    \Log::error("JDETABLEFIX - Purchase Order was not found - Reference: ".$o->Order_Number." ".$o->Order_Type);
                }

            }
        }
        catch(Exception $e)
        {
            $this->error($e->getMessage());
            \Log::error($e);
        }
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