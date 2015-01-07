<?php

Namespace Swift;

trait ElasticSearchEventTrait {
    public $esExcludes = array('created_at','updated_at','deleted_at');
    
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
        if(isset($this->esMain) && $this->esMain === true)
        {
            \Queue::push('ElasticSearchHelper@indexTask',array('id'=>$this->esId(),'context'=>$this->esContext,'info-context'=>$this->esInfoContext(),'excludes'=>$this->esExcludes));
        }
        else
        {
            $this->esUpdate();
        }
    }
    
    private function esUpdate()
    {
        \Queue::push('ElasticSearchHelper@updateTask',array('id'=>$this->esId(),'context'=>$this->esContext,'info-context'=>$this->esInfoContext(),'excludes'=>$this->esExcludes));
    }
    
    private function esId()
    {
        try
        {
            return $this->esGetId();
        } catch (Exception $ex) {
            throw new \RuntimeException("esGetId() is not set");
        }
    }
    
    private function esInfoContext()
    {
        try
        {
            return $this->esGetInfoContext();
        } catch (Exception $ex) {
            throw new \RuntimeException("esGetId() is not set");
        }
    }    
}