<?php
/*
 * Name: Ocr Text Field
 * Description: For Scan Invoice Documents
 */

NameSpace Swift\OcrTextField;

class NodeDefinition {
    
    public static function ocrtextStart($nodeActivity)
    {
        $scandoc = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        $task = new SwiftOcrTask();
        $task->queued_at = Carbon::now();
        $scandoc->ocrTask()->save($task);
        
        return true;
    }
    
    /*
     * Send Image to OCR Cloud Service
     * and Create OCR task record
     */
    public static function ocrtextSubmitimage($nodeActivity)
    {
        $scandoc = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        //Fetch S3 URL
        $docUrl = $s3->getObjectUrl(\Config::get('cloudocr.s3_bucket'),$scandoc->s3_key,'+10 minutes');
        
        //send image to Cloud OCR
        $taskResult = \OcrTask::submitImage($docUrl);
        if($taskResult !== false)
        {
            //Start Task
            $ocrTask = new SwiftOcrTask([
               'ocrtask_id' => (string)$taskresult->id,
               'queued_at' => new DateTime((string)$taskresult->statuschangetime),
               'scan_doc_id' => $scandoc->id,
               'status' => \SwiftOcrTask::$INPROGRESS
            ]);
            
            $ocrtask->save();
            
            return true;
        }
        
        return false;
    }
    
    /*
     * Send details of text fields to be recognized
     * and update OCR task record
     */
    public static function ocrtextProcessfields($nodeActivity)
    {
        $scandoc = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        $scandoc->load(['ocrTask','plot']);
        if(count($scandoc->ocrTask))
        {
            foreach($scandoc->ocrTask as $task)
            {
                //Next we send info for text fields
                if($task->status === \SwiftOcrTask::$INPROGRESS)
                {
                    //Expected number of task in progress = one
                    if(count($scandoc->plot))
                    {
                        $taskResult = \OcrTask::processFields($scandoc->plot,$task->ocrtask_id);
                        if($taskResult !== false)
                        {
                            $task->inprogress_at = Carbon::now();
                            $task->save();
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function ocrtextTaskstatus($nodeActivity)
    {
        $scandoc = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        $scandoc->load('ocrTask');
        if(count($scandoc->ocrTask))
        {
            foreach($scandoc->ocrTask as $task)
            {
                //Next we send info for text fields
                if($task->status === \SwiftOcrTask::$INPROGRESS)
                {
                    $taskResult = \OcrTask::taskStatus($task->ocrtask_id);
                    if($taskResult !== false && (string)$taskResult->status === "Completed")
                    {
                        $task->completed_at = Carbon::now();
                        $task->save();
                        return true;
                        
                    }
                }
            }
        }
        return false;
    }
    
    public function ocrtextGetresult($nodeActivity)
    {
        $scandoc = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        $scandoc->load('ocrTask');
        if(count($scandoc->ocrTask))
        {
            foreach($scandoc->ocrTask as $task)
            {
                //Next we send info for text fields
                if($task->status === \SwiftOcrTask::$INPROGRESS)
                {
                    $taskResult = \OcrTask::taskStatus($task->ocrtask_id);
                    if($taskResult !== false && (string)$taskResult->status === "Completed")
                    {
                        $doc = OcrTask::fetchResult((string)$taskResult->resultUrl);
                        if($doc !== false)
                        {
                            $originalRecord = $scandoc->scannable()->first();
                            foreach($doc->page as $page)
                            {
                                foreach($page->text as $text)
                                {
                                    $attr = $text->attributes();
                                    $mapping = \SwiftScanTemplateMapping::where('plot_name','=',(string)$attr->id)->first();
                                    if(count($mapping))
                                    {
                                        $field_type = \DB::connection()->getDoctrineColumn($originalRecord->getTable(), $mapping->fieldname)->getType()->getName();
                                        //Get Field Type and format accordingly
                                        switch($field_type)
                                        {
                                            case "integer":
                                                $value = (int) $text->value[0];
                                                break;
                                            case "float":
                                                break;
                                            case "datetime":
                                            case "date":
                                                $value = new \DateTime((string) $text->value[0]);
                                                break;
                                            case "varchar":
                                            case "blob":
                                            default:
                                                $value = (string) $text->value[0];
                                                break;
                                        }
                                        $originalRecord->{$mapping->fieldname} = $value;
                                    }
                                    else
                                    {
                                        \Log::error('Unknown mapping "'.(string)$attr->id.'" receieved from scan result');
                                    }
                                    
                                }
                                $originalRecord->save();
                            }
                            return true;
                        }
                    }
                }
            }            
        }        
    }
}