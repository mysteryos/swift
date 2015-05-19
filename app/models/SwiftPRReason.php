<?php
/**
 * Description of SwiftPRReason
 *
 * @author kpudaruth
 */

class SwiftPRReason extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_pr_reason";
    
    protected $fillable = ['name','category'];

    protected $appends = ['text'];

    public $timestamps = false;
    
    /*
     * Accessors
     */

    public function getTextAttribute()
    {
        return $this->name.' (At '.$this->category.')';
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */
    
    /*
     * Query
     */

    public static function getAll()
    {
        $all = self::remember(60)
                ->orderBy('Category','ASC')
                ->orderBy('Name','ASC')
                ->get();
        foreach($all as $row)
        {
            $result[$row->id] = $row->category." - ".$row->name;
        }
        return $result;
    }

    public static function getInvoiceCancelledScottId()
    {
        return self::remember(240)
                ->where('name','=','Invoice Cancelled')
                ->where('category','=','Scott','AND')
                ->first()->id;
    }
}
