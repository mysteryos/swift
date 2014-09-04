<?php
/*
 * Name:
 * Description:
 */

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class SwiftDocument extends Eloquent implements StaplerableInterface{
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    use EloquentTrait;
    
    use \Venturecraft\Revisionable\RevisionableTrait;    
    
    protected $table = "swift_document";
    
    protected $guarded = array('id');
    
    protected $fillable = ["document_file_name","document_file_size","document_content_type","document_updated_at","user_id"];
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'document_file_name',
    );
    
    protected $revisionFormattedFieldNames = array(
        'document_file_name' => 'Document Name',
    );    
    
    protected $revisionClassName = "Document";
    
    protected $saveCreateRevision = true;
    
    public function __construct(array $attributes = array())
    {
        // Define an attachment named 'document' that stores files locally.
        $this->hasAttachedFile('document', [
            'storage' => 's3',
            'url' => '/upload/:attachment/:id/:filename',
            'default_url' => '/defaults/:style/missing.png',
            'keep_old_files' => true,
            'preserve_old_files' => true
        ]);
        
        parent::__construct($attributes);
    }
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        /*
         * Set User Id on create
         */
        static::creating(function($model){
            $model->user_id = Sentry::getUser()->id;
        });

        static::bootStapler();
        
    }
    
    /*
     * relationships
     */
    public function order(){
        return $this->belongsTo('SwiftOrder');
    }
    
    /*
     * Morph
     */
    
    public function document()
    {
        return $this->morphTo();
    }
    
    public function tag()
    {
        return $this->morphMany('SwiftTag','taggable');
    }
 
}