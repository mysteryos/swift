<?php
/**
 * Description of SwiftSalesmanDepartmentPermission
 *
 * @author kpudaruth
 */
class SwiftSalesmanDepartmentPermission extends Eloquent {

    protected $table = "swift_salesman_department_permission";
    
    public function department()
    {
        return $this->belongsTo('SwiftSalesmanDepartment','department_id');
    }

}
