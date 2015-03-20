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
            $client = new Elasticsearch\Client();
            $params = array();
            $params['index'] = \App::environment();
            
            $action = $this->ask("Create or Delete?");
            switch(strtolower($action))
            {
                case "create":
                    $context = $this->ask('What is the context?');
                    switch($context)
                    {
                        case "order-tracking":
                        case "aprequest":
                        case "acpayable":
                        case "supplier":
                            $params['type'] = $context;
                            $params['body'][$context] = Config::get('elasticsearchmapping.'.$context);
                            break;
                        default:
                            $this->error("We don't support this context!");
                            return;
                    }
                    if($client->indices()->putMapping($params))
                    {
                        $this->info("Your mapping has been created for '$context'");
                    }
                    return;
                    break;
                case "delete":
                    $context = $this->ask('What is the context?');
                    switch($context)
                    {
                        case "order-tracking":
                        case "aprequest":
                        case "acpayable":
                        case "supplier":
                            $params['type'] = $context;
                            break;
                        default:
                            $this->error("We don't support this context!");
                            return;
                    }
                    
                    if($client->indices()->deleteMapping($params))
                    {
                        $this->info("Your mapping has been deleted for '$context'");
                    }
                    return;
                    break;
                default:
                    $this->error("This action is not supported.");
                    break;
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