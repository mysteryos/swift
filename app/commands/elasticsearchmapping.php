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
            $context = $this->ask('What is the context?');
            $client = new Elasticsearch\Client();
            $params = array();
            $params['index'] = \App::environment();            
            switch($context)
            {
                case "order-tracking":
                    $params['type'] = 'order-tracking';
                    $params['body']['order-tracking'] = ["dynamic"=> "strict",
                                                        "properties"=>[
                                                            "order-tracking"=>["properties"=>[
                                                                "id"=>["type"=>"long"],
                                                                "name"=>["type"=>"string"],
                                                                "business_unit"=>["type"=>"string"],
                                                                "description"=>["type"=>"string"],
                                                            ]],
                                                            "customsDeclaration"=>["properties"=>[
                                                                "id"=>["type"=>"long",'index' => 'no'],                                                                
                                                                "customs_cleared_at"=>["type"=>"string",'index' => 'no'],
                                                                "customs_filled_at"=>["type"=>"string",'index' => 'no'],
                                                                "customs_processed_at"=>["type"=>"string",'index' => 'no'],
                                                                "customs_reference"=>["type"=>"string"],
                                                                "customs_status"=>["type"=>"string"],
                                                                "customs_under_verification_at"=>["type"=>"string",'index' => 'no'],
                                                            ]],
                                                            "freight"=>["properties"=>[
                                                                "bol_no"=>["type"=>"string"],
                                                                "freight_eta"=>["type"=>"date",'index' => 'no'],
                                                                "freight_etd"=>["type"=>"date",'index' => 'no'],
                                                                "freight_type"=>["type"=>"string"],
                                                                "freight_company"=>["type"=>"string"],
                                                                "id"=>["type"=>"long","index" => "no"],
                                                                "incoterms"=>["type"=>"string"],
                                                                "vessel_name"=>["type"=>"string"],
                                                                "vessel_voyage"=>["type"=>"string"]
                                                            ]],
                                                            "purchaseOrder"=>["properties"=>[
                                                                "id"=>["type"=>"long","index" => "no"],
                                                                "reference"=>["type"=>"string"]
                                                            ]],
                                                            "reception"=>["properties"=>[
                                                                "id"=>["type"=>"long","index" => "no"],                                                                
                                                                "grn"=>["type"=>"long"],
                                                                "reception_date"=>["type"=>"date","index" => "no"],
                                                                "reception_user"=>["type"=>"string"],
                                                            ]],
                                                            "shipment"=>["properties"=>[
                                                                "id"=>["type"=>"long","index" => "no"],                                                                
                                                                "gross_weight"=>["type"=>"float","index" => "no"],
                                                                "type"=>["type"=>"string"],
                                                                "container_no"=>["type"=>"string","index"=>"not_analyzed"]
                                                            ]],
                                                            "storage"=>["properties"=>[
                                                                "id"=>["type"=>"long","index"=>"no"],
                                                                "storage_start"=>["type"=>"date","index"=>"no"],
                                                                "demurrage_start"=>["type"=>"date","index"=>"no"],
                                                                "invoice_no"=>["type"=>"string","index"=>"not_analyzed"],
                                                                "storage_charges"=>["type"=>"float","index"=>"no"],
                                                                "demurrage_charges"=>["type"=>"float","index"=>"no"],
                                                                "reason"=>["type"=>"string","index"=>"no"]
                                                            ]]
                                                        ]];
                    break;
                case "aprequest":
                    $params['type'] = 'aprequest';
                    $params['body']['aprequest'] = array("dynamic"=> "strict",
                                                        'properties' => [
                                                                    'aprequest' => [
                                                                        'properties' => [
                                                                            'id' => [
                                                                                'type'  => 'long'
                                                                            ],
                                                                            'requester_user_id' => [
                                                                                'type'  =>  'long',
                                                                                'index' => 'no'
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
                                                                        ]
                                                                    ],
                                                                    'product' => [
                                                                        'properties' => [
                                                                            'id' => [
                                                                                'type' => 'long',
                                                                                'index' => 'no'
                                                                            ],
                                                                            'name' => [
                                                                                'type' => 'string'
                                                                            ],
                                                                            'jde_itm' => [
                                                                                'type' => 'string',
                                                                            ],
                                                                            'quantity' => [
                                                                                'type' => 'integer',
                                                                                'index' => 'no'
                                                                            ],
                                                                            'price' => [
                                                                                'type' => 'float',
                                                                                'index' => 'no'
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
                                                                                'index' => 'no'
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
                                                                                'index' => 'no'
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