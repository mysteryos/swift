<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Permission;

/**
 * Description of PermissionTrait
 *
 * @author kpudaruth
 */
trait PermissionTrait
{
    public function permission($user_id=false)
    {
        $class = "\Permission\\".get_class($this);
        return new $class($this,$user_id);
    }
}