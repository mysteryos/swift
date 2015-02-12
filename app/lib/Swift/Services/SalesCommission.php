<?php

Namespace Swift\Services;

/**
 * Description of SalesCommission
 *
 * @author kpudaruth
 */
class SalesCommission {

    public function calculateAll()
    {
        
    }
    
    public function calculatePerSalesman($salesman_id,$date_start,$date_end)
    {
        $clientList = \SwiftSalesmanClient::getBySalesmanId($salesman_id);
        if(count($clientList))
        {
            $clientCustomerCode = array_map(function($v){
                                    return $v['customer_code'];
                                },$clientList->toArray());
            $sales = JdeSales::where('IVD','>=',$date_start)
                    ->where('IVD','<=',$date_end,'AND')
                    ->whereIn('customer_code',$clientCustomerCode)
                    ->sum('UPRC');
            
            return $sales;
        }
        return false;
    }
    
    public function saveCalculationPerSalesman($salesman_id,$budget_id)
    {
        $clientList = \SwiftSalesmanClient::getBySalesmanId($salesman_id);
        if(count($clientList))
        {
            $clientCustomerCode = array_map(function($v){
                                    return $v['customer_code'];
                                },$clientList->toArray());
            $sales = JdeSales::where('IVD','>=',$date_start)
                    ->where('IVD','<=',$date_end,'AND')
                    ->whereIn('customer_code',$clientCustomerCode)
                    ->sum('UPRC');
            
            return $sales;
        }
        return false;        
    }
}
