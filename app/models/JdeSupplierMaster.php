<?php
/*
 * Name: Jde Supplier Master on SCT_JDE
 * Description: Eloquent Model
 */

class JdeSupplierMaster extends Eloquent {
    protected $connection = 'sct_jde';

    protected $primaryKey = 'Supplier_Code';
    
    protected $table = 'sct_jde.jdesuppliermaster';

    public $readableName = "JDE Supplier";

    /*
     * Utility
     */

    public function getIcon()
    {
        return "fa-truck";
    }

    public function getReadableName()
    {
        return $this->Supplier_Name." (Code: ".$this->Supplier_Code.")";
    }

    /*
     * Pusher Channel Name
     */

    public function channelName()
    {
        return "jdesupplier_".trim($this->Supplier_Code);
    }

    /*
     * Relationships
     */

    public function credit()
    {
        return $this->hasMany('SwiftSupplierCredit','supplier_code','Supplier_Code');
    }

    public function invoice()
    {
        return $this->hasManyThrough('SwiftACPInvoice','SwiftACPRequest','Supplier_Code','acp_id');
    }

    //Accounts Payable
    public function acp()
    {
        return $this->hasMany('SwiftACPRequest','supplier_code','Supplier_Code');
    }

    public function document()
    {
        return $this->morphMany('SwiftDocument','document');
    }

    /*
     * Query
     */
    public static function getByName($term,$offset,$limit)
    {
        return self::where('Supplier_Name','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('Supplier_Code','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
    public static function countByName($term)
    {
        return self::where('Supplier_Name','LIKE',"%$term%")->count();
    }
    
    public static function countByCode($term)
    {
        return self::where('Supplier_Code','LIKE',"%$term%")->count();
    }

}
