<?php
namespace Swift\Share;

use SwiftShare;

/**
 * Trait used in conjucture with Eloquent
 *
 * @author kpudaruth
 */
trait SharingTrait
{
    public function share()
    {
        return $this->morphMany('SwiftShare','shareable');
    }

    /*
     * Check if form is shared with specified user
     * @var int $user_id
     * @var boolean|int $permission_type
     *
     * @return boolean
     */

    public function isSharedWith($user_id,$permission_type=false)
    {
        $query = $this->query();
        if($permission_type=== false)
        {
            return (boolean)$query->share()->where('to_user_id','=',$user_id)
                            ->count();
        }
        else
        {
            if(array_key_exists($permission_type,SwiftShare::$permissions))
            {
                return (boolean)$query->share()->where('to_user_id','=',$user_id)
                    ->where('permission','=',$permission_type,'AND')
                    ->count();
            }
            else
            {
                throw new \RuntimeException("Permission type doesn't exist");
            }
        }
    }
}
