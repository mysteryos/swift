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
                case "aprequest":
                case "acpayable":
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