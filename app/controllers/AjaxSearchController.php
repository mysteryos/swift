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
        $limit = 5;
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
    
    public function getCustomercode()
    {
        $limit = 10;
        $offset = (Input::get('page') == "1" ? "0" : (Input::get('page')-1)*$limit);

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
            $searchresult = JdeProduct::getByCode(Input::get('term'),$offset,$limit);
            $total = JdeProduct::countByCode(Input::get('term'));
        }
        else
        {
            $searchresult = JdeProduct::getByName(Input::get('term'),$offset,$limit);
            $total = JdeProduct::countByName(Input::get('term'));
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
        $users = User::where('id','!=',Sentry::getUser()->id)->get();
        $userArray = array();
        foreach($users as $u)
        {
            $userArray[] = array('username'=>$u->first_name.".".$u->last_name,'name'=>$u->first_name." ".$u->last_name,'id'=>$u->id,'email'=>$u->email);
        }
        
        return Response::json($userArray);
    }
}
