<?php

class DocumentViewerController extends UserController {
    public function getView($context,$id)
    {
        $mainClass = \Config::get('context.'.$context);
        if($mainClass !== false)
        {
            $form = $mainClass::with('document')->find(\Crypt::decrypt($id));
            if($form)
            {
                foreach($form->document as &$doc)
                {
                    // Generate External URL
                    switch($doc->getAttachedfiles()['document']->contentType())
                    {
                        case "image/jpeg":
                        case "image/png":
                        case "image/bmp":
                        case "image/jpg":
                            $doc->external_url = $doc->getAttachedfiles()["document"]->url();
                            break;
                        case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                        case "application/vnd.ms-excel":
                        case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                        case "application/msword":
                            $doc->external_url = 'https://docs.google.com/viewerng/viewer?url='.$doc->getAttachedfiles()["document"]->url();
                            break;
                        case "application/pdf":
                            $doc->external_url = '/pdfviewer/viewer.html?file='.$doc->getAttachedfiles()["document"]->url();
                            break;
                        default:
                            $doc->external_url = 'https://docs.google.com/viewerng/viewer?url='.$doc->getAttachedfiles()["document"]->url();
                            break;
                    }
                }

                $this->data['pageTitle'] = $form->getReadableName()." - Documents";
                $this->data['form'] = $form;
                return \View::make('docviewer.doc-viewer',$this->data);
            }
        }

        return parent::notfound();
    }
}