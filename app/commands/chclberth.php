<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class chclberth extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'chclberth:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Crawls CHCL website to extract current ships that are being unloaded';

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
        return false;
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
            @$dom->loadHTMLFile('http://www.chcl.mu/info/?id=49');
            foreach($dom->getElementsByTagName('td') as $td)
            {
                try
                {
                    if(strtolower($td->textContent) === 'berth')
                    {
                        $this->info('Vessel info found');
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
                                    if(strtolower($c->textContent) == "vessel")
                                    {
                                        unset($cargo);
                                        break;
                                    }
                                    else
                                    {
                                        $cargo['voyage'] = trim($c->textContent);
                                    }
                                }

                                if($count == 5)
                                {
                                    if(strtolower($c->textContent) == "started" || strtolower($c->textContent) == "eta")
                                    {
                                        unset($cargo);
                                        //It's the header, we skip
                                        break;
                                    }
                                    else
                                    {
                                        $cargo['date_start'] = $c->textContent;
                                    }
                                }
                            }

                            //Do save of cargo here
                            if(isset($cargo))
                            {
//                                $this->info(var_dump($cargo));
                                $cargo['date_start'] = Carbon::createFromFormat('d/m/Y',$cargo['date_start']);
                                $count = \CHCLLive::countWhereVoyageDateStart($cargo['voyage'],$cargo['date_start']->format('Y-m-d'));
                                if($count > 0)
                                {
                                    unset($cargo);
                                }
                                else
                                {
                                    /*
                                     * Save To DB
                                     */
                                    $savecargo = new CHCLLive($cargo);
                                    $savecargo->save();

                                    /*
                                     * Story
                                     */

                                    $orderProcesses = \SwiftOrder::query()->with(['freight'=>function($q){
                                                            return $q->where('freight_type','=',SwiftFreight::TYPE_SEA);
                                                        }])
                                                        ->whereHas('workflow',function($q){
                                                            return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                                                ->whereHas('nodes',function($q){
                                                                     return $q->whereHas('definition',function($q){
                                                                         return $q->where('name','=','ot_transit');
                                                                     })->where('user_id','=',0,'AND');
                                                                });
                                                        })->whereHas('freight',function($q){
                                                            return $q->whereNotNull('vessel_name')
                                                                ->whereNotNull('vessel_voyage')
                                                                ->where('freight_type','=',SwiftFreight::TYPE_SEA,'AND');
                                                        })
                                                        ->get();
                                    $matchScore = 0;
                                    $matchOrder = false;

                                    foreach($orderProcesses as $o)
                                    {
                                        if(count($o->freight))
                                        {
                                            $freightName = $o->freight->first()->vessel_name." ".$o->freight->first()->vessel_voyage;
                                            $this->info($freightName);
                                            similar_text($cargo['voyage'],$freightName,$similarScore);
                                            if($similarScore > $matchScore)
                                            {
                                                $this->info($similarScore);
                                                $matchScore = $similarScore;
                                                $matchOrder = $o;
                                            }
                                        }
                                    }

                                    $this->info('Match Score - '.$matchScore);
                                    $this->info('Match Order - '.$matchOrder->name);

                                    //Match by at least 90% - We create story
                                    if($matchScore >= 90)
                                    {
                                        Story::relate($savecargo,SwiftStory::ACTION_VESSEL,1,get_class($o),$o->id);
                                    }
                                    
                                }
                            }
                            else
                            {
                                $this->info('Cargo not found.');
                            }
                        }
                        break;
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
        return $scheduler->daily()->hours(4);
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
