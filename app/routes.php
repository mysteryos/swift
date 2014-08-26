<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*
 * All routes require authentication
 */

Route::group(array('before' => array('auth')), function()
{
    Route::get('/', function()
    {
        return Redirect::to('/dashboard');
    });
    
    //Logout Requests
    Route::get('/logout',array('as'=>'logout','uses'=>'LoginController@getLogout'));
    
    //Dashboard
    Route::controller('dashboard', 'DashboardController');
    
    //Inbox
    Route::controller('inbox','InboxController');
    
    //Order Tracking
    Route::controller('order-tracking','OrderTrackingController');
    
    //Nespresso CRM
    Route::controller('nespresso-crm','NespressoCrmController');
    
    //Ajax Search
    Route::controller('ajaxsearch','AjaxSearchController');
    
//  Route::controller('user', 'UserController');
});

Route::group(array('before'=> array('admin')), function()
{
    Route::controller('admin','AdminController');
});

/*
 * Handles all login requests
 */

Route::controller('login','LoginController');