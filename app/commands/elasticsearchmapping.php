<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class elasticsearchmapping extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'elasticsearchmapping:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Elastic search Mapping';

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
            //$context = $this->ask('What is the context?');
            $context = "aprequest";
            $client = new Elasticsearch\Client();
            switch($context)
            {
                case "order-tracking":
                    
                    break;
                case "aprequest":
                    $params = array();
                    $params['index'] = \App::environment();
                    $params['type'] = 'aprequest';
                    $params['body']['aprequest'] = array('properties' => [
                                                                    'id' => [
                                                                        'type'  => 'long'
                                                                    ],
                                                                    'requester_user_id' => [
                                                                        'type'  =>  'long',
                                                                        'index' => 'not_analyzed'
                                                                    ],
                                                                    'name' => [
                                                                        'type' => 'string',
                                                                    ],
                                                                    'description' => [
                                                                        'type' => 'string'
                                                                    ],
                                                                    'customer_name' => [
                                                                        'type' => 'string',
                                                                    ],
                                                                    'customer_code' => [
                                                                        'type' => 'integer',
                                                                    ],
                                                                    'product' => [
                                                                        'properties' => [
                                                                            'id' => [
                                                                                'type' => 'long',
                                                                                'index' => 'not_analyzed'
                                                                            ],
                                                                            'name' => [
                                                                                'type' => 'string'
                                                                            ],
                                                                            'jde_itm' => [
                                                                                'type' => 'string',
                                                                            ],
                                                                            'quantity' => [
                                                                                'type' => 'integer',
                                                                                'index' => 'not_analyzed'
                                                                            ],
                                                                            'price' => [
                                                                                'type' => 'float',
                                                                                'index' => 'not_analyzed'
                                                                            ],
                                                                            'reason_code' => [
                                                                                'type' => 'string'
                                                                            ],
                                                                            'reason_others' => [
                                                                                'type' => 'string'
                                                                            ]
                                                                        ]
                                                                    ],
                                                                    'order' =>  [
                                                                        'properties' => [
                                                                            'id' => [
                                                                                'type' => 'long',
                                                                                'index' => 'not_analyzed'
                                                                            ],                                                
                                                                            'ref' => [
                                                                                'type'  =>  'string'
                                                                            ],
                                                                            'type' => [
                                                                                'type' => 'string'
                                                                            ]
                                                                        ],
                                                                    ],
                                                                    'delivery' =>  [
                                                                        'properties' => [
                                                                            'id' => [
                                                                                'type' => 'long',
                                                                                'index' => 'not_analyzed'
                                                                            ],                                                 
                                                                            'status' => [
                                                                                'type'  =>  'string'
                                                                            ],
                                                                            'invoice_number' => [
                                                                                'type' => 'integer'
                                                                            ],
                                                                            'invoice_recipient' => [
                                                                                'type'  =>  'string'
                                                                            ],
                                                                            'delivery_person' => [
                                                                                'type'  =>  'string'
                                                                            ],
                                                                            'delivery_date' =>  [
                                                                                'type'  =>  'date'
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ]
                                                            );
                    break;
                default:
                    $this->error("We don't support this context!");
                    return;
            }
                   
            if($client->indices()->putMapping($params))
            {
                $this->info("Your mapping has been created for '$context'");
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