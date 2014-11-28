<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class elasticsearch extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'elasticsearch:index';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Elastic search Re-index';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
            try {
                //Crawl all Order-tracking and add to elasticsearch
                $ordertrackingall = SwiftOrder::all();
                foreach($ordertrackingall as $o)
                {
                    $params = array();
                    $params['index'] = App::environment();
                    $params['type'] = 'order-tracking';
                    $params['id'] = $o->id;
                    $params['timestamp'] = $o->updated_at->toIso8601String();
                    $params['body']['order'] = $o->toArray();
                    $params['body']['purchaseOrder'] = $o->purchaseOrder()->get()->toArray();
                    $params['body']['reception'] = $o->reception()->get()->toArray();
                    $params['body']['freight'] = $o->freight()->get()->toArray();
                    $params['body']['shipment'] = $o->shipment()->get()->toArray();
                    $params['body']['customsDeclaration'] = $o->customsDeclaration()->get()->toArray();
                    Es::index($params);
                    $this->info('Indexed ID:'.$o->id);
                }
            } 
            catch(Exception $e)
            {
                $this->error($e->getMessage());
                Log::error($e);
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
