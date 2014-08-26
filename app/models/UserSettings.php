<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class UserSettings extends Eloquent {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_settings';
        
        protected $guarded = array('id');
        
        protected $cache_duration = 60;
        
        private $cache_name;
        
        //List of allowed setting names
        protected $settings = array(
            
        );
        
        //Default value for setting if not declared.
        protected $defaults = array(
            
        );
        
        public function put($setting,$value)
        {
            
            self::validate($setting);
            
            // Retrieve the setting by the attributes, or instantiate a new instance...
            $setting = Self::firstOrNew(array('user_id' => Sentry::getUser()->id,'setting'=>$setting));
            //Set value
            $setting->value = $value;
            //Save to DB
            $setting->save();
            
            return true;
        }
        
        public function forget($setting)
        {
            
            self::validate($setting);            
            
            //Clear Cache
            
            if(Cache::has($this->cache_name))
            {
                Cache::forget($this->cache_name);
            }
            
            //Search in DB
            $setting = Self::find(array('user_id' => Sentry::getUser()->id,'setting'=>$setting));
            if($setting)
            {
                //Delete from DB
                $setting->delete();
            }
            return true;
        }
        
        public function get($setting)
        {
            
            self::validate($setting);
            
            $setting_value = Cache::remember($this->cache_name,self::$cache_duration,function(){
                $setting = Self::find(array('user_id' => Sentry::getUser()->id,'setting'=>$setting));

                if($setting)
                {
                    //Found in database
                    return $setting->value;
                }
                else
                {
                    //Try to assign default value
                    return isset(Self::$defaults[$setting]) ? Self::$defaults[$setting] : false;
                }                    
            });
            
            return $setting_value;
 
        }
        
        public function validate($setting)
        {
            if(in_array($setting,self::$settings))
            {
                //Set Cache name
                $this->cache_name = cacheName($setting);
                
                //Validation True
                return true;
            }
            else
            {
                App::abort(401, 'Settings value not authorized.');
            }
        }
        
        public function cacheName($setting)
        {
            return $setting.'_'.Sentry::getUser()->id;
        }
}