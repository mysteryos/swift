<?php
/**
 * Description of ElasticSearchHelper: Helps with Indexing/Updating
 *
 * @author kpudaruth
 */

Namespace Swift\Services;

class ElasticSearchHelper {
    
    public function indexTask($job,$data)
    {
        /*
         * Router
         */
        if(isset($data['context']))
        {
            if(is_callable("ElasticSearchHelper::".studly_case("index-".$data['context'])))
            {
                call_user_func_array("ElasticSearchHelper::".studly_case("index-".$data['context']),array("data"=>$data));
            }
            else
            {
                \Log::error('ElasticSearchHelper: Unable to call function with name: ElasticSearchHelper::'.studly_case("index-".$data['context']));
            }            
        }
        else
        {
            \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
        }
        $job->delete();
    }
    
    public function updateTask($job,$data)
    {
        if(isset($data['context']))
        {
            /*
             * Router
             */
            if(is_callable("ElasticSearchHelper::".studly_case("update-".$data['context'])))
            {
                call_user_func_array("ElasticSearchHelper::".studly_case("update-".$data['context']),array("data"=>$data));
            }
            else
            {
                \Log::error('ElasticSearchHelper: Unable to call function with name: ElasticSearchHelper::'.studly_case("update-".$data['context']));
            }
        }
        else
        {
            \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
        }
        $job->delete();        
    }
    
    /*
     * Context: order-tracking
     */
    public function IndexOrderTracking($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $order = \SwiftOrder::find($data['id']);
        if(count($order))
        {
            $params['id'] = $order->id;
            $params['timestamp'] = $order->updated_at->toIso8601String();
            $params['body']['order-tracking'] = $order->toArray();
            \Es::index($params);
        }
    }
    
    public function UpdateOrderTracking($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $order = \SwiftOrder::find($data['id']);
        if(count($order))
        {
            $params['id'] = $order->id;
            $params['timestamp'] = $order->updated_at->toIso8601String();
            switch($data['info-context'])
            {
                case 'order-tracking':
                    $relation = $order;
                    $relation = $relation->toArray();
                    foreach($relation as $k => $v)
                    {
                        if(in_array($k,$data['excludes']))
                        {
                            unset($relation[$k]);
                        }
                    }
                    $params['body']['doc'][$data['info-context']] = $relation;                    
                    break;
                case 'purchaseOrder':
                case 'reception':
                case 'freight':
                case 'shipment':
                case 'customsDeclaration':
                    $relation = $order->{$data['info-context']}()->get();
                    if(count($relation))
                    {
                        foreach($relation as &$l)
                        {
                            foreach($data['excludes'] as $ex)
                            {
                                if(isset($l->{$ex}))
                                {
                                    unset($l->{$ex});
                                }
                            }
                            if(isset($l->dates))
                            {
                                foreach($l->dates as $date)
                                {
                                    if(isset($l->{$date}) && get_class($l->{$date}) == "Carbon")
                                    {
                                        $l->{$date} = $l->{$date}->toIso8601String();
                                    }
                                }
                            }
                        }
                        $relation = $relation->toArray();
                        $params['body']['doc'][$data['info-context']] = $relation;
                    }
                    else
                    {
                        $params['body']['doc'][$data['info-context']] = array();
                    }                    
                    break;
                default:
                    return;
                    break;
            }
            \Es::update($params);
        }
    }
    
    public function IndexAprequest($data)
    {
        
    }
    
    public function UpdateAprequest($data)
    {
        
    }
    

}
