<?php

Namespace Swift;

trait ElasticSearchEventTrait {
    public $esRemoveDefault = array('created_at','updated_at','deleted_at');
    
    public static function boot()
    {
        parent::boot();
        
        static::bootElasticSearchEvent();
    }
    
    public static function bootElasticSearchEvent()
    {
        static::created(function ($model) {
            if(isset($model->esEnabled) && $model->esEnabled === true)
            {
                $model->esCreate();
            }
        });

        static::updated(function ($model) {
            if(isset($model->esEnabled) && $model->esEnabled === true)
            {            
                $model->esUpdate();
            }
        });

        static::deleted(function ($model) {
            if(isset($model->esEnabled) && $model->esEnabled === true)
            {
                $model->esUpdate();
            }
        });
    }
    
    private function esCreate()
    {
        //esContext is false for polymorphic relations with no elasticsearch indexing
        if(isset($this->esMain) && $this->esMain === true && $this->esContext !== false)
        {
            \Queue::push('ElasticSearchHelper@indexTask',array('id'=>$this->esGetId(),'class'=>get_class($this),'context'=>$this->esGetContext(),'info-context'=>$this->esGetInfoContext(),'excludes'=>$this->esGetRemove()));
        }
        else
        {
            $this->esUpdate();
        }
    }
    
    private function esUpdate()
    {
        //esContext is false for polymorphic relations with no elasticsearch indexing
        if($this->esContext !== false)
        {
            \Queue::push('ElasticSearchHelper@updateTask',array('id'=>$this->esGetId(),'class'=>get_class($this),'context'=>$this->esGetContext(),'info-context'=>$this->esGetInfoContext(),'excludes'=>$this->esGetRemove()));
        }
    }
    
    /*
     * Get Id of Model
     */
    public function esGetId()
    {
        if(isset($this->esId))
        {
            return $this->esId;
        }
        else
        {
            return $this->id;
        }
    }
    
    public function esGetInfoContext()
    {
        if(isset($this->esInfoContext))
        {
            return $this->esInfoContext;
        }
        else
        {
            throw new \RuntimeException("esInfoContext attribute or esGetInfoContext() is not set in class '".get_class($this)."'");
        }
    }
    
    /*
     * Name of main context of model
     */
    public function esGetContext()
    {
        if(isset($this->esContext))
        {
            return $this->esContext;
        }
        else
        {
            throw new \RuntimeException("esContext attribute or esGetContext() method must be set in class '".get_class($this)."'");
        }
    }
    
    /*
     * All attributes that needs to be removed from model
     */
    public function esGetRemove()
    {
        if(isset($this->esRemove))
        {
            return array_unique(array_merge($this->esRemoveDefault,$this->esRemove));
        }
        else
        {
            return $this->esRemoveDefault;
        }
    }
    
    /*
     * Extends Illuminate Collection to provide additional array functions
     */
    public function newCollection(array $models = Array())
    {
        return new Core\Collection($models);
    }
    
    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    public function asEsDateTime($value)
    {
            // If this value is an integer, we will assume it is a UNIX timestamp's value
            // and format a Carbon object from this timestamp. This allows flexibility
            // when defining your date fields as they might be UNIX timestamps here.
            if (is_numeric($value))
            {
                    return \Carbon::createFromTimestamp($value);
            }

            // If the value is in simply year, month, day format, we will instantiate the
            // Carbon instances from that format. Again, this provides for simple date
            // fields on the database, while still supporting Carbonized conversion.
            elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
            {
                    return \Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            }

            // Finally, we will just assume this date is in the format used by default on
            // the database connection and use that format to create the Carbon object
            // that is returned back out to the developers after we convert it here.
            elseif ( ! $value instanceof DateTime)
            {
                    $format = $this->getEsDateFormat();

                    return \Carbon::createFromFormat($format, $value);
            }

            return \Carbon::instance($value);
    }
    
    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    private function getEsDateFormat()
    {
            return $this->getConnection()->getQueryGrammar()->getDateFormat();
    }
    
    /*
     * Converts model to a suitable format for ElasticSearch
     */
    public function getEsSaveFormat()
    {
        $obj = clone $this;

        //Go through ES Accessors
        \ElasticSearchHelper::esAccessor($obj);

        $dates = $this->getDates();
        //Convert to array, then change Date to appropriate Elasticsearch format.
        //Why? Because eloquent's date accessors is playing me.
        $dataArray = $obj->attributesToArray();

        //Remove all Excludes
        foreach($this->esGetRemove() as $ex)
        {
            if(array_key_exists($ex,$dataArray))
            {
                unset($dataArray[$ex]);
            }
        }
        
        if(!empty($dates))
        {
            foreach($dates as $d)
            {
                if(isset($dataArray[$d]) && $dataArray[$d] !== "" )
                {
                    //Trigger Eloquent Getter which will provide a Carbon instance
                    $dataArray[$d] = $this->{$d}->toIso8601String();
                }
            }
        }
        
        return $dataArray;
    }
}