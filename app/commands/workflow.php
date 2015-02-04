<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class workflow extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'workflow:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Processes all nodes in current pending workflows';

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
                $context = $this->ask('What context?');
                switch($context)
                {
                    case 'aprequest':
                        $forms = \SwiftAPRequest::whereHas('workflow',function($q){
                                    return $q->inprogress();
                                  })->get();
                        if(count($forms))
                        {
                            foreach($forms as $f)
                            {
                                \WorkflowActivity::update($f,'aprequest');
                                $this->info("Workflow Update on Form ID: ".$f->id);
                            }
                        }
                        else
                        {
                            $this->info("No Forms in progress");
                        }
                        break;
                    case 'order-tracking':
                        $forms = \SwiftOrder::whereHas('workflow',function($q){
                                    return $q->inprogress();
                                  })->get();
                        if(count($forms))
                        {
                            foreach($forms as $f)
                            {
                                \WorkflowActivity::update($f,'order-tracking');
                                $this->info("Workflow Update on Form ID: ".$f->id);
                            }
                        }
                        else
                        {
                            $this->info("No Forms in progress");
                        }
                        break;
                    default:
                        $this->info('We dont have this context. Available ones: aprequest, order-tracking');
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
