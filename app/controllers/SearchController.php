<?php

class SearchController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Search";
        $this->rootURL = "search";
        $this->searchPermissions = array(
                                        'order-tracking'=>array('ot-view','ot-admin'),
                                        'aprequest'=>array('apr-view','apr-admin'),
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
                    case 'order-tracking':
                        $result[] = array('icon'=>'fa-map-marker',
                                          'title'=>'Order Process',
                                          'value'=>$line['_source'][$line['_type']]['name'],
                                          'url'=>Helper::generateUrl(SwiftOrder::find($line['_id'])),
                                          'highlight'=>$highlight);
                        break;
                    case 'aprequest':
                        $result[] = array('icon'=>'fa-gift',
                                          'title'=>'A&P Request',
                                          'value'=>$line['_source']['aprequest']['name'],
                                          'url'=>Helper::generateUrl(SwiftAPRequest::find($line['_id'])),
                                          'highlight'=>$highlight);
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
        if(count($params['type']) === 0)
        {
            echo "";
            return;
        }
        
        $params['body']['query']['bool']['should'][0]['match']['name']['query'] = $search;
        $params['body']['query']['bool']['should'][0]['match']['name']['fuzziness'] = 0.1;
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