<?php
/**
 * Description: Table that stores information extracted from http://www.chcl.mu/info/?id=49
 *
 * @author kpudaruth
 */

class CHCLLive extends Eloquent
{
    protected $table = "chcl_live";
    
    protected $fillable = ['voyage','date_start'];
    
    protected $dates = ['date_start'];

    public $timestamps = false;
    
    /*
     * Accessors
     */
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */
    
    /*
     * Query
     */

    public static function countWhereVoyageDateStart($voyage,$date_start)
    {
        return self::where('voyage','=',$voyage)
                    ->where('date_start','=',$date_start,'AND')
                    ->count();
    }

    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
    
}
