<?php
/*
 * Name: ajax Search
 * Description: All Ajax Search Functions go here
 */

Class AjaxSearchController extends UserController {
    /*
     * URL: /order-tracking/create
     * Description: Fetch list of suppliers from SCT_JDE -> jdesuppliermaster
     */
    public function getSearchsupplier()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

        if(is_numeric(Input::get('term')))
        {
            $searchresult = JdeSupplierMaster::getByCode(Input::get('term'),$offset,$limit);
            $total = JdeSupplierMaster::countByCode(Input::get('term'));
        }
        else
        {
            $searchresult = JdeSupplierMaster::getByName(Input::get('term'),$offset,$limit);
            $total = JdeSupplierMaster::countByName(Input::get('term'));
        }
        if($searchresult !== false)
        {
            echo json_encode(array('suppliers'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }        
    }
    
    /*
     * URL: /order-tracking/create
     * Description: Fetch list of Freight Companies from swift_freight_company
     */
    public function getFreightcompany()
    {
        $limit = 5;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

        $searchresult = SwiftFreightCompany::getByName(Input::get('query'),$offset,$limit);
        
        if(count($searchresult))
        {
            foreach($searchresult as $s)
            {
                $result[] = array('id'=>$s->id,'text'=>$s->name);
            }
            echo json_encode($result);
        }
        else
        {
            echo "";
        }       
    }
    
    /*
     * URL: /nespresso-crm/createmachine
     * Description: Fetches list of customers from SCT_JDE
     */
    
    public function getCustomercode($context="")
    {
        $limit = 10;
        $offset = (\Input::get('page') == "1" ? "0" : (\Input::get('page')-1)*$limit);

        if(\Input::get('term') === "")
        {
            switch($context)
            {
                case "aprequest":
                    $class = \Config::get("context.$context");
                    
                    //Get most popular customers by user
                    $customers = $class::groupBy('customer_code')
                                ->select(DB::raw("COUNT(*) as  count"),"customer_code")
                                ->where('requester_user_id','=',$this->currentUser->id)
                                ->orderBy('count')
                                ->lists("customer_code");
                    
                    if(count($customers))
                    {
                        $searchresult = \JdeCustomer::getIn($customers,$offset,$limit);
                        $total = count($customers);
                    }
                    else
                    {
                        //Get most popular customers on system
                        $customers = $class::groupBy('customer_code')
                                    ->select(DB::raw("COUNT(*) as  count"),"customer_code")
                                    ->orderBy('count')
                                    ->lists("customer_code");

                        $searchresult = \JdeCustomer::getIn($customers,$offset,$limit);
                        $total = count($customers);
                    }
                    break;
                case "product-returns":
                    $class = \Config::get("context.$context");

                    //Get most popular customers by user
                    $customers = $class::groupBy('customer_code')
                                ->select(DB::raw("COUNT(*) as  count"),"customer_code")
                                ->where('owner_user_id','=',$this->currentUser->id)
                                ->orderBy('count')
                                ->lists("customer_code");

                    if(count($customers))
                    {
                        $searchresult = \JdeCustomer::getIn($customers,$offset,$limit);
                        $total = count($customers);
                    }
                    else
                    {
                        //Get most popular customers on system
                        $customers = $class::groupBy('customer_code')
                                    ->select(DB::raw("COUNT(*) as  count"),"customer_code")
                                    ->orderBy('count')
                                    ->lists("customer_code");

                        $searchresult = \JdeCustomer::getIn($customers,$offset,$limit);
                        $total = count($customers);
                    }
                    break;
            }
        }
        else
        {
            if(is_numeric(\Input::get('term')))
            {
                $searchresult = \JdeCustomer::getByCode(Input::get('term'),$offset,$limit);
                $total = \JdeCustomer::countByCode(Input::get('term'));
            }
        }

        if(!isset($searchresult))
        {
            $searchresult = \JdeCustomer::getByName(Input::get('term'),$offset,$limit);
            $total = \JdeCustomer::countByName(Input::get('term'));
        }

        if($searchresult !== false)
        {
            echo json_encode(array('customers'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }          
    }

    /*
     * URL: /accounts-payable/create
     * Description: Fetches list of customers from SCT_JDE
     */

    public function getAcpCustomercode()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

        if(Input::get('term')==="")
        {
            //Get Most Popular customers
            $searchresult = \SwiftACPRequest::groupBy('billable_company_code')
                            ->select(\DB::Raw('COUNT(*) as count, jdecustomers.ALPH, jdecustomers.AN8, jdecustomers.AC09'))
                            ->limit($limit)
                            ->offset($offset)
                            ->orderBy('count','DESC')
                            ->join('sct_jde.jdecustomers','swift_acp_request.billable_company_code','=','jdecustomers.an8')
                            ->remember(5)->get();

            $total = \SwiftACPRequest::distinct('billable_company_code')
                    ->join('sct_jde.jdecustomers','swift_acp_request.billable_company_code','=','jdecustomers.an8')
                    ->remember(5)->count('billable_company_code');
        }
        else
        {
            if(is_numeric(Input::get('term')))
            {
                $searchresult = JdeCustomer::getByCode(Input::get('term'),$offset,$limit);
                $total = JdeCustomer::countByCode(Input::get('term'));
            }
            else
            {
                $searchresult = JdeCustomer::getByName(Input::get('term'),$offset,$limit);
                $total = JdeCustomer::countByName(Input::get('term'));
            }
        }
        
        if($searchresult !== false)
        {
            echo json_encode(array('customers'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }
    }

    /*
     * URL: /accounts-payable/create
     * Description: Fetch list of suppliers from SCT_JDE -> jdesuppliermaster
     */
    public function getAcpSearchsupplier()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);


        if(Input::get('term')==="")
        {
            //Get Most Recent customers
            $searchresult = \SwiftACPRequest::groupBy('supplier_code')
                            ->select(array(\DB::Raw('MAX(created_at) as max_created_at'), 'jdesuppliermaster.*'))
                            ->limit($limit)
                            ->offset($offset)
                            ->orderBy('max_created_at','DESC')
                            ->join('sct_jde.jdesuppliermaster','swift_acp_request.supplier_code','=','jdesuppliermaster.Supplier_Code')
                            ->remember(5)->get();

            $total = \SwiftACPRequest::join('sct_jde.jdesuppliermaster','swift_acp_request.supplier_code','=','jdesuppliermaster.Supplier_Code')
                    ->select(array(\DB::raw('DISTINCT(swift_acp_request.supplier_code)')))
                    ->remember(5)->count('swift_acp_request.supplier_code');
        }
        else
        {
            if(is_numeric(Input::get('term')))
            {
                $searchresult = JdeSupplierMaster::getByExactCode(Input::get('term'),$offset,$limit);
                $total = JdeSupplierMaster::countByExactCode(Input::get('term'));
            }
            else
            {
                $searchresult = JdeSupplierMaster::getByNameOrVat(Input::get('term'),$offset,$limit);
                $total = JdeSupplierMaster::countByNameOrVat(Input::get('term'));
            }
        }
        
        if($searchresult !== false)
        {
            echo json_encode(array('suppliers'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }
    }
    
    /*
     * Name: Select2 X-editable function for getting JDE customers
     * Uses: aprequest/edit.blade.php
     */
    public function getCustomercodeplain()
    {
        $limit = 5;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);
        
        if(Input::has('query'))
        {
            if(is_numeric(Input::get('query')))
            {
                $searchresult = JdeCustomer::getByCode(Input::get('query'),$offset,$limit);
            }
            else
            {
                $searchresult = JdeCustomer::getByName(Input::get('query'),$offset,$limit);
            }        

            if(count($searchresult))
            {
                foreach($searchresult as $s)
                {
                    $result[] = array('id'=>$s->AN8,'text'=>$s->ALPH." - ".$s->AN8);
                }
                echo json_encode($result);
                return;
            }
        }
        
        echo "";
    }
    
    /*
     * Name: JDE products, search by code or name
     * Uses:  /aprequest/edit-product.blade.php
     */
    public function getProduct()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);
        
        if(is_numeric(Input::get('term')))
        {
            $searchresult = \JdeProduct::getByCode(Input::get('term'),$offset,$limit);
            $total = \JdeProduct::countByCode(Input::get('term'));
            
            if(!$total)
            {
                $searchresult = \JdeProduct::getByName(Input::get('term'),$offset,$limit);
                $total = \JdeProduct::countByName(Input::get('term'));
            }
        }
        else
        {
            $searchresult = \JdeProduct::getByName(Input::get('term'),$offset,$limit);
            $total = \JdeProduct::countByName(Input::get('term'));
        }        
        
        if(count($searchresult))
        {
            foreach($searchresult as $s)
            {
                $result[] = array('id'=>trim($s->ITM),'text'=>trim($s->DSC1)." - ".trim($s->AITM));
            }
            echo json_encode(['products'=>$result,'total'=>$total]);
        }
        else
        {
            echo json_encode(array('total'=>0));
        }        
    }
    
    /*
     * Name: JDE products, search by code or name
     */
    public function getProductplain()
    {
        $limit = 5;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);
        
        if(is_numeric(Input::get('query')))
        {
            $searchresult = JdeProduct::getByCode(Input::get('query'),$offset,$limit);
        }
        else
        {
            $searchresult = JdeProduct::getByName(Input::get('query'),$offset,$limit);
        }        
        
        if(count($searchresult))
        {
            foreach($searchresult as $s)
            {
                $result[] = array('id'=>trim($s->ITM),'text'=>trim($s->DSC1)." - ".trim($s->AITM));
            }
            echo json_encode($result);
        }
        else
        {
            echo "";
        }        
    }    
    
    public function getNespressomachine()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

        if(is_numeric(Input::get('term')))
        {
            $searchresult = JdeProduct::getNespressoMachineByCode(Input::get('term'),$offset,$limit);
            $total = JdeProduct::countNespressoMachineByCode(Input::get('term'));
        }
        else
        {
            $searchresult = JdeProduct::getNespressoMachineByName(Input::get('term'),$offset,$limit);
            $total = JdeProduct::countNespressoMachineByName(Input::get('term'));
        }
        if($searchresult !== false)
        {
            echo json_encode(array('machines'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }          
    }
    
    public function getUserbyname()
    {
        $term = Input::get('term');
        $users = User::where('first_name','LIKE','%'.$term.'%','OR')->where('last_name','LIKE','%'.$term.'%')->get();
        if(count($users))
        {
            $result = array();
            foreach($users as $u)
            {
                if($u->activated)
                {
                    $result[] = array('uid'=>$u->id,'value'=>$u->first_name." ".$u->last_name);
                }
            }
            return Response::json($result);
        }
        
        return Response::json(array());
        
    }
    
    public function getSalesmanbyname()
    {
        $limit = 5;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);        
        
        $term = Input::get('term');
        $users = SwiftSalesman::whereHas('user',function($q) use ($term){
                    return $q->where('first_name','LIKE','%'.$term.'%')
                    ->where('last_name','LIKE','%'.$term.'%','OR')
                    ->where('activated','=',1,'AND');
                })->with('user')->take($limit)->offset($offset)
                ->get();
        if(count($users))
        {
            $total = SwiftSalesman::whereHas('user',function($q) use ($term){
                        return $q->where('first_name','LIKE','%'.$term.'%')
                        ->where('last_name','LIKE','%'.$term.'%','OR')
                        ->where('activated','=',1,'AND');
                    })
                    ->count();
            
            $salesmanArray = array_map(function($v){
                                return array('id'=>$v['id'],'text'=>$v['user']['first_name']." ".$v['user']['last_name']);
                            },$users->toArray());
            
            $result = array('salesman'=>$salesmanArray,'total'=>$total);
            return Response::json($result);
        }
        
        return Response::json(array('total'=>0));
        
    }    
    
    public function getUserall()
    {
        $users = \User::where('id','!=',$this->currentUser->id)->get();
        $userArray = array();
        foreach($users as $u)
        {
            $userArray[] = array('username'=>$u->first_name.".".$u->last_name,'name'=>$u->first_name." ".$u->last_name,'id'=>$u->id,'email'=>$u->email);
        }
        
        return \Response::json($userArray);
    }

    public function getPrInvoiceCode()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

        $searchresult = JdeSales::getByInvoiceCode(Input::get('term'),$offset,$limit);
        $total = JdeSales::totalByInvoiceCode(Input::get('term'));

        if($searchresult !== false)
        {
            foreach($searchresult as &$s)
            {
                $ivd = Carbon::createFromFormat('Y-m-d H:i:s',$s->IVD);
                $s->IVD = $ivd->format('Y/m/d');
            }
            echo json_encode(array('invoices'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }
        
    }

    public function getPrInvoiceCodeExact()
    {
        $limit = 10;
        $offset = (\Input::get('page') == "1" ? "0" : (\Input::get('page')-1)*$limit);

        $searchresult = \JdeSales::getByInvoiceCodeExact(\Input::get('term'),$offset,$limit);
        $total = \JdeSales::totalByInvoiceCodeExact(\Input::get('term'));

        if($searchresult !== false)
        {
            foreach($searchresult as &$s)
            {
                $ivd = \Carbon::createFromFormat('Y-m-d H:i:s',$s->IVD);
                $s->IVD = $ivd->format('Y/m/d');
            }
            echo json_encode(array('invoices'=>$searchresult,'total'=>$total));
        }
        else
        {
            echo json_encode(array('total'=>0));
        }

    }
}
