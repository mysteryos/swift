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
            $context = $this->ask('What is the context?');
            if(!\Config::has('elasticsearchmapping.'.$context))
            {
                $this->error("We don't support this context!");
                return;
            }
            
            $action = $this->ask("Create or Delete or Recreate?");
            switch(strtolower($action))
            {
                case "create":
                    $params['type'] = $context;
                    $params['body'][$context] = Config::get('elasticsearchmapping.'.$context);
                    if($client->indices()->putMapping($params))
                    {
                        $this->info("Your mapping has been created for '$context'");
                    }
                    break;
                case "delete":
                    $params['type'] = $context;
                    if($client->indices()->deleteMapping($params))
                    {
                        $this->info("Your mapping has been deleted for '$context'");
                    }
                    break;
                case "recreate":
                    $delparams['index'] = $createparams['index'] = \App::environment();
                    $delparams['type'] = $context;
                    $createparams['type'] = $context;
                    $createparams['body'][$context] = Config::get('elasticsearchmapping.'.$context);
                    if($client->indices()->deleteMapping($delparams))
                    {
                        $this->info("Your mapping has been deleted for '$context'");
                    }
                    if($client->indices()->putMapping($createparams))
                    {
                        $this->info("Your mapping has been created for '$context'");
                    }
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