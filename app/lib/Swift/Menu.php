<?php

/* 
 * Name: Swift Menu
 */

Namespace Swift;

use Config;
use Sentry;
use URL;

class Menu {
    
    private $html;
    private $structure;
    private $currentUri;
    
    public function __construct()
    {
        $this->structure = Config::get('menu');
        $this->currentUri = URL::current();
    }
    
    public function generateHTML()
    {
        $this->generateMenu($this->structure);
        return $this->html;

    }
    
    public function generateMenu($main)
    {
        $this->html .= "<ul>";
        foreach($main as $m)
        {
            if($this->getPermission($m))
            {
                $this->generateItem($m);
            }
        }
        $this->html .= "</ul>";
    }
    
    public function generateItem($m)
    {
        $this->generateItemHTML($m);
        if(isset($m['children']))
        {
            //Has Children
            $this->generateMenu($m['children']);
        }
        
        //Li Element --- END
        $this->html .= "</li>";
    }
    
    public function getPermission($menuItem)
    {
        if(isset($menuItem['permission']))
        {
            return Sentry::getUser()->hasAccess($menuItem['permission']);
        }
        else
        {
            return true;
        }
    }
    
    public function generateItemHTML($i)
    {
        $liHtml = "";
        
        //Anchor Element --- BEGIN
        $liHtml .= "<a href=\"{$i['uri']}\"".(isset($i['class']) ? " class=\"{$i['class']}\"" : "").">";
        
        //See if it has count function
        if(isset($i['count']))
        {
            $func = $i['count'];
            $count = $func();
        }
        
        //Set Icon
        if(isset($i['icon']))
        {
            $liHtml .= "<i class=\"fa fa-lg fa-fw {$i['icon']}\">".(isset($count) ? "<em>{$count}</em>" : "")."</i> ";
        }
        
        //Set Name
        $liHtml .= "<span class=\"menu-item-parent\">{$i['name']}</span>";
        
        //Anchor Element --- END
        $liHtml .= "</a>";
        
        //Li Element --- BEGIN
        if(Config::get('app.url').$i['uri']===$this->currentUri)
        {
            $liHtml = "<li class=\"active\">".$liHtml;
        }
        else {
            $liHtml = "<li>".$liHtml;
        }
        
        //Contribute to the HTML Menu
        $this->html .= $liHtml;
    }
    
}