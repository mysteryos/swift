<?php

class SearchController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Search";
        $this->rootURL = "search";
        $this->searchPermissions = array(
                                        'order-tracking'=>array('ot-view','ot-admin'),
                                        'aprequest'=>array('apr-view','apr-admin'),
                                        'acpayable'=>array('acp-admin','acp-edit'),
                                        'supplier'=>array('acp-view')
                                   );
        $this->searchCategory = array(
                                    'order-tracking' => 'Order Process',
                                    'aprequest' => 'A&P Request',
                                    'acpayable' => 'Accounts Payable',
                                    'supplier' => 'JDE Supplier'
                                );
    }
    
    /*
     * All Search
     */
    
    private function processSearchResult($queryResponse)
    {
        $result = array();
        if($queryResponse['hits']['total'] > 0 && $queryResponse['timed_out'] === false)
        {
            foreach($queryResponse['hits']['hits'] as $line)
            {
                $highlight = "";
                if(isset($line['highlight']))
                {
                    foreach($line['highlight'] as $k=>$v)
                    {
                        foreach($v as $val)
                        {
                            $highlight[] =  str_replace("_"," ",implode(" - ",explode(".",$k)))." : ".$val;
                        }
                    }
                    $highlight = implode(" · ",$highlight);
                }

                switch($line['_type'])
                {
                    case "supplier":
                        $result[] = array('icon'=>'fa-truck',
                                          'title'=> 'JDE Supplier',
                                          'id' => $line['_id'],
                                          'value'=>$line['_source'][$line['_type']]['name']." (Code: ".$line['_id'].")",
                                          'url'=>Helper::generateUrl(JdeSupplierMaster::whereSupplierCode($line['_id'])->get()),
                                          'highlight'=>$highlight);
                        break;
                    default:
                        //order-tracking, acpayable, aprequest
                        $contextClass = \Config::get('context.'.$line['_type']);
                        $result[] = array('icon'=>(new $contextClass)->getIcon(),
                                          'title'=> (new $contextClass)->readableName,
                                          'id' => $line['_id'],
                                          'value'=>$line['_source'][$line['_type']]['name'],
                                          'url'=>Helper::generateUrl($contextClass::find($line['_id'])),
                                          'highlight'=>$highlight);
                        break;
                }

            }
        }
        
        return $result;
    }
    
    public function getAll($search)
    {
        $params = array();
        $params['index'] = App::environment();
        $params['type'] = array();
        
        /*
         * Check Permissions
         */
        foreach($this->searchPermissions as $k=>$sp)
        {
            if($this->currentUser->hasAnyAccess($sp))
            {
                $params['type'][]=$k;
            }
        }
        if(count($params['type']) === 0 || trim($search) === "")
        {
            echo "";
            return;
        }
        
        try
        {
            $params['body']['query']['bool']['should'][0]['match']['name']['query'] = $search;
            $params['body']['query']['bool']['should'][0]['match']['name']['operator'] = "and";
            $params['body']['query']['bool']['should'][0]['match']['name']['boost'] = 2;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['like_text'] = $search;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['fuzziness'] = 0.5;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['prefix_length'] = 2;
            $params['body']['query']['bool']['minimum_should_match'] = 1;

            $params['body']['highlight']['fields']['*'] = new \stdClass();
            $params['body']['highlight']['pre_tags'] = array('<b>');
            $params['body']['highlight']['post_tags'] = array('</b>');
            $params['body']['_source']['exclude'] = array( "*.created_at","*.updated_at","*.deleted_at");
            $params['body']['from'] = 0;
            $params['body']['size'] = 5;
            $queryResponse = Es::search($params);
            
        } catch (\Exception $e)
        {
            \Log::error($e->getMessage());
            return Response::make("An error occured with the search server.",500);
        }
        
        echo json_encode($this->processSearchResult($queryResponse));
    }
    
    public function getAllPrefetch()
    {
        try
        {        
            $params = array();
            $params['index'] = App::environment();
            $params['body']['from'] = 0;
            $params['body']['size'] = 25;
            $queryResponse = Es::search($params);
        } catch (Exception $e)
        {
            \Log::error($e->getMessage());
            return Response::make("An error occured with the search server.",500);
        }        

        echo $this->processSearchResult($queryResponse);
    }
    
    /*
     * Order Tracking Search
     */
    public function getOrderTracking($search)
    {
        
    }
    
    /*
     * Order Tracking Suggest
     */
    public function getOrderTrackingPrefetch()
    {
        
    }
    
    /*
     * Display Search Page
     */
    
    public function getIndex()
    {
        $perpage = 15;
        $page_number = (int)Input::get('page',1);
        $search = Input::get('search',"");
        $this->data['selected_category'] = Input::get('category','everything');
        $this->data['category'] = $this->searchCategory;
        $this->pageTitle = "Search results for '".$search."'";
        
        
        $params = array();
        $params['index'] = App::environment();
        $params['type'] = array();
        
        /*
         * Check Permissions
         */
        foreach($this->searchPermissions as $k=>$sp)
        {
            if($this->currentUser->hasAnyAccess($sp))
            {
                $params['type'][]=$k;
            }
            else
            {
                if(isset($this->searchCategory[$k]))
                {
                    unset($this->searchCategory[$k]);
                }
            }
        }
        
        if(count($params['type']) === 0)
        {
            $this->data['hits_count'] = 0;         
            $this->data['result'] = array();
            $this->data['query'] = $search;
            $this->data['time_taken'] = 0;
            return $this->makeView('search');
        }
        
        if($this->data['selected_category'] !== "everything" && array_key_exists($this->data['selected_category'],$this->searchCategory))
        {
            unset($params['type']);
            $params['type'] = $this->data['selected_category'];
        }
        else
        {
            $this->data['selected_category'] = "everything";
        }
        
        try
        {
            $this->data['selected_category_text'] = $this->data['selected_category'] == "everything" ? "Everything" : ucfirst($this->searchCategory[$this->data['selected_category']]) ;

            $params['body']['query']['bool']['should'][0]['match']['name']['query'] = $search;
            $params['body']['query']['bool']['should'][0]['match']['name']['operator'] = "and";
            $params['body']['query']['bool']['should'][0]['match']['name']['boost'] = 2;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['like_text'] = $search;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['fuzziness'] = 0.5;
            $params['body']['query']['bool']['should'][1]['fuzzy_like_this']['prefix_length'] = 2;
            $params['body']['query']['bool']['minimum_should_match'] = 1;

            $params['body']['highlight']['fields']['*'] = new \stdClass();
            $params['body']['highlight']['pre_tags'] = array('<b>');
            $params['body']['highlight']['post_tags'] = array('</b>');
            $params['body']['_source']['exclude'] = array( "*.created_at","*.updated_at","*.deleted_at");
            $params['body']['from'] = $page_number == 1 ? 0 : (($page_number-1)*$perpage)+1;
            $params['body']['size'] = 15;
            $queryResponse = Es::search($params);
        } catch (Exception $e) {
            \Log::error($e->getMessage());
            return Response::make("An error occured with the search server.",500);
        }
        
        $this->data['hits_count'] = $queryResponse['hits']['total'];         
        $this->data['result'] = Paginator::make($this->processSearchResult($queryResponse),$this->data['hits_count'],$perpage);
        $this->data['query'] = $search;
        $this->data['time_taken'] = $queryResponse['took']/1000;

        return $this->makeView('search');
        
    }
        
}