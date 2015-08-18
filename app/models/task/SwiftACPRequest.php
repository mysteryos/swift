<?php

namespace Task;

class SwiftACPRequest extends Task{
    
    protected $table = "SwiftACPRequest";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    /*
     * Register Filters for Forms
     */

    public function registerFilters()
    {
        \Input::flash();

        $this->controller->filter['filter_start_date'] = ['name'=>'Start Date',
                                                                'value' => \Input::get('filter_start_date'),
                                                                'enabled' => \Input::has('filter_start_date'),
                                                                'function' => 'filterStartDate'
                                                            ];

        $this->controller->filter['filter_end_date'] = ['name'=>'End Date',
                                                            'value' => \Input::get('filter_end_date'),
                                                            'enabled' => \Input::has('filter_end_date'),
                                                            'function' => 'filterEndDate'
                                                        ];

        $this->controller->filter['filter_supplier'] = ['name'=>'Supplier',
                                                        'value' => \Input::has('filter_supplier') ? \JdeSupplierMaster::find(\Input::get('filter_supplier'))->getReadableName() : false,
                                                        'enabled' => \Input::has('filter_supplier'),
                                                        'function' => 'filterSupplier'
                                                        ];

        $this->controller->filter['filter_billable_company_code'] = ['name'=>'Billable Company',
                                                                        'value' => \Input::has('filter_billable_company_code') ? \JdeCustomer::find(\Input::get('filter_billable_company_code'))->getReadableName() : false,
                                                                        'enabled' => \Input::has('filter_billable_company_code'),
                                                                        'function' => 'filterBillableCompanyCode'
                                                                       ];

        $this->controller->filter['filter_type'] = ['name'=>'Order Type',
                                                    'value' => \Input::get('filter_type',0) > 0 ? \SwiftACPInvoice::$type[(int)\Input::get('filter_type')] : "" ,
                                                    'enabled' => \Input::has('filter_type') && \Input::get('filter_type',0) > 0,
                                                    'function' => 'filterType'
                                                    ];

        $this->controller->data['filterActive'] = (boolean)count(
                                                        array_filter($this->controller->filter,function($v){
                                                            return $v['enabled'] === true;
                                                        })
                                                    );
    }

    public function registerFormFilters()
    {
        \Input::flash();

        $this->controller->filter['filter_supplier'] = ['name'=>'Supplier',
                                                        'value' => \Input::has('filter_supplier') ? \JdeSupplierMaster::find(\Input::get('filter_supplier'))->getReadableName() : false,
                                                        'enabled' => \Input::has('filter_supplier'),
                                                        'function' => 'filterSupplier'
                                                        ];

        $this->controller->filter['filter_billable_company_code'] = ['name'=>'Billable Company',
                                                                        'value' => \Input::has('filter_billable_company_code') ? \JdeCustomer::find(\Input::get('filter_billable_company_code'))->getReadableName() : false,
                                                                        'enabled' => \Input::has('filter_billable_company_code'),
                                                                        'function' => 'filterBillableCompanyCode'
                                                                       ];

        $this->controller->filter['filter_type'] = ['name'=>'Order Type',
                                                    'value' => \Input::get('filter_type',0) > 0 ? \SwiftACPInvoice::$type[(int)\Input::get('filter_type')] : "" ,
                                                    'enabled' => \Input::has('filter_type') && \Input::get('filter_type',0) > 0,
                                                    'function' => 'filterType'
                                                    ];

        $this->controller->filter['filter_step'] = ['name' => 'Current Step',
                                                    'value' => \Input::get('filter_step',0) > 0 ? \SwiftNodeDefinition::find(\Input::get('filter_step'))->label : false,
                                                    'enabled' => \Input::has('filter_step'),
                                                    'function' => 'filterStep'
                                                    ];

        $this->controller->data['filterActive'] = (boolean)count(
                                                        array_filter($this->controller->filter,function($v){
                                                            return $v['enabled'] === true;
                                                        })
                                                    );
    }

    /*
     * Apply Filters
     */

    public function applyFilters(&$query)
    {
        foreach($this->controller->filter as $filter_name => $filter_array)
        {
            if($filter_array['enabled'] &&
                array_key_exists('function',$filter_array) &&
                method_exists($this,$filter_array['function']))
            {
                $this->{$filter_array['function']}($query);
            }
        }
    }



    /*
     * Get counts of records at a specific stage.
     *
     * @var string $nodeDefinitionName
     */
    public function getCounts($nodeDefinitionName=false)
    {
        $this->controller->data['all_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                    $q->inprogress();
                                                    if($nodeDefinitionName !== false)
                                                    {
                                                        $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                            return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                               return $q->where('name','=',$nodeDefinitionName);
                                                            });
                                                        });
                                                    }
                                                    return $q;
                                                })
                                                ->has('invoice')
                                                ->count();

        //Overdue

        $this->controller->data['overdue_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                        $q->inprogress();
                                                        if($nodeDefinitionName !== false)
                                                        {
                                                            $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                                return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                                   return $q->where('name','=',$nodeDefinitionName);
                                                                });
                                                            });
                                                        }
                                                        return $q;
                                                    })
                                                    ->whereHas('invoice',function($q){
                                                        return $q->whereNotNull('due_date')
                                                              ->where('due_date','<',\Helper::previousBusinessDay(\Carbon::now())->format('Y-m-d'),'AND');
                                                    })->count();

        //Today

        $this->controller->data['today_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                    $q->inprogress();
                                                    if($nodeDefinitionName !== false)
                                                    {
                                                        $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                            return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                               return $q->where('name','=',$nodeDefinitionName);
                                                            });
                                                        });
                                                    }
                                                    return $q;
                                                })
                                                ->whereHas('invoice',function($q){
                                                    return $q->whereNotNull('due_date')
                                                          ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
                                                })->count();

        //Tomorrow

        $this->controller->data['tomorrow_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                        $q->inprogress();
                                                        if($nodeDefinitionName !== false)
                                                        {
                                                            $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                                return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                                   return $q->where('name','=',$nodeDefinitionName);
                                                                });
                                                            });
                                                        }
                                                        return $q;
                                                    })
                                                    ->whereHas('invoice',function($q){
                                                        return $q->whereNotNull('due_date')
                                                              ->where('due_date','=',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                                                    })->count();

        //Future

        $this->controller->data['future_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                    $q->inprogress();
                                                    if($nodeDefinitionName !== false)
                                                    {
                                                        $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                            return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                               return $q->where('name','=',$nodeDefinitionName);
                                                            });
                                                        });
                                                    }
                                                    return $q;
                                                })
                                                ->whereHas('invoice',function($q){
                                                    return $q->whereNotNull('due_date')
                                                          ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
                                                })->count();

        $this->controller->data['nodate_count'] = $this->resource->query()->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                                    $q->inprogress();
                                                    if($nodeDefinitionName !== false)
                                                    {
                                                        $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                            return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                               return $q->where('name','=',$nodeDefinitionName);
                                                            });
                                                        });
                                                    }
                                                    return $q;
                                                })
                                                ->whereHas('invoice',function($q){
                                                    return $q->whereNull('due_date');
                                                })
                                                ->count();
    }

    /*
     * Get list of billable companies for filter
     */

    public function getBillableCompanyCodes($type,$nodeDefinitionName=false)
    {
        $activeBillableCompanyCodes = $this->resource->query();

        $activeBillableCompanyCodes->groupBy('billable_company_code')
                                    ->orderBy('billable_company_code','ASC')
                                    ->with(['company'])
                                    ->whereHas('workflow',function($q) use ($nodeDefinitionName){
                                        $q->inprogress();
                                        if($nodeDefinitionName !== false)
                                        {
                                            $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                                return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                                   return $q->where('name','=',$nodeDefinitionName);
                                                });
                                            });
                                        }
                                        return $q;
                                    });
        switch ($type)
        {
            case 'overdue':
                $this->filterDueDateOverdue($activeBillableCompanyCodes);
                break;
            case 'today':
                $this->filterDueDateToday($activeBillableCompanyCodes);
                break;
            case 'tomorrow':
                $this->filterDueDateTomorrow($activeBillableCompanyCodes);
                break;
            case 'future':
                $this->filterDueDateFuture($activeBillableCompanyCodes);
                break;
            case 'nodate':
                $this->filterDueDateNoDate($activeBillableCompanyCodes);
                break;
            case 'all':
            default:
                break;
        }

        return $activeBillableCompanyCodes->get();
    }

    /*
     * Get list of suppliers for filter
     *
     */
    public function getActiveSuppliers($type,$nodeDefinitionName=false)
    {
        $activeSuppliers = $this->resource->query();

        $activeSuppliers->groupBy('supplier_code')
                        ->orderBy('supplier_code','ASC')
                        ->with(['supplier'])
                        ->whereHas('workflow',function($q) use ($nodeDefinitionName){
                            $q->inprogress();
                            if($nodeDefinitionName !== false)
                            {
                                $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionName){
                                    return $q->whereHas('definition',function($q) use ($nodeDefinitionName){
                                       return $q->where('name','=',$nodeDefinitionName);
                                    });
                                });
                            }
                            return $q;
                        });

        switch ($type)
        {
            case 'overdue':
                $this->filterDueDateOverdue($activeSuppliers);
                break;
            case 'today':
                $this->filterDueDateToday($activeSuppliers);
                break;
            case 'tomorrow':
                $this->filterDueDateTomorrow($activeSuppliers);
                break;
            case 'future':
                $this->filterDueDateFuture($activeSuppliers);
                break;
            case 'nodate':
                $this->filterDueDateNoDate($activeSuppliers);
                break;
            case 'all':
            default:
                break;
        }

        return $activeSuppliers->get();
    }

    /*
     * Get Billable Company Codes for Form
     */
    public function getFormBillableCompanyCodes($nodeDefinitionId=false)
    {
        $activeBillableCompanyCodes = $this->resource->query();

        $activeBillableCompanyCodes->groupBy('billable_company_code')
                                    ->orderBy('billable_company_code','ASC')
                                    ->with(['company'])
                                    ->whereHas('workflow',function($q) use ($nodeDefinitionId){
                                        $q->inprogress();
                                        if($nodeDefinitionId !== false)
                                        {
                                            $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionId){
                                                return $q->whereHas('definition',function($q) use ($nodeDefinitionId){
                                                   return $q->where('id','=',$nodeDefinitionId);
                                                });
                                            });
                                        }
                                        return $q;
                                    });

        return $activeBillableCompanyCodes->get();
    }

    /*
     * Get list of suppliers for form filters
     *
     */
    public function getFormActiveSuppliers($nodeDefinitionId=false)
    {
        $activeSuppliers = $this->resource->query();

        $activeSuppliers->groupBy('supplier_code')
                        ->orderBy('supplier_code','ASC')
                        ->with(['supplier'])
                        ->whereHas('workflow',function($q) use ($nodeDefinitionId){
                            $q->inprogress();
                            if($nodeDefinitionId !== false)
                            {
                                $q->whereHas('pendingNodes',function($q) use ($nodeDefinitionId){
                                    return $q->whereHas('definition',function($q) use ($nodeDefinitionId){
                                       return $q->where('id','=',$nodeDefinitionId);
                                    });
                                });
                            }
                            return $q;
                        });

        return $activeSuppliers->get();
    }

    /*
     * Filter Functions: START
     */
    private function filterDueDateOverdue(&$query)
    {
        return $query->whereHas('invoice',function($q){
            return $q->whereNotNull('due_date')
                  ->where('due_date','<',\Helper::previousBusinessDay(\Carbon::now())->format('Y-m-d'),'AND');
        });
    }

    private function filterDueDateToday(&$query)
    {
        return $query->whereHas('invoice',function($q){
            return $q->whereNotNull('due_date')
                  ->where('due_date','=',\Carbon::now()->format('Y-m-d'),'AND');
        });
    }

    private function filterDueDateTomorrow(&$query)
    {
        return $query->whereHas('invoice',function($q){
            return $q->whereNotNull('due_date')
                  ->where('due_date','=',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
        });
    }

    private function filterDueDateFuture(&$query)
    {
        return $query->whereHas('invoice',function($q){
            return $q->whereNotNull('due_date')
                  ->where('due_date','>',\Helper::nextBusinessDay(\Carbon::now()->addDay())->format('Y-m-d'),'AND');
        });
    }

    private function filterDueDateNoDate(&$query)
    {
        return $query->whereHas('invoice',function($q){
            return $q->whereNull('due_date');
        });
    }

    private function filterStartDate(&$query)
    {
        $this->filterDate($query);
    }
    
    private function filterEndDate(&$query)
    {
        $this->filterDate($query);
    }

    private function filterDate(&$query)
    {
        $filter_end_date = \Carbon::createFromFormat('d/m/Y',\Input::get('filter_end_date'));
        $filter_start_date = \Carbon::createFromFormat('d/m/Y',\Input::get('filter_start_date'));

        $query->whereHas('invoice',function($q) use ($filter_end_date,$filter_start_date){
            if($filter_start_date !== false)
            {
                $q->where('due_date','>=',$filter_start_date->format('Y-m-d'),'AND');
            }
            if($filter_end_date !== false)
            {
                $q->where('due_date','<=',$filter_end_date->format('Y-m-d'),'AND');
            }

            $q->whereNotNull('due_date');

            return $q;
        });
    }

    private function filterSupplier(&$query)
    {
        if(is_numeric(\Input::get('filter_supplier')))
        {
            $query->where('supplier_code','=',\Input::get('filter_supplier'));
        }
    }

    private function filterBillableCompanyCode(&$query)
    {
        if(is_numeric(\Input::get('filter_billable_company_code')))
        {
            $query->where('billable_company_code','=',\Input::get('filter_billable_company_code'));
        }
    }

    private function filterType(&$query)
    {
        $query->whereHas('invoice',function($q){
             if((int)\Input::get('filter_type',0) === \SwiftACPInvoice::TYPE_LOCAL)
             {
                 return $q->local();
             }
             else
             {
                 return $q->foreign();
             }
        });
    }

    private function filterStep(&$query)
    {
        $query->whereHas('workflow',function($q){
            return $q->inprogress()->whereHas('pendingNodes',function($q){
                return $q->whereHas('definition',function($q){
                   return $q->where('id','=',\Input::get('filter_step'));
                });
            });
        });
    }

    /*
     * Filter Functions: END
     */
}