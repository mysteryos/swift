<?php
/*
 * Name:
 * Description:
 */

Namespace Swift\Services;

Use Helper;
Use Log;

class OcrTask {
    /*
     * Create task
     */
    
    public function __construct(){
        if(!Helper::loginSysUser())
        {
            Log::error('Unable to login system user');
        }
        $this->guzzle = new \GuzzleHttp\Client(['base_url'=>'http://cloud.ocrsdk.com']);
    }
    
    public function init($obj,$scanModel)
    {
        
    }
    
    /*
     * Submit Image for Prior Processing
     * @return $taskinfo
     */
    public function submitImage($docUrl)
    {
        try
        {
            $request = $this->guzzle->createRequest('POST','/submitImage',[
                'headers' => ['Content-Type' => 'multipart/form-data;'],
                'body' => [
                    'my_file'=>fopen($docUrl, 'r')
                ],
                'config'=>
                    ['curl'=>
                        [CURLOPT_USERPWD => 
                                \Config::get('cloudocr.application_id').":".\Config::get('cloudocr.application_password')
                        ]
                    ]
            ]);

            $response = $this->guzzle->send($request);
            
            if($response->getBody())
            {
                $responseXml = \simplexml_load_string($response->getBody());
                $taskInfo = $responseXml->task[0]->attributes();
                if((string)$taskInfo->status === "" || strtolower((string)$taskInfo->status) !== "submitted")
                {
                    \Log::Error("Unexpected status on file upload: '".(string)$taskInfo->status."'");
                }
                else
                {
                    return $taskInfo;
                }
            }
            else
            {
                \Log::Error('File uploaded successfully but no response');
            }
            return false;            

        } catch (\Exception $e) {
            if ($e->hasResponse()) {
                \Log::Error("'$docPath'".$e->getResponse());
            }
            return false;
        }
    }
    
    /*
     * Builds XML for Text Field Processing
     */
    private function buildTextFieldXML($templatePlots)
    {
        $doc = new \SimpleXMLElement('<document/>');
        $doc->addAttribute('xmlns','http://ocrsdk.com/schema/taskDescription-1.0.xsd');
        $doc->addAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
        $doc->addAttribute('xsi:schemaLocation','http://ocrsdk.com/schema/taskDescription-1.0.xsd http://ocrsdk.com/schema/taskDescription-1.0.xsd');
        
        foreach($templatePlots as $p)
        {
            if(!isset($pageNumber))
            {
                //Just starting, insert first page node.
                $pageNumber = $p->page;
                $page = $doc->addChild('page');
                $page->addAttribute('applyTo',$p->page);
            }
            else
            {
                //Page number has changed, insert another page Node
                if($pageNumber !== $p->page)
                {
                    $pageNumber = $p->page;
                    $page = $doc->addChild('page');
                    $page->addAttribute('applyTo',$p->page);
                }
            }
            
            
            switch($p->type)
            {
                case \SwiftScanTemplatePlot::$typeText:
                    //Create field node
                    $node = $page->addChild('text');
                    break;
                case \SwiftScanTemplatePlot::$typeBarcode:
                    $node = $page->addChild('barcode');
                    break;
                case \SwiftScanTemplatePlot::$typeCheckmark:
                    $node = $page->addChild('checkmark');
                    break;
                default:
                    throw new \RuntimeException("Unknown type specified for template plot. Var dump: ".var_dump($p));
                    break;
            }
            
            $node->addAttribute('id',$p->name);
            $node->addAttribute('left',$p->coord_left);
            $node->addAttribute('top',$p->coord_top);
            $node->addAttribute('right',$p->coord_right);
            $node->addAttribute('bottom',$p->coord_bottom);

            //Add settings for the field
            foreach($p->options as $k => $v)
            {
                $node->addChild($k,$v);
            }            
        }
        
        return $doc;
                
    }
    
    /*
     * Send info for text fields
     * @return $taskInfo
     */
    
    public function processFields($templatePlots,$taskId)
    {
        $fieldXml = $this->buildTextFieldXML($templatePlots);
        try 
        {
            $tmpFile = fopen('php://temp', 'r+');
            fwrite($tmpFile, $fieldXml->asXML());
            rewind($tmpFile);
            
            $request = $this->guzzle->createRequest('POST','/processFields',[
                'headers' => ['Content-Type' => 'text/xml'],
                'body' => $tmpFile,
                'config'=>
                    ['curl'=>
                        [CURLOPT_USERPWD => 
                                \Config::get('cloudocr.application_id').":".\Config::get('cloudocr.application_password')
                        ]
                    ]
            ]);
            
            $query = $request->getQuery();
            $query->set('taskId',$taskId);
            
            $response = $this->guzzle->send($request);
            
            if($response->getBody())
            {
                $responseXml = \simplexml_load_string($response->getBody());
                $taskInfo = $responseXml->task[0]->attributes();
                if((string)$taskInfo->status === "" || strtolower((string)$taskInfo->status) !== "submitted")
                {
                    \Log::Error("Unexpected status on file upload: '{$taskInfo['status']}'");
                }
                else
                {
                    return $taskInfo;
                }
            }
            else
            {
                \Log::Error('File uploaded successfully but no response');
            }
            
            //Prevent Memory Leak
            fclose($tmpFile);
            
            return false;
            
        } catch (Exception $e) {
            if(isset($tmpFile))
            {
                //Prevent Memory Leak
                fclose($tmpFile);
            }
            
            if ($e->hasResponse()) {
                \Log::Error("'$docPath'".$e->getResponse());
            }
            return false;
        }
    }
    
    /*
     * Fetch Task Status by Task Id
     * @return array $taskInfo
     */
    public function taskStatus($taskId)
    {
        try
        {
            $request = $this->guzzle->createRequest('GET','/getTaskStatus',[
                'config'=>
                    ['curl'=>
                        [CURLOPT_USERPWD => 
                                \Config::get('cloudocr.application_id').":".\Config::get('cloudocr.application_password')
                        ]
                    ]
            ]);
            
            $query = $request->getQuery();
            $query->set('taskId',$taskId);
            
            $response = $this->guzzle->send($request);
            if($response->getBody())
            {
                $responseXml = \simplexml_load_string($response->getBody());
                $taskInfo = $responseXml->task[0]->attributes();
            
                return $taskInfo;
            }
            
            return false;
            
        } catch (Exception $e) {
            echo $e->getMessage() ."\n";
            if ($e->hasResponse()) {
                \Log::Error("'$docPath'".$e->getResponse());
            }
            return false;
        }
    }
    
    /*
     * Fetch Result from Url Provided
     * @return SimpleXMLElement $resultInfo
     */
    public function fetchResult($resultUrl)
    {
        try
        {
            $client = new \GuzzleHttp\Client();
            $client->setDefaultOption('verify', false);
            $response = $client->get($resultUrl);
            if($response->getBody())
            {
                $responseXml = \simplexml_load_string($response->getBody());
                $resultInfo = $responseXml;
                return $responseXml;
            }
            
            return false;
            
        } catch (Exception $e) {
            echo $e->getMessage() ."\n";
            if ($e->hasResponse()) {
                \Log::Error("'$docPath'".$e->getResponse());
            }
            return false;
        }        
    }
    
}