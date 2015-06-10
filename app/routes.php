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
    
    //A&P request
    Route::controller('aprequest','APRequestController');
    
    //Ajax Search
    Route::controller('ajaxsearch','AjaxSearchController');
    
    //Pusher
    Route::controller('pusher','PusherController');
    
    //Comment
    Route::controller('comment','CommentController');
    
    //search
    Route::controller('search','SearchController');
    
    //Notification
    Route::controller('notification','NotificationController');
    
    //Subscription
    Route::controller('subscription','SubscriptionController');
    
    //Sales Commission
    Route::controller('sales-commission','SalesCommissionController');
    
    //Salesman
    Route::controller('salesman','SalesmanController');

    //Accounts Payable
    Route::controller('accounts-payable','AccountsPayableController');

    //Jde Purchase Order
    Route::controller('jde-purchase-order','JdePurchaseOrderController');

    //Workflow
    Route::controller('workflow','WorkflowController');

    //Product Returns
    Route::controller('product-returns','ProductReturnsController');
    
});

Route::group(array('before'=> array('admin')), function()
{
    Route::controller('admin','AdminController');
});

/*
 * Handles all login requests
 */

Route::controller('login','LoginController');

/*
 * 404 Route
 */

Route::get('404','UserController@notfound');

Route::controller('test','testController');