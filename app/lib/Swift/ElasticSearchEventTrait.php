<?php

Namespace Swift;

trait ElasticSearchEventTrait {
    public $esExcludesDefault = array('created_at','updated_at','deleted_at');
    
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
            \Queue::push('ElasticSearchHelper@indexTask',array('id'=>$this->esId(),'context'=>$this->esContext(),'info-context'=>$this->esInfoContext(),'excludes'=>$this->esExcludes()));
        }
        else
        {
            $this->esUpdate();
        }
    }
    
    private function esUpdate()
    {
        //esContext is false for polymorphic relations with no elasticsearch indexing
        if(isset($this->esMain) && $this->esMain === true && $this->esContext !== false)
        {
            \Queue::push('ElasticSearchHelper@updateTask',array('id'=>$this->esId(),'context'=>$this->esContext(),'info-context'=>$this->esInfoContext(),'excludes'=>$this->esExcludes()));
        }
    }
    
    private function esId()
    {
        try
        {
            return $this->esGetId();
        } catch (Exception $ex) {
            throw new \RuntimeException("esGetId() is not set in class '".get_class($this)."'");
        }
    }
    
    private function esInfoContext()
    {
        if(method_exists($this,'esGetInfoContext'))
        {
            return $this->esGetInfoContext();
        }
        elseif(isset($this->esInfoContext))
        {
            return $this->esInfoContext;
        }
        else
        {
            throw new \RuntimeException("esInfoContext attribute or esGetInfoContext() is not set in class '".get_class($this)."'");
        }
    }
    
    private function esContext()
    {
        if(method_exists($this,'esGetContext'))
        {
            return $this->esGetContext();
        }
        elseif(isset($this->esContext))
        {
            return $this->esContext;
        }
        else
        {
            throw new \RuntimeException("esContext attribute or esGetContext() method must be set in class '".get_class($this)."'");
        }
    }
    
    private function esExcludes()
    {
        if(isset($this->esExcludes))
        {
            return $this->exExcludes;
        }
        else
        {
            return $this->esExcludesDefault;
        }
    }
}