<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class dbclean extends Command {
/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'dbclean:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cleans Database completely';

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
                //List of tables to truncate
                $tables = ['swift_ap_product','swift_ap_request','swift_approval','swift_comments','swift_customs_declaration',
                            'swift_delivery','swift_document','swift_erp_order','swift_event','swift_flag','swift_freight','swift_node_activity','swift_node_activity_join',
                            'swift_notification','swift_order','swift_purchase_order','swift_recent','swift_reception','swift_shipment','swift_story','swift_tag','swift_workflow_activity'];
                foreach($tables as $t)
                {
                    \DB::table($t)->truncate();
                    $this->info('Truncated: '.$t);
                }
                $this->info('Success: Truncating Complete');
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
