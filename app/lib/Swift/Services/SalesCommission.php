<?php

Namespace Swift\Services;

/**
 * Description of SalesCommission
 *
 * @author kpudaruth
 */
class SalesCommission {

    public function calculateAll($date_start,$date_end)
    {
        $salesmen = \SwiftSalesman::all();
        foreach($salesmen as $s)
        {
            $this->calculatePerSalesman($s,$date_start,$date_end,true);
        }
    }
    
    public function calculatePerSalesman($salesman,$date_start,$date_end,$save=false)
    {
        try {
            if(is_numeric($salesman))
            {
                $salesman = \SwiftSalesman::getById($salesman);
            }
            else
            {
                if(!$salesman instanceof \SwiftSalesman)
                {
                    throw new \Exception('Salesman parameter is not recognized. Parameter data: '.var_dump($salesman));
                }
            }
            if($salesman)
            {
                //Get active scheme
                if(count($salesman->scheme))
                {
                    $activeScheme = false;
                    foreach($salesman->scheme as $scheme)
                    {
                        if($scheme->isActiveBetween($date_start,$date_end))
                        {
                            //Active scheme, lets dig in
                            switch($scheme->type)
                            {
                                case \SwiftSalesCommissionScheme::KEYACCOUNT_FLAT_SALES_PRODUCTCATEGORY:
                                    $this->keyAccountFlatSalesProductCategory($salesman,$scheme,$date_start,$date_end,$save);
                                    break;
                                case \SwiftSalesCommissionScheme::KEYACCOUNT_DYNAMIC_PRODUCTCATEGORY:
                                    $this->keyAccountDynamicProductCategory($salesman,$scheme,$date_start,$date_end,$save);
                                    break;
                            }
                        }
                    }
                }
                else
                {
                    throw new \exception("No scheme for salesman with ID: ".$salesman_id);
                }
            }
            else
            {
                throw new \exception("Unable to find salesman with ID: ".$salesman_id);
            }            
        }
        catch (Exception $ex) 
        {
            \Log::error($ex->getMessage());
        }
    }
    
    private function saveCalculationPerSalesman($data)
    {
        $commissionCalculation = new \SwiftSalesCommissionCalc([
            'salesman_id'   =>  $data['salesman']->id,
            'salesman_info' =>  $data['salesman']->toJson(),
            'budget_id'     =>  isset($data['budget']) ? $data['budget']->id : 0,
            'scheme_id'     =>  isset($data['scheme']) ? $data['scheme']->id : 0,
            'rate_id'       =>  isset($data['rate']) ? $data['rate']->id : 0,
            'total'         =>  round($data['sales_value'],4),
            'value'         =>  round($data['commission_value'],4),
            'date_start'    =>  $data['date_start'],
            'date_end'      =>  $data['date_end'],
            'budget_info'   =>  isset($data['budget']) ? $data['budget']->toJson() : "",
            'scheme_info'   =>  isset($data['scheme']) ? $data['scheme']->toJson() : "",
            'rate_info'     =>  isset($data['rate']) ? $data['rate']->toJson() : "",
        ]);
        
        $commissionCalculation->save();
        
        if(isset($data['product_list']))
        {
            foreach($data['product_list'] as $p)
            {
                $product = new \SwiftSalesCommissionCalcProduct([
                    'jde_itm'   => $p->ITM,
                    'jde_doc'   => $p->DOC,
                    'jde_an8'   => $p->AN8,
                    'jde_qty'   => $p->UORG,
                    'total'     =>  $p->AEXP,
                ]);

                $commissionCalculation->product()->save($product);
            }
        }

        return true;
    }
    
    private function keyAccountFlatSalesProductCategory($salesman,$scheme,$date_start,$date_end,$save)
    {
        //Get Budget
        $budget = \SwiftSalesCommissionBudget::getActiveBudgetBySalesman($salesman->id,$scheme->id,$date_start->toDateString(),$date_end->toDateString());
        if($budget)
        {
            $budgetVal = $budget->value;
            $customerCodes = array_map(function($v){
                                return $v['customer_code'];
                            },$salesman->client->toArray());
            $productCategory = array_map(function($v){
                                return $v['category'];
                            },$scheme->productCategory->toArray());
                            
            //Fetch sales for current period
            $salesSum = \JdeSales::where('IVD','>=',$date_start)
                ->where('IVD','<=',$date_end,'AND')
                ->whereIn('AN8',$customerCodes)
                ->whereIn('SRP3',$productCategory)
                ->sum('AEXP');
            /*
             * If salesman achieves 80% or more of his budget of the month, commission is granted.
             */
            if($salesSum/$budgetVal >= 0.8)
            {
                $commissionRate = 0;
                if($salesSum/$budgetVal >= 1.25)
                {
                    $commissionRate = 1.25;
                }
                else
                {
                    $commissionRate = $salesSum/$budgetVal;
                }

                $commissionValue = $commissionRate * 0.002 * $salesSum;
            }
            else
            {
                // Else no commission for u
                $commissionValue = 0;
            }
                
            //Save the commissionValue
            if($save)
            {
                $product_list =\JdeSales::where('IVD','>=',$date_start)
                            ->where('IVD','<=',$date_end,'AND')
                            ->whereIn('AN8',$customerCodes)
                            ->whereIn('SRP3',$productCategory)
                            ->get();
                $this->saveCalculationPerSalesman([
                    'salesman' => $salesman,
                    'budget' => $budget,
                    'scheme' => $scheme,
                    'commission_value'=>$commissionValue,
                    'sales_value'=>$salesSum,
                    'product_list'=>$product_list,
                    'date_start'=>$date_start,
                    'date_end' =>$date_end,
                ]);
            }
        }
    }
    
    private function keyAccountDynamicProductCategory($salesman,$scheme,$date_start,$date_end,$save)
    {
        //Active Rate
        if(count($scheme->rate))
        {
            $activeRate = false;
            foreach($scheme->rate as $r)
            {
                if($r->isActiveBetween($date_start,$date_end))
                {
                    $activeRate = $r;
                    break;
                }
            }
            
            if($activeRate !== false)
            {
                $rateValue = $activeRate->rate;
                
                //List of Customers
                $customerCodes = array_map(function($v){
                                    return $v['customer_code'];
                                },$salesman->client->toArray());
                //List of product codes
                $productCodes = array_map(function($k){
                                    return $k['jde_itm'];
                                },$scheme->product->toArray());
                
                //Sales value, filtered by product and customer
                $salesSum = \JdeSales::where('IVD','>=',$date_start)
                    ->where('IVD','<=',$date_end,'AND')
                    ->whereIn('AN8',$customerCodes)
                    ->whereIn('ITM',$productCodes)
                    ->sum('AEXP');
                
                $commissionValue = ($rateValue/100) * $salesSum;
                
                if($save)
                {
                    $product_list = \JdeSales::where('IVD','>=',$date_start)
                                    ->where('IVD','<=',$date_end,'AND')
                                    ->whereIn('AN8',$customerCodes)
                                    ->whereIn('ITM',$productCodes)
                                    ->get();
                    
                    $this->saveCalculationPerSalesman([
                        'salesman' => $salesman,
                        'scheme' => $scheme,
                        'rate'   => $activeRate,
                        'commission_value'=>$commissionValue,
                        'product_list'=>$product_list,
                        'date_start'=>$date_start,
                        'date_end' =>$date_end,
                    ]);                    
                }
            }
        }
    }
}
