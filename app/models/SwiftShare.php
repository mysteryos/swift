<?php
/**
 * Description: Accounts Payable - Credit Note
 * TODO: Change to SwiftCreditNote
 * @author kpudaruth
 */

class SwiftShare extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = "swift_share";

    protected $dates = ['deleted_at'];

    protected $fillable = ['permission','from_user_id','to_user_id','msg'];

    protected $appends = ['permission_name'];

    protected $with = ['from_user','to_user'];

    const PERMISSION_VIEW = 1;
    const PERMISSION_EDIT = 2;

    public static $permissions = [
        self::PERMISSION_VIEW => 'Can View',
        self::PERMISSION_EDIT => 'Can Edit'
    ];


    public function getPermissionNameAttribute()
    {
        if(key_exists($this->permission,self::$permissions) && $this->permission !== null)
        {
            return self::$permissions[$this->permission];
        }

        return "N/A";
    }

    public function getToUserFullNameAttribute()
    {
        
    }

    public function shareable()
    {
        return $this->morphTo();
    }

    public function from_user()
    {
        return $this->belongsTo('User','from_user_id');
    }

    public function to_user()
    {
        return $this->belongsTo('User','to_user_id');
    }

    public static function findUserByForm($className,$form_id,$user_id)
    {
        return self::where('shareable_type','=',$className)
                ->where('shareable_id','=',$form_id)
                ->where('to_user_id','=',$user_id)
                ->first();
    }
}