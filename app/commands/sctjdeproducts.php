<?php
/*
 * Name: SCT JDE Database Updater
 * Description: Runs Calculations for Cost Price of Jde Products (For now)
 */

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class sctjdeProducts extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'sctjde:product';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Calculates Cost for SCT JDE table jdeProducts, and save it in CostPrice Column';

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
        $limit = 10;
        try
        {
            //Parse products, 10 at a time
            $productCount = \JdeProduct::count();
            for($i = 0;$i<=$productCount; $i=$i+$limit)
            {
                $products = \JdeProduct::take($limit)->offset($i)->get();
                foreach($products as $p)
                {
                    //Get last sales item for product
                    $sales = \JdeSales::getProductLatestCostPrice($p->ITM);
                    if($sales)
                    {
                       $cost = round(abs($sales->ECST/$sales->SOQS),4);
                       $p->cost_price = $cost;
                       $p->save();
                       $this->info("Save: ".trim($p->DSC1)." - Rs. ".$cost);
                    }
                    else
                    {
                        $this->info("Skipped: ".trim($p->DSC1));
                    }
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
        return $scheduler->weekly()->hours(4);
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

