<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class userlastseen extends ScheduledCommand {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'userlastseen:update';
        
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Updates last seen entry on database';
        
        
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
            $last_seen = Cache::get('last_seen',array());
            if(empty($last_seen))
            {
                foreach($last_seen as $email => $timestamp)
                {
                    $user = Sentry::findUserByLogin($email);
                    if($user->last_seen->diffInMinutes(new Carbon($timestamp)) < 1)
                    {
                        $user->last_seen = $timestamp;
                    }
                    $user->save();
                }
            }
        }        
        
        /*
         * Add Schedule
         */
        public function schedule(Schedulable $scheduler)
        {
            //Every Day at 3a.m
            return $scheduler->daily()->hours(3);
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