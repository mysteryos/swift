<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class chclcrawler extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'chclcrawler:crawl';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Crawls CHCL website to extract storage information';

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
            $dom = new DOMDocument();
            $dom->strictErrorChecking = false;
            @$dom->loadHTMLFile('http://www.chcl.mu/info/?id=30');
            foreach($dom->getElementsByTagName('td') as $td)
            {
                try
                {
                    if($td->textContent == 'STORAGE REEFER')
                    {
                        $this->info('Storage info found');
                        $parentTable = $td->parentNode->parentNode;

                        $childrenTr = $parentTable->childNodes;
                        foreach($childrenTr as $tr)
                        {
                            $this->info('Children node found');
                            //
                            $td = $tr->childNodes;
                            $count = 0;
                            $cargo = array();
                            foreach($td as $c)
                            {
                                $count++;
                                if($count == 1)
                                {
                                    if($c->textContent == "VESSEL")
                                    {
                                        unset($cargo);
                                        break;
                                    }
                                    else
                                    {
                                        $cargo['vessel'] = $c->textContent;
                                    }
                                }

                                if($count == 3)
                                {
                                    if($c->textContent == "CODE")
                                    {
                                        unset($cargo);
                                        //It's the header, we skip
                                        break;
                                    }
                                    else
                                    {
                                        $cargo['code'] = $c->textContent;
                                    }
                                }

                                if($count == 5) // Voy
                                {
                                    $cargo['voy'] = $c->textContent;
                                }

                                if($count == 7) //Date Start
                                {
                                    $cargo['date_start'] = Carbon::createFromFormat('d/m/Y', $c->textContent);
                                }
                                
                                if($count == 7) // Discharge
                                {
                                    $cargo['discharge'] = Carbon::createFromFormat('d/m/Y', $c->textContent);
                                }
                                
                                if($count == 11) // Storage
                                {
                                    $cargo['storage'] = Carbon::createFromFormat('d/m/Y', $c->textContent);
                                }

                                if($count == 15) // Storage Rate
                                {
                                    $cargo['storage_rate'] = trim(str_replace("Rs.","",$c->textContent));
                                }
                            }

                            $this->info(var_dump($cargo));

                            //Do save of cargo here
                            if(isset($cargo) && isset($cargo['storage_rate']))
                            {
                                $count = CHCLStorage::getByVesselAndVoyage($cargo['vessel'],$cargo['voy']);
                                if($count > 0)
                                {
                                    unset($cargo);
                                }
                                else
                                {
                                    /*
                                     * Save To DB
                                     */
                                    $savecargo = new CHCLStorage($cargo);
                                    $savecargo->save();
                                    
                                    /*
                                     * Save to ElasticSearch Index
                                     */
                                    
//                                    $params = array('index'=>'chcl',
//                                                    'type'=>'storage',
//                                                    'id'=>$savecargo->id,
//                                                    'body'=>$cargo);
//                                    Es::index($params);
                                }
                            }
                            else
                            {
                                $this->info('Cargo found, Storage rate not found');
                            }
                        }
                        break;
                    }
                    else
                    {
                        $this->info('Storage info NOT found');
                    }
                }
                catch(Exception $e)
                {
                    $this->info($e->getMessage());
                    Log::error($e);
                }
            }
	}
        
        /*
         * Add Schedule
         */
        public function schedule(Schedulable $scheduler)
        {
            //Every Day at 4a.m
            return $scheduler->daily()->hours(12);
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
