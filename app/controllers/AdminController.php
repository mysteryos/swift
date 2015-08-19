<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class AdminController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Admin";
    }    
    
    public function getIndex()
    {
        
    }
    
    public function getUsers()
    {
        $this->pageTitle = "Users";
        $this->data['users'] = User::all();
        
        return $this->makeView('admin.users');
    }

    public function getLoginAs($email)
    {
        if($this->currentUser->isSuperUser())
        {
            $user = \Sentry::findUserByLogin($email);
            if($user)
            {
                \Sentry::login($user,false);
                return \Response::make("Success");
            }
        }
        else
        {
            return \Response::make("You are not allowed to do this action",500);
        }
    }
    
    public function getPhpinfo()
    {
        $this->pageTitle = "PHPInfo";
        return $this->makeView('admin.phpinfo');
    }

    public function getUtility()
    {
        $this->pageTitle = "Utility";
        return $this->makeView("admin.utility");
    }

    public function postMssqlSync()
    {
        if(\Input::has('sqlstatement') && \Input::has('master_table_name'))
        {
            $sqlParse = (new \PHPSQLParser\PHPSQLParser(Input::get('sqlstatement')))->parsed;
            $colArray = array();
            //Get all column names in a flat array
            foreach($sqlParse['SELECT'] as $c)
            {
                $colArray[] = $c['base_expr'];
            }

            $mergeString = "MERGE INTO ".Input::get('master_table_name')." AS TARGET \nUSING(\nSELECT ".implode(",\n",$colArray)." FROM ".$sqlParse['FROM'][0]['table'].")";
            $sourceString = "\nAS SOURCE ON \n /*Add your own PKs*/";
            $notMatchedString = "\nWHEN NOT MATCHED BY TARGET THEN\nINSERT (".
                                implode(",\n",Input::has('timestamps') ? array_merge($colArray,['[created_at]','[updated_at]']) : $colArray).
                                ")\nVALUES (".
                                implode(",\n\v",Input::has('timestamps') ? array_merge(array_map(function($v){return "SOURCE.".$v;}, $colArray),['CURRENT_TIMESTAMP','CURRENT_TIMESTAMP']) : array_map(function($v){return "SOURCE.".$v;}, $colArray)).
                                ")";
            $whenMatchedString = "\nWHEN MATCHED\nAND ";

            foreach($colArray as $c)
            {
                $whenMatchedParams[] = "TARGET.$c <> SOURCE.$c";
            }

            $whenMatchedString.= implode(" OR \n",$whenMatchedParams)." \n THEN \n";

            $updateString = "UPDATE SET ";

            foreach($colArray as $c)
            {
                $updateParams[] = "TARGET.$c = SOURCE.$c";
            }

            //If has timestamps
            if(Input::has('timestamps'))
            {
                $updateParams[] = "TARGET.[updated_at] = GETDATE()";
            }

            $updateString .= implode(", \n\v",$updateParams);

            return \Response::json(['result'=>$mergeString.$sourceString.$notMatchedString.$whenMatchedString.$updateString]);
        }
        else
        {
            return \Response::json(["msg"=>"Please fill in all fields"],500);
        }

        return \Response::json(['msg'=>"Unable to complete action"],500);

    }
    
    
}