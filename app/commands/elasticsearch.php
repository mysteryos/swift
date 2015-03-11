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
	protected $name = 'elasticsearch:start';

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
                $context = $this->ask('What is the context?');
                switch($context)
                {
                    case "order-tracking":
                        //Crawl all Order-tracking and add to elasticsearch
                        $ordertrackingall = \SwiftOrder::all();
                        foreach($ordertrackingall as $o)
                        {
                            $params = array();
                            $params['index'] = \App::environment();
                            $params['type'] = 'order-tracking';
                            $params['id'] = $o->id;
                            $params['timestamp'] = $o->updated_at->toIso8601String();
                            $params['body']['order-tracking'] = \ElasticSearchHelper::saveFormat($o);
                            $params['body']['purchaseOrder'] = \ElasticSearchHelper::saveFormat($o->purchaseOrder()->get());
                            $params['body']['reception'] = \ElasticSearchHelper::saveFormat($o->reception()->get());
                            $params['body']['freight'] = \ElasticSearchHelper::saveFormat($o->freight()->get());
                            $params['body']['shipment'] = \ElasticSearchHelper::saveFormat($o->shipment()->get());
                            $params['body']['customsDeclaration'] = \ElasticSearchHelper::saveFormat($o->customsDeclaration()->get());
                            \Es::index($params);
                            $this->info('OT Indexed ID:'.$o->id);
                        }
                        break;
                    case "aprequest":
                        $aprequestall = \SwiftAPRequest::all();
                        foreach($aprequestall as $ap)
                        {
                            $params = array();
                            $params['index'] = \App::environment();
                            $params['type'] = 'aprequest';
                            $params['id']= $ap->id;
                            $params['timestamp'] = $ap->updated_at->toIso8601String();
                            $params['body']['aprequest'] = \ElasticSearchHelper::saveFormat($ap);
                            $params['body']['product'] = \ElasticSearchHelper::saveFormat($ap->product()->get());
                            $params['body']['delivery'] = \ElasticSearchHelper::saveFormat($ap->delivery()->get());
                            $params['body']['order'] = \ElasticSearchHelper::saveFormat($ap->order()->get());

                            \Es::index($params);
                            $this->info('APR Indexed ID:'.$ap->id);
                        }
                        break;
                    case "acpayable":
                        $acpayableall = \SwiftACPRequest::all();
                        foreach($acpayableall as $acp)
                        {
                            $params = array();
                            $params['index'] = \App::environment();
                            $params['type'] = 'acpayable';
                            $params['id']= $acp->id;
                            $params['timestamp'] = $acp->updated_at->toIso8601String();
                            $params['body']['acpayable'] = \ElasticSearchHelper::saveFormat($acp);
                            $params['body']['payment'] = \ElasticSearchHelper::saveFormat($acp->payment()->get());
                            $params['body']['invoice'] = \ElasticSearchHelper::saveFormat($acp->invoice()->get());
                            $params['body']['purchaseOrder'] = \ElasticSearchHelper::saveFormat($acp->purchaseOrder()->get());
                            $params['body']['paymentVoucher'] = \ElasticSearchHelper::saveFormat($acp->paymentVoucher()->get());
                            $params['body']['creditNote'] = \ElasticSearchHelper::saveFormat($acp->creditNote()->get());
                            \Es::index($params);
                            $this->info('ACP Indexed ID:'.$acp->id);
                        }
                        break;
                    default:
                        $this->error("We don't support this context!");
                        break;
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
