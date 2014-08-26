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
}
