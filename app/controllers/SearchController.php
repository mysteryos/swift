<?php

class SearchController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Search";
        $this->rootURL = "search";
    }
    
    /*
     * All Search
     */
    
    private function processSearchResult($queryResponse) {
        $result = array();
        if($queryResponse['hits']['total'] > 0 && $queryResponse['timed_out'] === false)
        {
            foreach($queryResponse['hits']['hits'] as $line)
            {
                switch($line['_type'])
                {
                    case 'order-tracking':
                        $result[] = array('icon'=>'fa-map-marker','title'=>'Order Process','value'=>$line['_source']['name'],'url'=>Helper::generateUrl(SwiftOrder::find($line['_source']['id'])));
                        break;
                    case 'aprequest':
                        $result[] = array('icon'=>'fa-gift','title'=>'A&P Request','value'=>$line['_source']['name'], 'url'=>Helper::generateUrl(SwiftAPRequest::find($line['_source']['id'])));
                        break;
                    case 'acpayable':
                        break;
                }

            }
        }
        
        return json_encode($result);
    }
    
    public function getAll($search)
    {
        $params = array();
        $params['index'] = App::environment();
        $params['body']['query']['match']['name'] = array(
            'query' => $search,
            'fuzziness' => 0.7,
        );
        $params['body']['from'] = 0;
        $params['body']['size'] = 20;
        $queryResponse = Es::search($params);
        
        echo $this->processSearchResult($queryResponse);
    }
    
    public function getAllPrefetch()
    {
        $params = array();
        $params['index'] = App::environment();
        $params['body']['from'] = 0;
        $params['body']['size'] = 25;
        $queryResponse = Es::search($params);

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
    
    public function getResult($search)
    {
        
    }
        
}