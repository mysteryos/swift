<?php
/*
 * Name: CHCLStorage
 * Description: Keeps list of storage obtained from CHCL website, at link: http://www.chcl.mu/info/?id=30
 */

class CHCLStorage extends Eloquent {
    
    protected $table = "chcl_storage";
    
    protected $fillable = array("vessel","code","voy","date_start","storage","discharge","storage_rate");
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    public $dates = ['date_start','storage','discharge'];
    
    
    public static function getByVesselAndVoyage($vessel,$voy)
    {
        return self::where('vessel','=',$vessel)->where('voy','=',$voy,'AND')->count();
    }

    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
}

