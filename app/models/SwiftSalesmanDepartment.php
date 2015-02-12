<?php

class SwiftSalesmanDepartment extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;    
    
    protected $table = "swift_salesman_department";
    protected $fillable = ['name','notes'];
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'name', 'notes'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'notes' => 'Notes'
    );
    
    public $revisionClassName = "Salesman Department";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    //Used to retrieve last user who worked on this main model
    public $revisionRelations = ['salesman'];
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }
    
    /*
     * Relationships
     */
    
    public function salesman()
    {
        return $this->hasMany('SwiftSalesman','department_id');
    }
    
    public static function getList($trashed=false)
    {
        $query = self::query();
        if($trashed)
        {
            $query->withTrashed();
        }
        
        $list = $query->orderBy('name','ASC')->get();
        
        $listArray = array();
        
        foreach($list as $row)
        {
            $listArray[$row->id] = $row->name;
        }
        
        return $listArray;
    }
    
}
